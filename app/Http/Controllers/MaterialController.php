<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Material::all();
        return $this->responseSuccess('Materials Data', $data);
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
            'title' => 'required|string',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Validation Error', $validator->errors(), 400);
        }

        if ($request->hasFile('image')) {
            $input['image'] = rand() . '.' . request()->image->getClientOriginalExtension();

            request()->image->move(public_path('/images/material/'), $input['image']);
        }

        $data = Material::create([
            // 'user_id' => auth()->user()->id,
            'title' => $input['title'],
            'description' => $input['description'],
            'image' => $input['image'] ?? null
        ]);

        return $this->responseSuccess('Data created', $data, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function show(Material $material)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function edit(Material $material)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $material = Material::where('id', $id)->first();
        if (!$material) return $this->responseFailed('Data tidak ditemukan', '', 404);

        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required|string',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        $oldImage = $material->image;

        if ($request->hasFile('image')) {
            File::delete('/images/material/' . $oldImage);
            $input['image'] = rand() . '.' . request()->image->getClientOriginalExtension();

            request()->image->move(public_path('/images/material/'), $input['image']);
        } else {
            $input['image'] = $oldImage;
        }

        $material->update($input);

        $data = Material::find($id);

        return $this->responseSuccess('Material has been updated', $data, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $material = Material::where('id', $id)->first();
        if (!$material) return $this->responseFailed('Data not found', '', 404);

        if ($material->image) {
            File::delete('/images/materi/' . $material->image);
        }

        $material->delete();

        return $this->responseSuccess('Data has been deleted');
    }
}
