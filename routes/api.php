<?php

use App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BluffGameController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\KuraiyatController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\PracticeController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuestionSubmissionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

Route::get('/leaderboard', [LeaderboardController::class, 'index']);
Route::get('/leaderboard/statistics', [LeaderboardController::class, 'statistics']);
Route::get('/leaderboard/categories', [LeaderboardController::class, 'categoryStats']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);

    Route::get('/practice/categories', [PracticeController::class, 'categories']);
    Route::get('/practice/{category}', [PracticeController::class, 'nextQuestion']);
    Route::post('/practice/answer', [PracticeController::class, 'answer']);

    Route::get('/games', [GameController::class, 'index']);
    Route::post('/games', [GameController::class, 'store']);
    Route::get('/games/{session:code}', [GameController::class, 'show']);
    Route::get('/games/{session:code}/categories', [GameController::class, 'categories']);
    Route::post('/games/{session:code}/categories', [GameController::class, 'attachCategories']);
    Route::post('/games/{session:code}/start', [GameController::class, 'start']);
    Route::get('/games/{session:code}/board', [GameController::class, 'board']);
    Route::get('/games/{session:code}/round/{round}/question', [GameController::class, 'getQuestion']);
    Route::post('/games/{session:code}/round/{round}/answer', [GameController::class, 'submitAnswer']);
    Route::post('/games/{session:code}/round/{round}/power/remove', [GameController::class, 'usePowerRemove']);
    Route::post('/games/{session:code}/round/{round}/power/steal', [GameController::class, 'usePowerSteal']);
    Route::get('/games/{session:code}/result', [GameController::class, 'result']);

    Route::get('/bluff', [BluffGameController::class, 'index']);
    Route::post('/bluff', [BluffGameController::class, 'store']);
    Route::post('/bluff/join', [BluffGameController::class, 'join']);
    Route::get('/bluff/{code}/lobby', [BluffGameController::class, 'lobby']);
    Route::post('/bluff/{code}/start', [BluffGameController::class, 'start']);
    Route::get('/bluff/{code}/state', [BluffGameController::class, 'state']);
    Route::post('/bluff/{code}/advance', [BluffGameController::class, 'advanceRound']);
    Route::get('/bluff/{code}/results', [BluffGameController::class, 'results']);
    Route::get('/bluff/{code}/history', [BluffGameController::class, 'history']);
    Route::post('/bluff/{code}/round/{round}/answer', [BluffGameController::class, 'submitAnswer']);
    Route::post('/bluff/{code}/round/{round}/vote', [BluffGameController::class, 'submitVote']);

    Route::get('/kuraiyat/games', [KuraiyatController::class, 'games']);
    Route::get('/kuraiyat', [KuraiyatController::class, 'index']);
    Route::post('/kuraiyat', [KuraiyatController::class, 'store']);
    Route::get('/kuraiyat/{code}/state', [KuraiyatController::class, 'state']);
    Route::post('/kuraiyat/{code}/answer', [KuraiyatController::class, 'submitAnswer']);
    Route::get('/kuraiyat/{code}/clue', [KuraiyatController::class, 'clue']);
    Route::post('/kuraiyat/{code}/skip', [KuraiyatController::class, 'skip']);
    Route::get('/kuraiyat/{code}/results', [KuraiyatController::class, 'results']);

    Route::post('/questions/submit', [QuestionSubmissionController::class, 'store']);
    Route::get('/questions/my-submissions', [QuestionSubmissionController::class, 'mySubmissions']);

    Route::get('/leaderboard/user-stats', [LeaderboardController::class, 'userStats']);

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index']);

        Route::get('/categories', [Admin\CategoryController::class, 'index']);
        Route::post('/categories', [Admin\CategoryController::class, 'store']);
        Route::get('/categories/{category}', [Admin\CategoryController::class, 'show']);
        Route::post('/categories/{category}', [Admin\CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [Admin\CategoryController::class, 'destroy']);
        Route::post('/categories/{category}/featured', [Admin\CategoryController::class, 'toggleFeatured']);

        Route::get('/questions', [Admin\QuestionController::class, 'index']);
        Route::post('/questions', [Admin\QuestionController::class, 'store']);
        Route::get('/questions/{question}', [Admin\QuestionController::class, 'show']);
        Route::post('/questions/{question}', [Admin\QuestionController::class, 'update']);
        Route::delete('/questions/{question}', [Admin\QuestionController::class, 'destroy']);

        Route::get('/questions-submissions', [Admin\QuestionController::class, 'submissions']);
        Route::get('/questions-submissions/{submission}', [Admin\QuestionController::class, 'reviewSubmission']);
        Route::post('/questions-submissions/{submission}/approve', [Admin\QuestionController::class, 'approveSubmission']);
        Route::post('/questions-submissions/{submission}/reject', [Admin\QuestionController::class, 'rejectSubmission']);

        Route::get('/users', [Admin\UserController::class, 'index']);
        Route::get('/users/{user}', [Admin\UserController::class, 'show']);
        Route::post('/users/{user}/toggle-status', [Admin\UserController::class, 'toggleStatus']);
        Route::delete('/users/{user}', [Admin\UserController::class, 'destroy']);
    });
});
