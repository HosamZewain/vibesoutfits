<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\User;
use Database\Seeders\LaunchCatalogSeeder;
use Database\Seeders\ShippingZoneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_ready_order_prices_shipping_reserves_stock_and_tracks_with_correct_phone(): void
    {
        $this->seed();
        Mail::fake();
        config(['vibe.email_confirmations' => true]);
        $product = Product::where('slug', 'after-hours')->firstOrFail();
        $this->add($product, ['quantity' => 2]);

        $this->get(route('checkout'))->assertOk()->assertSee('1,298');
        $this->post(route('checkout.place'), $this->delivery(['quoted_total' => 1350]))
            ->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);

        $response = $this->post(route('checkout.place'), $this->delivery(['quoted_total' => 1363]));
        $order = Order::sole();
        $response->assertRedirect(route('tracking.show', $order->number));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id, 'phone' => '01000000000', 'subtotal' => 1298, 'shipping_fee' => 65, 'total' => 1363, 'deposit_due' => 0,
        ]);
        $this->assertDatabaseHas('variants', ['id' => $order->items->sole()->variant_id, 'stock' => 10]);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'quantity' => 2, 'unit_price' => 649]);
        Mail::assertSent(OrderConfirmation::class, fn (OrderConfirmation $mail) => $mail->hasTo('buyer@example.test') && $mail->order->is($order));

        $this->post(route('checkout.place'), $this->delivery())->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 1);
        $this->get(route('tracking.show', $order->number))->assertOk()->assertSee('After Hours Tee');
        $this->withSession(['vibe.order_access' => []])->get(route('tracking.show', $order->number))->assertRedirect(route('tracking', ['order' => $order->number]));
        $this->post(route('tracking.lookup'), ['number' => $order->number, 'phone' => '01000000001'])->assertSessionHasErrors('number');
        $this->post(route('tracking.lookup'), ['number' => strtolower($order->number), 'phone' => '٠١٠٠٠٠٠٠٠٠٠'])->assertRedirect(route('tracking.show', $order->number));
    }

    public function test_custom_order_blocks_production_until_current_preview_is_approved_and_deposit_collected(): void
    {
        $this->seed();
        Storage::fake('local');
        $product = Product::where('slug', 'after-hours')->firstOrFail();
        $this->add($product, ['design_mode' => 'idea', 'idea' => 'Red graphic with a moon and geometric lines.']);
        $this->post(route('checkout.place'), $this->delivery())->assertSessionHasErrors('payment');
        $this->post(route('checkout.place'), $this->delivery(['payment' => 'transfer', 'quoted_total' => 834]))->assertRedirect();
        $order = Order::sole();
        $item = $order->items()->sole();
        $params = ['order' => $order->number, 'item' => $item->id];
        $status = route('admin.orders.status', ['order' => $order->number]);
        $payment = route('admin.orders.payment', ['order' => $order->number]);
        $review = route('tracking.review', $params);
        $this->assertSame(385, $order->deposit_due);

        $this->actingAs($this->admin())->patch($status, ['status' => 'production'])->assertSessionHasErrors('workflow');
        $this->post($payment, ['action' => 'collect', 'amount' => 385, 'recorded' => 0])->assertSessionHasNoErrors();
        $this->post($payment, ['action' => 'collect', 'amount' => 385, 'recorded' => 0])->assertSessionHasErrors('workflow');
        $this->assertSame(385, $order->fresh()->paid_amount);
        $this->patch($status, ['status' => 'production'])->assertSessionHasErrors('workflow');

        $upload = route('admin.orders.artwork', $params);
        $this->post($upload, ['kind' => 'preview', 'artwork' => $this->image('first.jpg')])->assertSessionHasNoErrors();
        $first = $item->fresh()->preview_path;
        Storage::disk('local')->assertExists($first);
        $this->post($review, ['decision' => 'changes', 'version' => 1, 'reason' => 'Move the moon a bit.'])->assertSessionHasNoErrors();
        $this->assertSame('changes', $item->fresh()->design_status);
        $this->post($upload, ['kind' => 'preview', 'artwork' => $this->image('second.jpg')])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($first);
        $this->post($review, ['decision' => 'approve', 'version' => 1])->assertSessionHasErrors('workflow');
        $this->get(route('tracking.preview', $params))->assertOk()->assertHeader('content-type', 'image/jpeg');
        $this->post($review, ['decision' => 'approve', 'version' => 2])->assertSessionHasNoErrors();
        $this->assertNotNull($item->fresh()->approved_at);

        $this->get(route('admin.orders.show', ['order' => $order->number]))->assertOk()->assertSee('وقت الموافقة');
        $this->patch($status, ['status' => 'production'])->assertSessionHasNoErrors();
        $this->patch($status, ['status' => 'shipped'])->assertSessionHasErrors('workflow');
        $this->patch($status, ['status' => 'shipped', 'carrier' => 'Demo Courier', 'shipment_code' => 'DEMO-123'])->assertSessionHasNoErrors();
        $this->patch($status, ['status' => 'delivered'])->assertSessionHasErrors('workflow');
        $this->post($payment, ['action' => 'collect', 'amount' => 449, 'recorded' => 385])->assertSessionHasNoErrors();
        $this->patch($status, ['status' => 'delivered'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'delivered', 'paid_amount' => 834, 'shipment_code' => 'DEMO-123']);
        $this->get(route('tracking.show', $order->number))->assertOk()->assertSee('DEMO-123');
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('834');
        $this->get(route('admin.customers'))->assertOk()->assertSee('01000000000');
    }

    public function test_uploaded_artwork_is_private_and_cancellation_releases_reserved_stock_once(): void
    {
        $this->seed();
        Storage::fake('local');
        $product = Product::where('slug', 'after-hours')->firstOrFail();
        $this->post(route('cart.add', ['product' => $product->slug]), $this->selection([
            'design_mode' => 'upload', 'artwork' => UploadedFile::fake()->create('design.svg', 3, 'image/svg+xml'),
        ]))->assertSessionHasErrors('artwork');

        $this->add($product, ['design_mode' => 'upload', 'artwork' => $this->image('customer.jpg')]);
        $this->post(route('checkout.place'), $this->delivery(['payment' => 'transfer']))->assertRedirect();
        $order = Order::sole();
        $item = $order->items()->sole();
        $status = route('admin.orders.status', ['order' => $order->number]);
        $payment = route('admin.orders.payment', ['order' => $order->number]);
        $file = route('admin.orders.file', ['order' => $order->number, 'item' => $item->id, 'kind' => 'upload']);
        Storage::disk('local')->assertExists($item->upload_path);
        $this->get($file)->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create())->get($file)->assertForbidden();
        $this->get('/storage/'.$item->upload_path)->assertNotFound();

        $this->actingAs($this->admin())->get($file)->assertDownload();
        $this->post($payment, ['action' => 'collect', 'amount' => 385, 'recorded' => 0])->assertSessionHasNoErrors();
        $this->patch($status, ['status' => 'cancelled'])->assertSessionHasNoErrors();
        $this->patch($status, ['status' => 'cancelled'])->assertSessionHasErrors('workflow');
        $this->post($payment, ['action' => 'refund', 'amount' => 386, 'recorded' => 0])->assertSessionHasErrors('workflow');
        $this->post($payment, ['action' => 'refund', 'amount' => 385, 'recorded' => 0])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled', 'refunded_amount' => 385]);
        $this->assertDatabaseHas('variants', ['id' => $item->variant_id, 'stock' => 12]);
    }

    public function test_admin_can_manage_catalog_and_stale_stock_updates_do_not_overwrite_reservations(): void
    {
        $this->seed();
        Storage::fake('local');
        $this->actingAs($this->admin())->get(route('admin.products.create'))->assertOk()->assertSee('منتج جديد');
        $this->post(route('admin.products.store'), [
            'name' => 'Demo Tee', 'description' => 'Demo description', 'category' => 'regular', 'price' => 350, 'custom_fee' => 60, 'active' => 1, 'ready_enabled' => 1,
            'image' => $this->image('product.jpg'),
        ])->assertSessionHasNoErrors();
        $product = Product::where('name', 'Demo Tee')->sole();
        $this->get(route('admin.products.edit', $product))->assertOk()->assertSee('Demo Tee');
        $this->get(route('product.image', $product))->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->post(route('admin.variants.store', $product), ['color' => 'black', 'size' => 'M', 'stock' => 2])->assertSessionHasNoErrors();
        $variant = $product->variants()->sole();
        $update = route('admin.variants.update', [$product, $variant]);

        $this->add($product, ['size' => 'M']);
        $this->post(route('checkout.place'), $this->delivery())->assertRedirect();
        $this->patch($update, ['stock' => 7, 'current_stock' => 2, 'active' => 1])->assertSessionHasErrors('stock');
        $this->assertSame(1, $variant->fresh()->stock);
        $this->patch($update, ['stock' => 7, 'current_stock' => 1, 'active' => 1])->assertSessionHasNoErrors();
        $this->assertSame(7, $variant->fresh()->stock);
        $this->get(route('admin.products'))->assertOk()->assertSee('Demo Tee');
        $this->get(route('admin.shipping'))->assertOk()->assertSee('الإسكندرية');
    }

    public function test_launch_catalog_groups_colors_without_overwriting_admin_prices_or_stock(): void
    {
        $this->seed(LaunchCatalogSeeder::class);
        $this->seed(ShippingZoneSeeder::class);

        $this->assertDatabaseCount('products', 4);
        $this->assertSame([899], Product::distinct()->pluck('price')->all());
        $hoodie = Product::where('slug', 'relaxed-hoodie')->sole();
        $this->assertSame(['black', 'navy', 'white'], $hoodie->variants()->orderBy('color')->distinct()->pluck('color')->all());
        $this->get(route('product', ['product' => $hoodie->slug]))
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('relaxed-navy-angle.jpg')
            ->assertSee('data-product-form');

        $variant = $hoodie->variants()->where('color', 'navy')->where('size', 'M')->sole();
        $this->assertSame(2, $variant->stock);
        $this->add($hoodie, ['color' => 'navy', 'size' => 'M', 'quantity' => 2]);
        $this->post(route('checkout.place'), $this->delivery(['quoted_total' => 1863]))->assertRedirect();
        $this->assertDatabaseHas('orders', ['subtotal' => 1798, 'shipping_fee' => 65, 'total' => 1863]);

        $hoodie->update(['price' => 950]);
        $this->seed(LaunchCatalogSeeder::class);

        $this->assertSame(950, $hoodie->fresh()->price);
        $this->assertSame(0, $variant->fresh()->stock);
        $this->assertDatabaseCount('products', 4);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function add(Product $product, array $options = []): void
    {
        $this->post(route('cart.add', ['product' => $product->slug]), $this->selection($options))->assertRedirect(route('cart'))->assertSessionHasNoErrors();
    }

    private function selection(array $options = []): array
    {
        return $options + ['color' => 'black', 'size' => 'L', 'quantity' => 1, 'design_mode' => 'ready', 'placement' => 'front'];
    }

    private function delivery(array $options = []): array
    {
        return $options + [
            'name' => 'Demo Customer', 'phone' => '+20 100 000 0000', 'email' => 'buyer@example.test',
            'zone' => ShippingZone::where('name', 'القاهرة')->value('id'), 'city' => 'Cairo', 'address' => '123 Demo Street, floor 2', 'payment' => 'cod',
        ];
    }

    private function image(string $name): UploadedFile
    {
        return new UploadedFile(public_path('assets/img/after-hours.jpg'), $name, 'image/jpeg', null, true);
    }
}
