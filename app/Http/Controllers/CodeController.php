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
        //
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
            'code' => 'string|required',
        ]);
        if ($validator->fails()) {
            return $this->responseFailed('Validator error', '', 400);
        }

        $titleCode = $input['title'] ?? 'untitled' . '-' . uniqid();

        try {
            $code = Code::create([
                'title' => $titleCode,
                'slug' => Str::slug($titleCode),
                'code' => $input['code'],
                'description' => $input['description'],
                'user_id' => auth()->id()
            ]);
            $data = Code::where('id', $code->id)->with('user')->first();
            return $this->responseSuccess('Success create code', $data, 201);
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
    public function show($id)
    {
        //
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
