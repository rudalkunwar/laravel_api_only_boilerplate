<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    require __DIR__.'/api/public.php';
    require __DIR__.'/api/user.php';
    require __DIR__.'/api/admin.php';
});
