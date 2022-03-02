<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{

    public function checkAuth()
    {
        if (Auth::check()) {
            return $this->responseSuccess('Authenticated', '', 200);
        }

        auth()->logoutOtherDevices(Hash::check(auth()->user()->password));

        return $this->responseFailed('Unauthenticated', '', 401);
    }

    public function register(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string|unique:users,name|alpha_dash',
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

        $user->assignRole('student');
        if (!empty($input['role'])) {
            $user->assignRole($input['role']);
        }
        $token = $user->createToken('token')->plainTextToken;
        $data = [
            'user' => User::where('id', $user->id)->with('roles')->first(),
            'token' => $token
        ];

        return $this->responseSuccess('Registration Successful', $data, 201);

        // return $this->success([
        //     'token' => $user->createToken('tokens')->plainTextToken
        // ]);
    }
    //use this method to login users
    public function login(Request $request)
    {
        $input = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|max:255'
        ]);

        if (!Auth::attempt($input)) {
            return $this->responseFailed('Email or password is incorrect', '', 401);
        }

        try {
            $user = User::where('email', $input['email'])->with('roles')->first();
            $token = $user->createToken('token')->plainTextToken;
            $data = [
                'user' => $user,
                'token' => $token
            ];
            Auth::logoutOtherDevices($input['password']);
            return $this->responseSuccess('Login Successful', $data, 200);
        } catch (\Exception $e) {
            return $this->responseFailed('Unexpected Error', '', 500);
        }
    }

    public function update($id, Request $request)
    {
        $user = User::where('id', $id)->first();
        if (!$user) return $this->responseFailed('Data not found', '', 404);

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string|alpha_dash',
            'email' => 'required|string|email',
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
        ]);

        $data = User::where('id', $id)->with(['roles'])->first();

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
