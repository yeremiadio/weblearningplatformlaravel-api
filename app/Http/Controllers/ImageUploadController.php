<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            if ($request->hasFile('image')) {
                // $input['image'] = rand() . '.' . request()->image->getClientOriginalExtension();
                // $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();
                $uploadedFileUrl = cloudinary()->upload($request->file('image')->getRealPath())->getSecurePath();
                // dd($uploadedFileUrl);
                // request()->image->move(public_path('/images/material/'), $input['image']);
            }

            return response()->json($uploadedFileUrl);
        } catch (\Exception $e) {
            return $this->responseFailed('Upload Image Failed', 500);
        }
    }
}
