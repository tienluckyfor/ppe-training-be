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


Route::get('/auth-handle', function (Request $request) {
    $state = json_decode($request->state, true);
    $client = new Client();
    if ($state['platform'] == 'google') {
        $data = [
            'client_id'     => '970342387179-a4rkio0kslht19rm2flc0igsfvl30bgg.apps.googleusercontent.com',
            'client_secret' => 'I9EP1ZWUHTR-9TD60r6S-3Ir',
            'redirect_uri'  => 'https://40e2fc5f7ff8.ngrok.io/api/auth-handle',
            'grant_type'    => 'authorization_code',
            'code'          => $request->code,
        ];

        $res = $client->request('POST', "https://oauth2.googleapis.com/token",
            [
                'headers'     => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $data
            ]
        );
        $accessToken = json_decode($res->getBody()->getContents(), true);

        $res = $client->request('GET', "https://www.googleapis.com/oauth2/v2/userinfo",
            [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken['access_token']}",
                ],
            ]
        );
        $info = json_decode($res->getBody()->getContents(), true);
        dd(['$accessToken' => $accessToken, '$info' => $info]);
    }
    if ($state['platform'] == 'facebook') {
        $res = $client->request('GET', "https://graph.facebook.com/v9.0/oauth/access_token",
            [
                'query' => [
                    'client_id'     => '158441679483630',
                    'client_secret' => 'a46f2b00ca083a557db7bae4f7ebc889',
                    'redirect_uri'  => 'https://40e2fc5f7ff8.ngrok.io/api/auth-handle',
                    'code'          => $request->code,
                ]
            ]
        );
        $accessToken = json_decode($res->getBody()->getContents(), true);
        $res = $client->request('GET', "https://graph.facebook.com/v9.0/me",
            [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken['access_token']}",
                ],
                'query'=>[
                    'fields'=> 'id,email,first_name,last_name,picture'
                ]
            ]
        );
        $info = json_decode($res->getBody()->getContents(), true);
        dd(['$accessToken' => $accessToken, '$info' => $info]);
    }
});

Route::get('/generate-url', function (Request $request) {
    if ($request->platform == 'google') {
        $params = http_build_query([
            'client_id'     => '970342387179-a4rkio0kslht19rm2flc0igsfvl30bgg.apps.googleusercontent.com',
            'redirect_uri'  => 'https://40e2fc5f7ff8.ngrok.io/api/auth-handle',
            'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
            'response_type' => 'code',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => json_encode([
                'platform' => $request->platform,
            ]),
        ]);
        return response()->json([
            'status' => true,
            'data'   => "https://accounts.google.com/o/oauth2/v2/auth?{$params}"
        ]);
    }
    if ($request->platform == 'facebook') {
        $params = http_build_query([
            'client_id'     => '158441679483630',
            'redirect_uri'  => 'https://40e2fc5f7ff8.ngrok.io/api/auth-handle',
            'scope'         => 'email',
            'response_type' => 'code',
            'auth_type'     => 'rerequest',
            'display'       => 'popup',
            'state'         => json_encode([
                'platform' => $request->platform,
            ]),
        ]);
        return response()->json([
            'status' => true,
            'data'   => "https://www.facebook.com/v9.0/dialog/oauth?{$params}"
        ]);
    }
});
