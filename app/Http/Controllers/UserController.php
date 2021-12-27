<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = User::with(['role' => function ($q) {
            $q->select('id', 'role_name');
        }])->get();
        return $this->responseSuccess('List all users', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|between:8,255',
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
            'role_id' => 2,
        ]);

        $data = User::where('id', $user->id)->with(['role' => function ($q) {
            $q->select('id', 'role_name');
        }])->first();

        return $this->responseSuccess('User created Successfully', $data, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) return $this->responseFailed('Data not found', '', 404);

        $data = User::where('id', $id)->with(['role' => function ($q) {
            $q->select('id', 'role_name');
        }])->first();
        return $this->responseSuccess('User detail', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
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

        return $this->responseSuccess('User updated successfully', $data, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) return $this->responseFailed('User not found', '', 404);
        if (is_array($id)) {
            User::destroy($id);
        } else {
            $user->delete();
        }


        return $this->responseSuccess('User deleted successfully');
    }
}
