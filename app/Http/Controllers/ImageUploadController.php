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
}
