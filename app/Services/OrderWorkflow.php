<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderWorkflow
{
    public function transition(Order $order, string $next, array $shipment): void
    {
        DB::transaction(function () use ($order, $next, $shipment): void {
            $current = Order::lockForUpdate()->findOrFail($order->id);
            $allowed = match ($current->status) {
                'new' => ['production', 'cancelled'],
                'production' => ['shipped', 'cancelled'],
                'shipped' => ['delivered'],
                default => [],
            };
            if (! in_array($next, $allowed, true)) {
                self::fail('الحالة دي مش متاحة من المرحلة الحالية.');
            }

            if ($next === 'production' && ($current->paid_amount < $current->deposit_due || ! $current->allApproved())) {
                self::fail('سجّل المقدم واستنى موافقة العميل على كل التصميمات قبل الإنتاج.');
            }
            if ($next === 'shipped') {
                if (blank($shipment['carrier'] ?? null) || blank($shipment['shipment_code'] ?? null)) {
                    self::fail('اكتب شركة الشحن ورقم الشحنة.');
                }
                $current->fill(['carrier' => $shipment['carrier'], 'shipment_code' => $shipment['shipment_code']]);
            }
            if ($next === 'delivered' && $current->balance() > 0) {
                self::fail('سجّل تحصيل باقي المبلغ قبل تأكيد التسليم.');
            }
            if ($next === 'cancelled' && $current->status === 'new') {
                $restocks = $current->items->groupBy('variant_id')->sortKeys();
                foreach ($restocks as $variantId => $items) {
                    Variant::whereKey($variantId)->increment('stock', $items->sum('quantity'));
                }
            }
            $current->status = $next;
            $current->save();
            $current->addEvent(config('vibe.statuses.'.$next));
        }, 3);
    }

    public function recordMoney(Order $order, int $amount, string $action, int $recorded, ?string $reference): void
    {
        DB::transaction(function () use ($order, $amount, $action, $recorded, $reference): void {
            $current = Order::lockForUpdate()->findOrFail($order->id);
            $expected = $action === 'refund' ? $current->refunded_amount : $current->paid_amount;
            if ($expected !== $recorded) {
                self::fail('المبلغ المسجّل اتغير. راجع آخر تحصيل أو استرداد قبل التسجيل.');
            }
            if ($action === 'refund') {
                if ($current->status !== 'cancelled' || $amount > $current->paid_amount - $current->refunded_amount) {
                    self::fail('المبلغ غير متاح للاسترداد. سجل الاسترداد على الطلبات الملغية فقط.');
                }
                $current->increment('refunded_amount', $amount);
                $current->addEvent('تم تسجيل استرداد '.number_format($amount).' جنيه.');
            } else {
                if ($current->status === 'cancelled' || $amount > $current->balance()) {
                    self::fail('مبلغ التحصيل أكبر من المتبقي، أو الطلب ملغي.');
                }
                $current->increment('paid_amount', $amount);
                $current->addEvent('تم تأكيد استلام '.number_format($amount).' جنيه.');
            }
            if (filled($reference)) {
                $current->addEvent('مرجع العملية: '.$reference, visible: false);
            }
        }, 3);
    }

    public function review(Order $order, OrderItem $item, string $decision, int $version, ?string $reason): void
    {
        DB::transaction(function () use ($order, $item, $decision, $version, $reason): void {
            $current = Order::lockForUpdate()->findOrFail($order->id);
            $line = $current->items()->findOrFail($item->id);
            if ($current->status !== 'new' || $line->design_status !== 'preview' || $line->preview_version !== $version) {
                self::fail('المعاينة اتغيرت أو اتراجع عليها بالفعل. افتح أحدث معاينة.');
            }
            $approved = $decision === 'approve';
            $line->update([
                'design_status' => $approved ? 'approved' : 'changes',
                'approved_at' => $approved ? now() : null,
                'change_request' => $approved ? null : $reason,
            ]);
            $current->addEvent(($approved ? 'تمت الموافقة على تصميم ' : 'تم طلب تعديل تصميم ').$line->product_name.' · V'.$version);
        }, 3);
    }

    public function attach(Order $order, OrderItem $item, string $kind, string $path): array
    {
        return DB::transaction(function () use ($order, $item, $kind, $path): array {
            $current = Order::lockForUpdate()->findOrFail($order->id);
            $line = $current->items()->findOrFail($item->id);
            if ($line->design_mode === 'ready' || $current->status !== 'new' || $line->design_status === 'approved') {
                self::fail('الملفات بتتعدل قبل الموافقة وفي الطلبات المخصصة فقط.');
            }

            if ($kind === 'preview') {
                $old = array_filter([$line->preview_path, $line->production_path]);
                $line->update([
                    'preview_path' => $path,
                    'production_path' => null,
                    'preview_version' => $line->preview_version + 1,
                    'design_status' => 'preview',
                    'approved_at' => null,
                ]);
                $current->addEvent('معاينة جديدة جاهزة للمراجعة: '.$line->product_name.' · V'.$line->preview_version);
            } else {
                if (! $line->preview_path || $line->design_status !== 'preview') {
                    self::fail('ارفع أحدث معاينة الأول، وبعدها ملف الطباعة الخاص بيها.');
                }
                $old = array_filter([$line->production_path]);
                $line->update(['production_path' => $path]);
                $current->addEvent('تم رفع ملف الطباعة: '.$line->product_name, visible: false);
            }

            return $old;
        }, 3);
    }

    private static function fail(string $message): never
    {
        throw ValidationException::withMessages(['workflow' => $message]);
    }
}
