<?php

use App\Http\Controllers\AIProcessingSubtitleController;
use App\Http\Controllers\Home;
use App\Http\Controllers\ProcessingSubtitleController;
use Illuminate\Support\Facades\Route;

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

Route::redirect('/', 'auth/login');

Route::middleware(['auth', 'verified'])->group(fn () => Route::get('/home', Home::class)->name('home'));

include_once __DIR__.'/auth.php';
include_once __DIR__.'/my.php';

Route::get('/resource/project/processing-subtitle/{media}', ProcessingSubtitleController::class)
    ->name('resource.project.processing-subtitle');
Route::get('/resource/project/ai-translation/{media}', AIProcessingSubtitleController::class)
    ->name('resource.project.ai-translation');

Route::post('/resource/project/ai-translation/{media}', App\Http\Controllers\StoreAIProcessingSubtitleController::class);
Route::get('/subtitles/translate/{jobId}/status', [App\Http\Controllers\StoreAIProcessingSubtitleController::class, 'status']);
Route::get('/subtitles/translate/{jobId}/result', [App\Http\Controllers\StoreAIProcessingSubtitleController::class, 'result']);
