<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    //
    function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => Arr::first(Arr::first($validator->errors()->toArray())),
                'errors'  => $validator->errors(),
            ]);
        }
        $payload = $request->all();
        $payload['password'] = Hash::make($payload['password']);
        $userCreate = User::create($payload);
        $userCreate->token = $userCreate->createToken('authToken')->accessToken;
        return response()->json([
            'status' => true,
            'data'   => $userCreate
        ]);
    }

    function login(Request $request)
    {
        $payload = $request->all();
        $user = User::where('email', $payload['email'])->first();
        if ($user) {
            if (Hash::check($payload['password'], $user->password)) {
                $user->token = $user->createToken('authToken')->accessToken;
                return response()->json([
                    'status' => true,
                    'data'   => $user
                ]);
            }
        }
        return response()->json([
            'status'  => false,
            'message' => 'Username or password are wrongs'
        ]);
    }

    function getMe(){
        return response()->json(Auth::user());
    }
}
