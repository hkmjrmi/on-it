<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\User;
use App\Http\Controllers\User\Profile;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Route;
use Onpay\Core\Http\Middleware\IsUserBanned;
use Onpay\Core\Http\Middleware\SetUserLocale;

Route::middleware('guest:user')->group(function () {
    // Login
    Route::get('login', [Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [Auth\AuthenticatedSessionController::class, 'store']);

    // Forgot password
    Route::get('forgot-password', [Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [Auth\PasswordResetLinkController::class, 'store'])->name('password.email');

    // Reset password
    Route::get('reset-password/{token}', [Auth\NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [Auth\NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware(['auth:user', SetUserLocale::class])->group(function () {
    // Email verification
    Route::get('verify-email', Auth\EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', Auth\VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [Auth\EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');

    // Confirm password
    Route::get('confirm-password', [Auth\ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [Auth\ConfirmablePasswordController::class, 'store']);

    // Logout
    Route::post('logout', [Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware([
    Authenticate::using('user'),
    EnsureEmailIsVerified::redirectTo('user.verification.notice'),
    SetUserLocale::class,
    IsUserBanned::using('user.login'),
])->group(function () {
    // Dashboard
    Route::get('/', [User\IssueController::class, 'index'])->name('main');
    Route::view('status-guide', 'user.issues.status_guide')->name('status_guide');

    // Issues
    Route::get('my-issues', [User\IssueController::class, 'mine'])->name('my_issues');
    Route::get('reports', [User\ReportController::class, 'index'])->name('reports');
    Route::get('board', [User\IssueBoardController::class, 'index'])->name('board');
    Route::patch('issues/{issue}/board', [User\IssueBoardController::class, 'move'])->name('issues.board.move');
    Route::post('issues/bulk', User\IssueBulkController::class)->name('issues.bulk');
    Route::resource('issues', User\IssueController::class)->only(['index', 'create', 'store', 'show', 'update', 'destroy']);
    Route::post('issues/{issue}/comments', [User\IssueCommentController::class, 'store'])->name('issues.comments.store');
    Route::patch('issues/{issue}/comments/{comment}', [User\IssueCommentController::class, 'update'])->scopeBindings()->name('issues.comments.update');
    Route::delete('issues/{issue}/comments/{comment}', [User\IssueCommentController::class, 'destroy'])->scopeBindings()->name('issues.comments.destroy');
    Route::post('issues/{issue}/archive', [User\IssueArchiveController::class, 'store'])->name('issues.archive');
    Route::delete('issues/{issue}/archive', [User\IssueArchiveController::class, 'destroy'])->name('issues.unarchive');
    Route::patch('issues/{issue}/workflow', User\IssueWorkflowController::class)->name('issues.workflow');
    Route::get('attachments/{attachment}', User\IssueAttachmentController::class)->name('attachments.show');

    // Saved filters
    Route::post('saved-filters', [User\SavedFilterController::class, 'store'])->name('saved_filters.store');
    Route::patch('saved-filters/{savedFilter}', [User\SavedFilterController::class, 'update'])->name('saved_filters.update');
    Route::delete('saved-filters/{savedFilter}', [User\SavedFilterController::class, 'destroy'])->name('saved_filters.destroy');

    // Notifications
    Route::get('notifications', [User\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [User\NotificationController::class, 'readAll'])->name('notifications.read_all');
    Route::get('notifications/{notification}', [User\NotificationController::class, 'show'])->name('notifications.show');

    // Profile
    Route::name('profile.')->prefix('profile')->group(function () {
        Route::singleton('user', Profile\UserController::class);
        Route::singleton('password', Profile\PasswordController::class);
        Route::put('locale', LocaleController::class)->name('locale');
    });
});
