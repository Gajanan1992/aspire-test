<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    public function register(Request $request)
    {
        // $attr = $request->validate(User::$rules);
        $validator = Validator::make($request->all(), User::$rules);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error.', $validator->errors(), 400);
        }

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        $response['token'] =  $user->createToken('auth-token')->plainTextToken;
        $response['name'] =  $user->name;

        return $this->successResponse('Registeration successfull!.', $response);
    }

    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            $response = [
                'token' =>  $user->createToken('auth-token')->plainTextToken,
                'name' =>  $user->name
            ];

            return $this->successResponse('User successfully logged-in.', $response);
        } else {
            return $this->errorResponse('Unauthorized.', ['error' => 'Unauthorized'], 403);
        }
    }

    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();

        return $this->successResponse('Logout successfully.');
    }
}
