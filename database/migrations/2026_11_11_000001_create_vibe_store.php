<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->boolean('is_admin')->default(false));
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('category', 20)->index();
            $table->text('description');
            $table->unsignedInteger('price');
            $table->unsignedInteger('custom_fee')->default(0);
            $table->string('image_path')->nullable();
            $table->string('badge', 30)->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('featured')->default(false);
            $table->boolean('ready_enabled')->default(true);
            $table->boolean('custom_enabled')->default(true);
            $table->timestamps();
        });
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('color', 24);
            $table->string('size', 10);
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('active')->default(true);
            $table->unique(['product_id', 'color', 'size']);
            $table->timestamps();
        });
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('fee');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 24)->unique();
            $table->string('name');
            $table->string('phone', 11)->index();
            $table->string('email')->nullable();
            $table->string('governorate');
            $table->string('city');
            $table->string('address', 300);
            $table->text('notes')->nullable();
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('shipping_fee');
            $table->unsignedInteger('total');
            $table->string('payment_method', 20);
            $table->unsignedInteger('deposit_due');
            $table->unsignedInteger('paid_amount')->default(0);
            $table->unsignedInteger('refunded_amount')->default(0);
            $table->string('status', 20)->default('new')->index();
            $table->string('carrier', 80)->nullable();
            $table->string('shipment_code', 100)->nullable();
            $table->timestamps();
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained()->restrictOnDelete();
            $table->string('product_name');
            $table->string('size', 10);
            $table->string('color', 24);
            $table->unsignedInteger('unit_price');
            $table->unsignedSmallInteger('quantity');
            $table->string('design_mode', 12);
            $table->string('placement', 10);
            $table->text('idea')->nullable();
            $table->string('upload_path')->nullable();
            $table->string('design_status', 12)->nullable();
            $table->string('preview_path')->nullable();
            $table->string('production_path')->nullable();
            $table->unsignedInteger('preview_version')->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->text('change_request')->nullable();
            $table->timestamps();
        });
        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('message');
            $table->boolean('visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['order_events', 'order_items', 'orders', 'shipping_zones', 'variants', 'products'] as $name) {
            Schema::dropIfExists($name);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_admin'));
    }
};
