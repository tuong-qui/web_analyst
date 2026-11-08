<?php

use App\Http\Controllers\AssociationRuleController;
use App\Http\Controllers\CustomerImportController;
use App\Http\Controllers\CustomerSegmentController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\OrderImportController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\OrderItemImportController;
use App\Http\Controllers\OrderPaymentController;
use App\Http\Controllers\OrderPaymentImportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\ProductRecommendationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/customers/segments', [CustomerSegmentController::class, 'index'])->name('customers.segments');
    Route::get('/basket-analysis/rules', [AssociationRuleController::class, 'index'])->name('basket.rules');
    Route::post('/basket-analysis/rules', [AssociationRuleController::class, 'index'])->name('basket.rules.analyze');
    Route::get('/basket-analysis/recommendations', [ProductRecommendationController::class, 'index'])->name('basket.recommendations');
    Route::post('/basket-analysis/recommendations', [ProductRecommendationController::class, 'index'])->name('basket.recommendations.generate');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/', [WebController::class, 'index'])->name('home');
Route::middleware('auth')->group(function () {
    Route::get('/data', [DatasetController::class, 'index'])->name('data');
    Route::post('/data', [DatasetController::class, 'store'])->name('datasets.store');
    Route::post('/data/select', [DatasetController::class, 'select'])->name('datasets.select');
    Route::get('/order_payments', [OrderPaymentController::class, 'index'])->name('order_payments');
    Route::post('/order_payments/import', [OrderPaymentImportController::class, 'store'])->name('order_payments.import');
    Route::get('/order_items', [OrderItemController::class, 'index'])->name('order_items');
    Route::post('/order_items/import', [OrderItemImportController::class, 'store'])->name('order_items.import');
    Route::get('/orders', [WebController::class, 'orders'])->name('orders');
    Route::post('/orders/import', [OrderImportController::class, 'store'])->name('orders.import');
    Route::get('/customers', [WebController::class, 'customers'])->name('customers');
    Route::post('/customers/import', [CustomerImportController::class, 'store'])->name('customers.import');
    Route::post('/products/import', [ProductImportController::class, 'store'])->name('products.import');
    Route::get('/products', [ProductController::class, 'index'])->name('products');
});
