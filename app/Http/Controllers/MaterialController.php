<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

// use Illuminate\Support\Facades\File;
// use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $materials = Material::all();
        if (!$materials) return $this->responseFailed('Data not found', '', 404);

        return $this->responseSuccess('List Materials', $materials, 200);
    }
    public function indexWithFilter()
    {
        $materials = Material::latest()->filter(request(['search', 'sort', 'orderby']))->paginate(request('limit' ?? 6));
        if (!$materials) return $this->responseFailed('Data not found', '', 404);

        return $this->responseSuccess('List Materials', $materials, 200);
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
            'description' => 'required|string|max:200',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Validation Error', $validator->errors(), 400);
        }

        if ($request->hasFile('thumbnail')) {
            $uploadedFileUrl = $this->uploadFileImageKit('thumbnail');
        }

        $data = Material::create([
            'title' => $input['title'],
            'description' => $input['description'],
            'content' => $input['content'],
            'slug' => Str::slug($input['title']),
            'thumbnail' => $uploadedFileUrl ?? null
        ]);

        return $this->responseSuccess('Data created', $data, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $material = Material::where('slug', $slug)->first();
        if (!$material) return $this->responseFailed('Data not found', '', 404);

        return $this->responseSuccess('Data fetched Successfully', $material);
    }

    public function storeScreenshotPage(Request $request)
    { }

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
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Error Validation', $validator->errors(), 400);
        }

        $oldThubmanil = $material->thumbnail;

        if ($request->hasFile('thumbnail')) {
            $input['thumbnail'] = cloudinary()->upload($request->file('thumbnail')->getRealPath())->getSecurePath();
        } else {
            $input['thumbnail'] = $oldThubmanil;
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

        $material->delete();

        return $this->responseSuccess('Data has been deleted');
    }
}
