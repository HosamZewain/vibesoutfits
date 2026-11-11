<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Variant;
use App\Services\OrderWorkflow;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AdminController extends Controller
{
    public function loginPage(Request $request): View|RedirectResponse
    {
        return $request->user()?->is_admin ? redirect()->route('admin.dashboard') : view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge(['email' => strtolower((string) $request->string('email'))]);
        $data = $request->validate(['email' => ['required', 'email', 'max:150'], 'password' => ['required', 'string', 'max:255']]);
        if (! Auth::attempt($data + ['is_admin' => true])) {
            throw ValidationException::withMessages(['email' => 'بيانات الدخول غير صحيحة.']);
        }
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboard(Request $request): View
    {
        $days = in_array($request->integer('days'), [7, 30, 90], true) ? $request->integer('days') : 30;
        $period = Order::where('created_at', '>=', now('Africa/Cairo')->startOfDay()->subDays($days - 1)->utc());
        $stats = [
            'orders' => (clone $period)->count(),
            'sales' => (clone $period)->where('status', 'delivered')->sum('total'),
            'cash' => (clone $period)->sum('paid_amount') - (clone $period)->sum('refunded_amount'),
            'aov' => (int) round((clone $period)->where('status', '!=', 'cancelled')->avg('total') ?? 0),
        ];
        $counts = (clone $period)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $orders = Order::with('items')->latest('id')->limit(6)->get();
        $lowStock = Variant::where('active', true)->whereHas('product', fn ($query) => $query->where('active', true))->where('stock', '<=', 3)->with('product')->orderBy('stock')->limit(6)->get();
        $catalogCount = Product::where('active', true)->count();

        return view('admin.dashboard', compact('stats', 'counts', 'orders', 'lowStock', 'days', 'catalogCount'));
    }

    public function orders(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = $request->string('q')->limit(100)->trim()->toString();
        $orders = Order::with('items')
            ->when(array_key_exists($status, config('vibe.statuses')), fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('number', 'like', '%'.$search.'%')->orWhere('phone', 'like', '%'.$search.'%')->orWhere('name', 'like', '%'.$search.'%')))
            ->latest('id')->paginate(15)->withQueryString();

        return view('admin.orders', compact('orders', 'status', 'search'));
    }

    public function order(Order $order): View
    {
        $order->load(['items', 'events' => fn ($query) => $query->latest('id')]);

        return view('admin.order', compact('order'));
    }

    public function transition(Request $request, Order $order, OrderWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('vibe.statuses')))],
            'carrier' => ['nullable', 'string', 'max:80'],
            'shipment_code' => ['nullable', 'string', 'max:100'],
        ]);
        $workflow->transition($order, $data['status'], $data);

        return back()->with('success', 'حالة الطلب اتحدثت.');
    }

    public function payment(Request $request, Order $order, OrderWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:99999999'],
            'recorded' => ['required', 'integer', 'min:0'],
            'action' => ['required', Rule::in(['collect', 'refund'])],
            'reference' => ['nullable', 'string', 'max:150'],
        ]);
        $workflow->recordMoney($order, (int) $data['amount'], $data['action'], (int) $data['recorded'], $data['reference'] ?? null);

        return back()->with('success', 'تم تسجيل العملية.');
    }

    public function artwork(Request $request, Order $order, OrderItem $item, OrderWorkflow $workflow): RedirectResponse
    {
        abort_unless($item->order_id === $order->id, 404);
        $data = $request->validate([
            'kind' => ['required', Rule::in(['preview', 'production'])],
            'artwork' => ['required', File::types(['jpg', 'jpeg', 'png', 'webp'])->max('10mb')],
        ]);
        $path = $data['artwork']->store('order-'.$data['kind'], 'local');
        if (! $path) {
            throw ValidationException::withMessages(['artwork' => 'رفع الملف ماكملش.']);
        }
        try {
            $obsolete = $workflow->attach($order, $item, $data['kind'], $path);
        } catch (Throwable $error) {
            Storage::disk('local')->delete($path);
            throw $error;
        }
        Storage::disk('local')->delete($obsolete);

        return back()->with('success', $data['kind'] === 'preview' ? 'المعاينة متاحة للعميل دلوقتي.' : 'تم حفظ ملف الطباعة.');
    }

    public function file(Order $order, OrderItem $item, string $kind): StreamedResponse
    {
        abort_unless($item->order_id === $order->id, 404);
        $path = match ($kind) {
            'upload' => $item->upload_path,
            'preview' => $item->preview_path,
            'production' => $item->production_path,
            default => null,
        };
        abort_unless($path, 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404);

        return $kind === 'preview'
            ? $disk->response($path, null, ['X-Content-Type-Options' => 'nosniff'])
            : $disk->download($path, $kind.'-'.strtolower($order->number).'-'.$item->id.'.'.pathinfo($path, PATHINFO_EXTENSION), ['X-Content-Type-Options' => 'nosniff']);
    }

    public function customers(Request $request): View
    {
        $search = $request->string('q')->trim()->limit(100)->toString();
        $summary = Order::selectRaw('phone, MAX(id) as latest_id, COUNT(*) as order_count, SUM(paid_amount - refunded_amount) as collected')->groupBy('phone');
        $customers = Order::joinSub($summary, 'customer_stats', 'orders.id', '=', 'customer_stats.latest_id')
            ->select('orders.*', 'customer_stats.order_count', 'customer_stats.collected')
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('orders.phone', 'like', '%'.$search.'%')->orWhere('orders.name', 'like', '%'.$search.'%')))
            ->latest('orders.id')->paginate(15)->withQueryString();

        return view('admin.customers', compact('customers', 'search'));
    }
}
