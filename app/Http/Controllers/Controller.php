<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use ImageKit\ImageKit;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function responseSuccess($msg, $arr = null, $status = 200)
    {
        $res = [
            'status' => true,
            'message' => ($msg == "") ? "Success" : $msg,
        ];

        if ($arr) {
            $res['data'] = $arr;
        }

        return response()->json($res, $status);
    }

    protected function responseFailed($msg = null, $arr = null, $status = 500)
    {
        $res = [
            'status' => false,
            'message' => (!$msg) ? "Error" : $msg,
        ];

        if ($arr) {
            $res['data'] = $arr;
        }

        return response()->json($res, $status);
    }

    protected function uploadFileImageKit($name = 'file')
    {
        if ($this->request->hasFile($name)) {
            $file = $this->request->file($name);
            $client = new ImageKit(
                env('IMAGEKIT_PUBLIC_KEY'),
                env('IMAGEKIT_PRIVATE_KEY'),
                env('IMAGEKIT_CLIENTID')
            );

            $response = $client->upload(array(
                'file' => base64_encode(file_get_contents($file)),
                'fileName' => $file->getClientOriginalName()
            ));

            $url = $response->success->url;
            return $url;
        } else {
            $client = new ImageKit(
                env('IMAGEKIT_PUBLIC_KEY'),
                env('IMAGEKIT_PRIVATE_KEY'),
                env('IMAGEKIT_CLIENTID')
            );

            $response = $client->upload(array(
                'file' => $name,
                'fileName' => "my_file_name.jpg",
            ));

            $url = $response->success->url;
            return $url;
        }
    }
}
