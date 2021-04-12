<?php
$redirect_url = URL::to('/api/auth-handle');
$redirect_url = str_replace('http:', 'https:', $redirect_url);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('user')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::middleware('auth:api')->get('/me', [UserController::class, 'getMe']);
});

Route::middleware('auth:api')->resource('posts', PostController::class);
Route::get('all-posts', function () {
    $users = \App\Models\User::get();
    $posts = \App\Models\Post::get();
    return response()->json([
        'status' => true,
        'data'   => [
            'posts' => $posts,
            'users' => $users,
        ]
    ]);
});

Route::get('/auth-handle', [AuthController::class, 'authHandle']);
Route::get('/auth-generate-url', [AuthController::class, 'generateUrl']);
