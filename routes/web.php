<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CatalogAdminController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TrackingController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::get('/shop', [StoreController::class, 'shop'])->name('shop');
Route::get('/customize', [StoreController::class, 'customize'])->name('customize');
Route::get('/products/{product:slug}', [StoreController::class, 'product'])->name('product');
Route::get('/media/products/{product}', [StoreController::class, 'image'])->name('product.image');
Route::post('/products/{product:slug}/cart', [StoreController::class, 'add'])->middleware('throttle:25,1')->block(45, 10)->name('cart.add');
Route::get('/cart', [StoreController::class, 'cart'])->name('cart');
Route::patch('/cart/{key}', [StoreController::class, 'updateCart'])->block(45, 10)->name('cart.update');
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'place'])->middleware('throttle:12,1')->block(45, 10)->name('checkout.place');
Route::get('/track', [TrackingController::class, 'index'])->name('tracking');
Route::post('/track', [TrackingController::class, 'lookup'])->middleware('throttle:10,1')->name('tracking.lookup');
Route::get('/track/{number}', [TrackingController::class, 'show'])->name('tracking.show');
Route::get('/track/{order:number}/items/{item}/preview', [TrackingController::class, 'preview'])->name('tracking.preview');
Route::post('/track/{order:number}/items/{item}/review', [TrackingController::class, 'review'])->middleware('throttle:20,1')->name('tracking.review');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AdminController::class, 'loginPage'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->middleware('throttle:6,1')->name('authenticate');

    Route::middleware(AdminOnly::class)->group(function (): void {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
        Route::get('/orders/{order:number}', [AdminController::class, 'order'])->name('orders.show');
        Route::patch('/orders/{order:number}/status', [AdminController::class, 'transition'])->name('orders.status');
        Route::post('/orders/{order:number}/payment', [AdminController::class, 'payment'])->name('orders.payment');
        Route::post('/orders/{order:number}/items/{item}/artwork', [AdminController::class, 'artwork'])->name('orders.artwork');
        Route::get('/orders/{order:number}/items/{item}/files/{kind}', [AdminController::class, 'file'])->name('orders.file');
        Route::get('/customers', [AdminController::class, 'customers'])->name('customers');
        Route::get('/products', [CatalogAdminController::class, 'products'])->name('products');
        Route::get('/products/create', [CatalogAdminController::class, 'edit'])->name('products.create');
        Route::post('/products', [CatalogAdminController::class, 'save'])->name('products.store');
        Route::get('/products/{product}/edit', [CatalogAdminController::class, 'edit'])->name('products.edit');
        Route::patch('/products/{product}', [CatalogAdminController::class, 'save'])->name('products.update');
        Route::post('/products/{product}/variants', [CatalogAdminController::class, 'addVariant'])->name('variants.store');
        Route::patch('/products/{product}/variants/{variant}', [CatalogAdminController::class, 'updateVariant'])->name('variants.update');
        Route::get('/shipping', [CatalogAdminController::class, 'shipping'])->name('shipping');
        Route::patch('/shipping/{zone}', [CatalogAdminController::class, 'updateShipping'])->name('shipping.update');
    });
});
