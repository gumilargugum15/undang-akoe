<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CoupleController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DigitalEnvelopeController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\GuestbookController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\HonoreeController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\InvitationEventController;
use App\Http\Controllers\Api\InvitationStatisticsController;
use App\Http\Controllers\Api\LoveStoryController;
use App\Http\Controllers\Api\MusicController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\PaymentSettingController;
use App\Http\Controllers\Api\PublicStatsController;
use App\Http\Controllers\Api\ThemeCategoryController;
use App\Http\Controllers\Api\ThemeController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::get('invitations/{invitation:slug}', [InvitationController::class, 'publicShow']);
    Route::post('invitations/{invitation:slug}/rsvp', [GuestbookController::class, 'submit'])
        ->middleware('throttle:10,1');
    Route::get('invitations/{invitation:slug}/guestbook', [GuestbookController::class, 'wall']);
    Route::get('invitations/{invitation:slug}/envelopes', [DigitalEnvelopeController::class, 'publicIndex']);
    Route::post('invitations/{invitation:slug}/visit', [InvitationStatisticsController::class, 'track'])
        ->middleware('throttle:30,1');

    Route::get('banners', [BannerController::class, 'publicIndex']);
    Route::get('faqs', [FaqController::class, 'publicIndex']);
    Route::get('stats', [PublicStatsController::class, 'index']);

    // Same handlers as the authenticated /themes and /packages routes below — both already
    // branch on `$request->user()?->isAdmin()`, which safely resolves to null with no auth
    // middleware in front, falling through to the public/active-only view. Lets the landing
    // page's template gallery and pricing sections browse the catalog before signing up.
    Route::get('themes', [ThemeController::class, 'index']);
    Route::get('packages', [PackageController::class, 'index']);
});

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:auth');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:auth');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:auth');

    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
            ->middleware('throttle:6,1');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::put('profile/password', [AuthController::class, 'changePassword']);

    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::middleware('role:admin')->prefix('users')->group(function () {
        Route::get('/', [UserManagementController::class, 'index']);
        Route::patch('{user}/suspend', [UserManagementController::class, 'suspend']);
        Route::patch('{user}/activate', [UserManagementController::class, 'activate']);
        Route::patch('{user}/verify', [UserManagementController::class, 'verify']);
        Route::put('{user}/role', [UserManagementController::class, 'updateRole']);
        Route::delete('{user}', [UserManagementController::class, 'destroy']);
    });

    Route::get('themes', [ThemeController::class, 'index']);
    Route::post('themes', [ThemeController::class, 'store'])->middleware('role:admin');
    Route::get('themes/preview/{theme:slug}', [ThemeController::class, 'show']);
    Route::get('themes/{theme}', [ThemeController::class, 'show']);
    Route::put('themes/{theme}', [ThemeController::class, 'update'])->middleware('role:admin');
    Route::delete('themes/{theme}', [ThemeController::class, 'destroy'])->middleware('role:admin');
    Route::patch('themes/{theme}/publish', [ThemeController::class, 'publish'])->middleware('role:admin');
    Route::patch('themes/{theme}/unpublish', [ThemeController::class, 'unpublish'])->middleware('role:admin');
    Route::post('themes/{theme}/duplicate', [ThemeController::class, 'duplicate'])->middleware('role:admin');

    Route::get('theme-categories', [ThemeCategoryController::class, 'index']);
    Route::post('theme-categories', [ThemeCategoryController::class, 'store'])->middleware('role:admin');
    Route::put('theme-categories/{themeCategory}', [ThemeCategoryController::class, 'update'])->middleware('role:admin');
    Route::delete('theme-categories/{themeCategory}', [ThemeCategoryController::class, 'destroy'])->middleware('role:admin');

    Route::get('packages', [PackageController::class, 'index']);
    Route::post('packages', [PackageController::class, 'store'])->middleware('role:admin');
    Route::put('packages/{package}', [PackageController::class, 'update'])->middleware('role:admin');
    Route::delete('packages/{package}', [PackageController::class, 'destroy'])->middleware('role:admin');

    Route::middleware('role:admin')->prefix('payment-settings')->group(function () {
        Route::get('/', [PaymentSettingController::class, 'show']);
        Route::put('/', [PaymentSettingController::class, 'update']);
        Route::post('qris', [PaymentSettingController::class, 'uploadQris']);
        Route::delete('qris', [PaymentSettingController::class, 'removeQris']);
    });

    Route::get('banners', [BannerController::class, 'index']);
    Route::post('banners', [BannerController::class, 'store'])->middleware('role:admin');
    Route::put('banners/{banner}', [BannerController::class, 'update'])->middleware('role:admin');
    Route::delete('banners/{banner}', [BannerController::class, 'destroy'])->middleware('role:admin');

    Route::get('faqs', [FaqController::class, 'index']);
    Route::post('faqs', [FaqController::class, 'store'])->middleware('role:admin');
    Route::put('faqs/{faq}', [FaqController::class, 'update'])->middleware('role:admin');
    Route::delete('faqs/{faq}', [FaqController::class, 'destroy'])->middleware('role:admin');

    Route::apiResource('invitations', InvitationController::class);
    Route::patch('invitations/{invitation}/publish', [InvitationController::class, 'publish']);
    Route::patch('invitations/{invitation}/unpublish', [InvitationController::class, 'unpublish']);
    Route::patch('invitations/{invitation}/suspend', [InvitationController::class, 'suspend'])
        ->middleware('role:admin');
    Route::patch('invitations/{invitation}/reactivate', [InvitationController::class, 'reactivate'])
        ->middleware('role:admin');
    Route::patch('invitations/{invitation}/archive', [InvitationController::class, 'archive']);
    Route::patch('invitations/{invitation}/change-theme', [InvitationController::class, 'changeTheme']);
    Route::post('invitations/{invitation}/cover-photo', [InvitationController::class, 'uploadCoverPhoto']);
    Route::delete('invitations/{invitation}/cover-photo', [InvitationController::class, 'removeCoverPhoto']);
    Route::post('invitations/{invitation}/home-cover-photo', [InvitationController::class, 'uploadHomeCoverPhoto']);
    Route::delete('invitations/{invitation}/home-cover-photo', [InvitationController::class, 'removeHomeCoverPhoto']);

    Route::post('invitations/{invitation}/checkout', [CheckoutController::class, 'store']);

    Route::get('invitations/{invitation}/couples', [CoupleController::class, 'index']);
    Route::put('invitations/{invitation}/couples/{role}', [CoupleController::class, 'upsert'])
        ->whereIn('role', ['groom', 'bride']);
    Route::delete('invitations/{invitation}/couples/{role}', [CoupleController::class, 'destroy'])
        ->whereIn('role', ['groom', 'bride']);

    Route::get('invitations/{invitation}/honorees', [HonoreeController::class, 'index']);
    Route::post('invitations/{invitation}/honorees', [HonoreeController::class, 'store']);
    Route::put('invitations/{invitation}/honorees/{honoree}', [HonoreeController::class, 'update']);
    Route::delete('invitations/{invitation}/honorees/{honoree}', [HonoreeController::class, 'destroy']);

    Route::get('invitations/{invitation}/guests', [GuestController::class, 'index']);
    Route::post('invitations/{invitation}/guests', [GuestController::class, 'store']);
    Route::delete('invitations/{invitation}/guests/{guest}', [GuestController::class, 'destroy']);

    Route::get('invitations/{invitation}/events', [InvitationEventController::class, 'index']);
    Route::post('invitations/{invitation}/events', [InvitationEventController::class, 'store']);
    Route::put('invitations/{invitation}/events/{event}', [InvitationEventController::class, 'update']);
    Route::delete('invitations/{invitation}/events/{event}', [InvitationEventController::class, 'destroy']);

    Route::get('invitations/{invitation}/love-stories', [LoveStoryController::class, 'index']);
    Route::post('invitations/{invitation}/love-stories', [LoveStoryController::class, 'store']);
    Route::put('invitations/{invitation}/love-stories/{story}', [LoveStoryController::class, 'update']);
    Route::delete('invitations/{invitation}/love-stories/{story}', [LoveStoryController::class, 'destroy']);

    Route::get('invitations/{invitation}/music', [MusicController::class, 'show']);
    Route::put('invitations/{invitation}/music', [MusicController::class, 'upsert']);
    Route::delete('invitations/{invitation}/music', [MusicController::class, 'destroy']);

    Route::get('invitations/{invitation}/gallery', [GalleryController::class, 'index']);
    Route::post('invitations/{invitation}/gallery', [GalleryController::class, 'store']);
    Route::post('invitations/{invitation}/gallery/bulk', [GalleryController::class, 'storeBulk']);
    Route::put('invitations/{invitation}/gallery/{item}', [GalleryController::class, 'update']);
    Route::delete('invitations/{invitation}/gallery/{item}', [GalleryController::class, 'destroy']);

    Route::get('invitations/{invitation}/rsvp', [GuestbookController::class, 'index']);
    Route::get('invitations/{invitation}/rsvp/summary', [GuestbookController::class, 'summary']);
    Route::patch('invitations/{invitation}/rsvp/{rsvp}/approve', [GuestbookController::class, 'approve']);
    Route::patch('invitations/{invitation}/rsvp/{rsvp}/reject', [GuestbookController::class, 'reject']);
    Route::delete('invitations/{invitation}/rsvp/{rsvp}', [GuestbookController::class, 'destroy']);

    Route::get('invitations/{invitation}/envelopes', [DigitalEnvelopeController::class, 'index']);
    Route::post('invitations/{invitation}/envelopes', [DigitalEnvelopeController::class, 'store']);
    Route::put('invitations/{invitation}/envelopes/{envelope}', [DigitalEnvelopeController::class, 'update']);
    Route::delete('invitations/{invitation}/envelopes/{envelope}', [DigitalEnvelopeController::class, 'destroy']);

    Route::get('invitations/{invitation}/statistics', [InvitationStatisticsController::class, 'summary']);

    Route::get('transactions', [TransactionController::class, 'index']);
    Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
    Route::post('transactions/{transaction}/proof', [TransactionController::class, 'uploadProof']);
    Route::patch('transactions/{transaction}/cancel', [TransactionController::class, 'cancel']);
    Route::patch('transactions/{transaction}/approve', [TransactionController::class, 'approve'])
        ->middleware('role:admin');
    Route::patch('transactions/{transaction}/reject', [TransactionController::class, 'reject'])
        ->middleware('role:admin');
});
