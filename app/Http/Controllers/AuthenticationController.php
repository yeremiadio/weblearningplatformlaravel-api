<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthenticationController extends Controller
{

    public function checkAuth()
    {
        if (Auth::check()) {
            return $this->responseSuccess('Authenticated', '', 200);
        }

        return $this->responseFailed('Unauthenticated', '', 401);
    }

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
            'password' => bcrypt($input['password']),
            'avatar' => empty($request->file('avatar')) ? null : $uploadedFileUrl
        ]);

        if (!empty($input['role'])) {
            $user->assignRole($input['role']);
        }
        $token = $user->createToken('token')->plainTextToken;
        $data = [
            'user' => User::where('id', $user->id)->with('roles')->first(),
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

        $user = User::where('email', $input['email'])->first();
        $token = $user->createToken('token')->plainTextToken;
        $data = [
            'user' => $user,
            'token' => $token
        ];

        $user->update(['last_seen' => Carbon::now()]);

        return $this->responseSuccess('Login Successful', $data, 200);
    }

    public function update($id, Request $request)
    {
        $user = User::where('id', $id)->first();
        if (!$user) return $this->responseFailed('Data not found', '', 404);

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string',
            'email' => 'required|string|email',
            'role_id' => 'required|numeric',
            'avatar' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error validation', $validator->errors(), 400);
        }

        $oldAvatar = $user->avatar;
        if ($request->hasFile('avatar')) {
            $input['avatar'] = cloudinary()->upload($request->file('avatar')->getRealPath())->getSecurePath();
        } else {
            $input['avatar'] = $oldAvatar;
        }

        $user->update([
            'name' => $input['name'],
            'email' => $input['email'],
            'avatar' => $input['avatar'],
            'role_id' => $input['role_id'],
        ]);

        $data = User::where('id', $id)->with(['role' => function ($q) {
            $q->select('id', 'role_name');
        }])->first();

        return $this->responseSuccess('Profile updated successfully', $data, 200);
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
