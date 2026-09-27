<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Game\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BluffController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn() => response()->json(['status' => 'ok']));

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/leaderboard', [HomeController::class, 'leaderboard'])->name('leaderboard');
Route::get('/statistics', [HomeController::class, 'statistics'])->name('statistics');

require __DIR__ . '/auth.php';

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('questions')->name('questions.')->group(function () {
        Route::get('/submit', [QuestionSubmissionController::class, 'create'])->name('submit');
        Route::post('/submit', [QuestionSubmissionController::class, 'store'])->name('store');
        Route::get('/my-submissions', [QuestionSubmissionController::class, 'mySubmissions'])->name('my-submissions');
    });

    Route::prefix('game')->name('game.')->group(function () {
        Route::get('/create', [GameController::class, 'create'])->name('create');
        Route::post('/create', [GameController::class, 'store'])->name('store');

        Route::middleware('can:view,session')->group(function () {
            Route::get('/{session:code}/categories', [GameController::class, 'selectCategories'])->name('categories');
            Route::post('/{session:code}/categories', [GameController::class, 'attachCategories'])->name('categories.attach');
            Route::get('/{session:code}/lobby', [GameController::class, 'lobby'])->name('lobby');
            Route::post('/{session:code}/start', [GameController::class, 'start'])->name('start');
            Route::get('/{session:code}/board', [GameController::class, 'board'])->name('board');
            Route::get('/{session:code}/result', [GameController::class, 'result'])->name('result');

            Route::prefix('{session:code}')->group(function () {
                Route::get('/round/{round}/question', [GameController::class, 'getQuestion'])->name('question');
                Route::post('/round/{round}/answer', [GameController::class, 'submitAnswer'])->name('answer');
                Route::post('/round/{round}/power/remove', [GameController::class, 'usePowerRemove'])->name('power.remove');
                Route::post('/round/{round}/power/steal', [GameController::class, 'usePowerSteal'])->name('power.steal');
            });
        });
    });

    Route::prefix('bluff')->name('bluff.')->group(function () {
        Route::get('/', [BluffController::class, 'index'])->name('index');
        Route::post('/create', [BluffController::class, 'store'])->name('store');
        Route::post('/join', [BluffController::class, 'join'])->name('join');
        Route::get('/{code}/lobby', [BluffController::class, 'lobby'])->name('lobby');
        Route::get('/{code}/lobby-data', [BluffController::class, 'lobbyData'])->name('lobby.data');
        Route::post('/{code}/start', [BluffController::class, 'start'])->name('start');
        Route::post('/{code}/display-name', [BluffController::class, 'updateDisplayName'])->name('display.name');
        Route::get('/{code}/play', [BluffController::class, 'play'])->name('play');
        Route::get('/{code}/state', [BluffController::class, 'getState'])->name('state');
        Route::post('/{code}/advance', [BluffController::class, 'advanceRound'])->name('advance');
        Route::get('/{code}/results', [BluffController::class, 'results'])->name('results');
        Route::post('/{code}/round/{round}/select-category', [BluffController::class, 'selectCategory'])->name('select.category');
        Route::post('/{code}/round/{round}/answer', [BluffController::class, 'submitAnswer'])->name('answer');
        Route::post('/{code}/round/{round}/vote', [BluffController::class, 'submitVote'])->name('vote');
        Route::get('/{code}/history', [BluffController::class, 'getHistory'])->name('history');
    });
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', Admin\CategoryController::class);
    Route::post('categories/{category}/featured', [Admin\CategoryController::class, 'toggleFeatured'])->name('categories.featured');

    Route::resource('questions', Admin\QuestionController::class);
    Route::get('questions-submissions', [Admin\QuestionController::class, 'submissions'])->name('questions.submissions');
    Route::get('questions-submissions/{submission}/review', [Admin\QuestionController::class, 'reviewSubmission'])->name('questions.review');
    Route::post('questions-submissions/{submission}/approve', [Admin\QuestionController::class, 'approveSubmission'])->name('questions.approve');
    Route::post('questions-submissions/{submission}/reject', [Admin\QuestionController::class, 'rejectSubmission'])->name('questions.reject');

    Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [Admin\UserController::class, 'show'])->name('users.show');
    Route::post('users/{user}/toggle-status', [Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
});