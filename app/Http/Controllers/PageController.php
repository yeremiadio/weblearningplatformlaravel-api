<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Page::all();
        return $this->responseSuccess('List Pages', $data);
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
            'description' => 'required|string|max:255',
            'thumbnail' => 'nullable|image|mimes:png,jpg,jpeg'
        ]);
        if ($validator->fails()) return $this->responseFailed('Validation Error', $validator->errors(), 400);

        if ($request->hasFile('thumbnail')) {
            // $input['image'] = rand() . '.' . request()->image->getClientOriginalExtension();
            // $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();
            $uploadedFileUrl = cloudinary()->upload($request->file('thumbnail')->getRealPath())->getSecurePath();
            // dd($uploadedFileUrl);
            // request()->image->move(public_path('/images/material/'), $input['image']);
        }

        $data = Page::create([
            'name' => $input['name'],
            'slug' => Str::slug($input['name']),
            'thumbnail' => $uploadedFileUrl ?? null,
            'description' => $input['description'],
            'content' => json_encode('')
        ]);
        return $this->responseSuccess('Page created successfully', $data, 201);
    }

    // public function changeContent($slug, Request $request)
    // {
    //     $data = Page::where('slug', $slug)->update(['content' => $request->input('content')]);
    //     // $data = Page::updateOrCreate(['name' => $page->name, 'slug' => $slug, 'content' => $request->input('content')]);
    //     return response()->json($data->content);
    // }
    public function changeContent($slug, Request $request)
    {
        $page = Page::where('slug', $slug)->first();
        $page->update(['content' => $request->all()]);
        $data = Page::where('slug', $page->slug)->first();
        // $page->update($input);
        // // $page->updateOrCreate(['name' => $page->name, 'slug' -> $page->slug, 'content' => $request->input('content')]);
        // $data = Page::where('slug', $page->slug)->first();
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $data = Page::where('slug', $slug)->first();
        return $this->responseSuccess('Success', $data, 200);
    }

    public function loadContent($slug)
    {
        $page = Page::where('slug', $slug)->first();
        return response($page->content, 200)->header('Content-Type', 'application/json');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function edit(Page $page)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Page $page)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $page = Page::where('slug', $slug)->first();
        if (!$page) return $this->responseFailed('Data not found', '', 404);

        // if ($material->image) {
        //     File::delete('/images/materi/' . $material->image);
        // }

        $page->delete();

        return $this->responseSuccess('Data has been deleted');
    }
}
