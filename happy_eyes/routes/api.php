<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/user/register', function (Request $request) {
    $payload = $request->all();
    $payload['password'] = \Illuminate\Support\Facades\Hash::make($payload['password']);
    $userCreate = \App\Models\User::create($payload);
    $userCreate->token = $userCreate->createToken('authToken')->accessToken;
    return response()->json([
        'status' => true,
        'data'   => $userCreate,
    ]);
});

Route::middleware('auth:api')->get('/user/me', function (Request $request) {
    return response()->json(Auth::user());
});

Route::post('/user/login', function (Request $request) {
    $payload = $request->all();
    $user = \App\Models\User::where('email', $payload['email'])->first();
    if ($user) {
        if (Hash::check($payload['password'], $user->password)) {
            $user->token = $user->createToken('authToken')->accessToken;
            return response()->json([
                'status' => true,
                'data'   => $user,
            ]);
        }
    }
    return response()->json([
        'status'  => false,
        'message' => 'Username or password are wrongs.',
    ]);
});

Route::middleware('auth:api')->post('/posts', function (Request $request) {
    $payload = $request->all();
    $payload['user_id'] = Auth::id();
    $postCreate = \App\Models\Post::create($payload);
    return response()->json([
        'status' => true,
        'data'   => $postCreate,
    ]);
});
