<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

if (! app()->environment('production')) {
    Route::get('/', function () {
        return view('home', [
            'appName' => config('app.name'),
            'env' => app()->environment(),
            'apiKey' => (string) config('app.api_key'),
        ]);
    });

    Route::view('/docs/flow', 'docs.flow');
    Route::view('/docs/devops', 'docs.devops');

    Route::get('/postman/collection', function () {
        $path = resource_path('postman/booking-microservice.postman_collection.json');

        abort_unless(is_file($path), 404);

        return response()->download($path, 'booking-microservice.postman_collection.json', [
            'Content-Type' => 'application/json',
        ]);
    });
} else {
    Route::get('/', function () {
        return response()->json(['status' => 'ok', 'service' => 'Booking Microservice API']);
    });
}
