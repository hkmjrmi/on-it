<?php

Route::post('github/webhook', App\Http\Controllers\Api\GitHubWebhookController::class)->name('github.webhook');
