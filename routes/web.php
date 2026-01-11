<?php

use App\Models\Section;
use App\Models\Slider;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    $sections = Section::with('books.writer')
        ->orderBy('order_column')
        ->get();

    $sliders = Slider::where('is_active', true)
        ->orderBy('order_column')
        ->get();

    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'sections' => $sections,
        'sliders' => $sliders,
        'title' => 'এলাকার বাজার - বাংলাদেশের সবচেয়ে সাশ্রয়ী অনলাইন বইয়ের দোকান',
        'description' => 'সবচেয়ে কম দামে বই কিনুন এলাকার বাজার থেকে। হাজারো বাংলা ও ইংরেজি বই, লেখক, প্রকাশনী এক জায়গায়। বিশেষ ছাড় ও অফার সহ ঘরে বসে বই অর্ডার করুন। ফ্রি হোম ডেলিভারি সারা বাংলাদেশে।',
    ]);
})->name('home');

// Single book page
Route::get('/book/{book:slug}', [BookController::class, 'show'])->name('book.show');

// cart page
Route::get('/cart', function () {
    return Inertia::render('Cart', [
        'title' => 'আপনার কার্ট | এলাকাডটকম',
        'description' => 'আপনার পছন্দের বইগুলো কার্টে জমা করুন এবং সহজেই অর্ডার সম্পন্ন করুন।',
    ]);
})->name('cart');

// checkout page
Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [App\Http\Controllers\CheckoutController::class, 'store'])->name('checkout.store');

// Catalog page
Route::get('/catalog', [App\Http\Controllers\CatalogController::class, 'index'])->name('catalog');

// Order Success page
Route::get('/order-success/{orderId}/{invoiceNo}/{totalPrice}', function ($orderId, $invoiceNo, $totalPrice) {
    return Inertia::render('OrderSuccess', [
        'orderId' => (int) $orderId,
        'invoiceNo' => $invoiceNo,
        'totalPrice' => (float) $totalPrice,
    ]);
})->name('order.success');

// all books page
Route::get('/allbooks', [BookController::class, 'index'])->name('allbooks');

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// google auth routes
use App\Http\Controllers\Auth\SocialAuthController;

Route::controller(SocialAuthController::class)->group(function () {
    Route::get('auth/google', 'redirectToGoogle')->name('auth.google');
    Route::get('auth/google/callback', 'handleGoogleCallback');
});

// Legal pages
Route::get('/terms', function () {
    return Inertia::render('Terms', [
        'title' => 'শর্তাবলী | এলাকাডটকম',
        'description' => 'আমাদের পরিষেবার শর্তাবলী বিস্তারিতভাবে পড়ুন।',
    ]);
})->name('terms');

Route::get('/privacy', function () {
    return Inertia::render('Privacy', [
        'title' => 'গোপনীয়তা নীতি | এলাকাডটকম',
        'description' => 'আপনার তথ্যের নিরাপত্তা ও গোপনীয়তা আমাদের অঙ্গীকার।',
    ]);
})->name('privacy');

// Search suggestions
Route::get('/api/search-suggestions', [\App\Http\Controllers\SearchController::class, 'suggestions'])->name('api.search.suggestions');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('user.password.update');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders');

    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::patch('/addresses/{address}/set-default', [AddressController::class, 'setDefault'])->name('addresses.setDefault');

    Route::get('/settings', function () {
        return Inertia::render('Settings/Index');
    })->name('settings');

    Route::get('/wishlist', [\App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist');

    Route::post('/wishlist/toggle', [\App\Http\Controllers\WishlistController::class, 'toggle'])->name('wishlist.toggle');

    Route::get('/payment-methods', function () {
        return Inertia::render('PaymentMethods/Index');
    })->name('payment-methods');
});

require __DIR__.'/settings.php';
