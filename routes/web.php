<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Models\User;
use App\Jobs\LogMessageJob;
use Illuminate\Support\Facades\Bus;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Just a test route to check my Log message and Queue Job
Route::get('/log-message', function () {
    $user = User::where('id',3)->first();
    $time = '19:00';
    // // Get the content from a specific view (replace 'sample-view' with your view name)
    // $viewContent = View::make('notification_message',['user' => $user, 'scheduledTime' => $time])->render();

    // // Log the content to the laravel.log file
    // Log::info('Log message from view: ' . $viewContent);

    Bus::dispatch(new LogMessageJob($user, $time));

    return 'Message logged to laravel.log!';
});
