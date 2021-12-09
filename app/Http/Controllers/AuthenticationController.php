<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthenticationController extends Controller
{
    public function register(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        if ($request->hasFile('avatar')) {
            $uploadedFileUrl = cloudinary()->upload($request->file('avatar')->getRealPath())->getSecurePath();
        }

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'avatar' => $uploadedFileUrl ?? null,
            'password' => bcrypt($input['password']),
            'role_id' => 3
        ]);
        $token = $user->createToken('token')->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token
        ];

        auth()->logoutOtherDevices($request->password);

        return $this->responseSuccess('Registration Successful', $data, 201);

        // return $this->success([
        //     'token' => $user->createToken('tokens')->plainTextToken
        // ]);
    }
    //use this method to login users
    public function login(Request $request)
    {
        $input = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($input)) {
            return $this->responseFailed('Email or Password is incorrect', '', 401);
        }

        $user = User::where('email', $input['email'])->with('role')->first();
        $token = $user->createToken('token')->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token
        ];

        $user->update(['last_seen' => Carbon::now()]);

        return $this->responseSuccess('Login Successful', $data, 200);
    }

    // this method signs out users by removing tokens
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout Successful'
        ]);
    }
}
