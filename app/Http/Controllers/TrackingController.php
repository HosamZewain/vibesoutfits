<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderWorkflow;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrackingController extends Controller
{
    public static function authorizeOrder(Request $request, Order $order): void
    {
        $access = $request->session()->get('vibe.order_access', []);
        unset($access[$order->id]);
        $access[$order->id] = now()->addHours(2)->timestamp;
        $request->session()->put('vibe.order_access', array_slice($access, -15, null, true));
    }

    public function index(Request $request): View
    {
        return view('store.track-form', ['number' => $request->string('order')->limit(24)->toString()]);
    }

    public function lookup(Request $request): RedirectResponse
    {
        $data = $request->validate(['number' => ['required', 'string', 'max:24'], 'phone' => ['required', 'string', 'max:30']]);
        $order = Order::where('number', strtoupper($data['number']))->where('phone', CheckoutController::phone($data['phone']))->first();
        if (! $order) {
            throw ValidationException::withMessages(['number' => 'رقم الطلب أو الموبايل مش مطابق. راجع البيانات وحاول تاني.']);
        }

        self::authorizeOrder($request, $order);

        return redirect()->route('tracking.show', $order->number);
    }

    public function show(Request $request, string $number): Response|RedirectResponse
    {
        $order = Order::where('number', $number)->first();
        if (! $order || ! $this->canView($request, $order)) {
            return redirect()->route('tracking', ['order' => $number]);
        }

        $order->load(['items', 'events' => fn ($query) => $query->where('visible', true)->latest('id')]);

        return response()->view('store.tracking', compact('order'))->header('Cache-Control', 'private, no-store')->header('X-Robots-Tag', 'noindex');
    }

    public function preview(Request $request, Order $order, OrderItem $item): StreamedResponse
    {
        abort_unless($this->canView($request, $order) && $item->order_id === $order->id && $item->preview_path, 404);

        return Storage::disk('local')->response($item->preview_path, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function review(Request $request, Order $order, OrderItem $item, OrderWorkflow $workflow): RedirectResponse
    {
        abort_unless($this->canView($request, $order) && $item->order_id === $order->id, 404);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'changes'])],
            'version' => ['required', 'integer', 'min:1'],
            'reason' => ['required_if:decision,changes', 'nullable', 'string', 'max:1000'],
        ]);
        $workflow->review($order, $item, $data['decision'], (int) $data['version'], $data['reason'] ?? null);

        return redirect()->route('tracking.show', $order->number)->with('success', 'اتسجل ردك على المعاينة.');
    }

    private function canView(Request $request, Order $order): bool
    {
        return $request->session()->get('vibe.order_access.'.$order->id, 0) >= now()->timestamp;
    }
}
