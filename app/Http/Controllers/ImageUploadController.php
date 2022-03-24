<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $url = $this->uploadFileImageKit('image');
            return response()->json($url);
        } catch (\Error $e) {
            return $this->responseFailed($e, 500);
        }
    }

    public function uploadCloudinary(Request $request)
    {
        try {
            $data = cloudinary()->upload($request->file('image')->getRealPath())->getSecurePath();
            return response()->json($data, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseFailed('Failed upload data');
        }
    }
}
