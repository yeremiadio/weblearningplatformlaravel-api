<?php

namespace App\Http\Controllers;

use App\Models\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Code::all();
        return $this->responseSuccess('Codes Data', $data, 200);
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

    public function getUserCodes()
    {
        $data = Code::where('user_id', auth()->user()->id)->get();
        return $this->responseSuccess('Fetched data successfully', $data, 200);
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
            'title' => 'string|required',
            'type' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->responseFailed('Validator error', $validator->errors(), 400);
        }
        $titleCode = $input['title'];
        try {
            $code = Code::create([
                'title' => $titleCode,
                'slug' => Str::slug($titleCode) . '-' . uniqid(),
                'code' => $input['code'],
                'type' => $input['type'],
                'description' => $input['description'] ?? null,
                'user_id' => auth()->id()
            ]);
            $data = Code::where('id', $code->id)->with('user')->first();
            return $this->responseSuccess('Code saved successfully', $data, 201);
        } catch (\Exception $e) {
            return $this->responseFailed($e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        try {
            $data = Code::where([
                'slug' => $slug,
            ])->first();
            return $this->responseSuccess('Fetched Code Successfully', $data, 200);
        } catch (\ErrorException $e) {
            return $this->responseFailed('Response error', $e, 500);
        }
    }

    public function storeWebPageBuilder(Request $request, $slug)
    {
        $code = Code::where('slug', $slug)->first();
        $code->update([
            'type' => 'webpage-builder',
            'code' => json_encode($request->all())
        ]);
        //
        $data = Code::where('slug', $code->slug)->first();
        return response()->json($data);
    }

    public function loadWebPageBuilder($slug)
    {
        $data = Code::where('slug', $slug)->first();
        return response($data->code, 200)->header('Content-Type', 'application/json');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $code = Code::where([
            'slug' => $slug,
            'user_id' => auth()->user()->id
        ])->with('user')->first();
        if (!$code) return $this->responseFailed('Data not found', '', 404);

        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'string|required',
            'code' => 'required',
            'type' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->responseFailed('Validator error', $validator->errors(), 400);
        }
        $titleCode = $input['title'];
        try {
            $code->update([
                'title' => $titleCode,
                'slug' => Str::slug($titleCode) . '-' . uniqid(),
                'code' => $input['code'],
                'type' => $input['type'],
                'description' => $input['description'] ?? null,
                'user_id' => auth()->id()
            ]);
            $data = Code::where([
                'slug' => $code->slug,
                'user_id' => auth()->user()->id
            ])->with('user')->first();
            return $this->responseSuccess('Code updated successfully', $data, 200);
        } catch (\Exception $e) {
            return $this->responseFailed($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $code = Code::where('id', $id)->first();
        if (!$code) return $this->responseFailed('Data not found', '', 404);

        $code->delete();

        return $this->responseSuccess('Data has been deleted');
    }
}
