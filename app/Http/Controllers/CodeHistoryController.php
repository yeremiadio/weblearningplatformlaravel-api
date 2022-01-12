<?php

namespace App\Http\Controllers;

use App\Models\CodeHistories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CodeHistoryController extends Controller
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

    public function getCodeHistories()
    {
        $data = CodeHistories::where('user_id', auth()->user()->id)->get();

        return $this->responseSuccess('User Code Histories', $data, 200);
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
            'description' => 'required',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Validation Error, Check your fields again', $validator->errors(), 400);
        }

        $data = CodeHistories::create([
            'title' => $input['title'],
            'description' => $input['description'],
            'code' => $input['code'],
            'user_id' => auth()->user()->id
        ]);

        return $this->responseSuccess('Code history created', $data, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CodeHistories  $codeHistories
     * @return \Illuminate\Http\Response
     */
    public function show(CodeHistories $codeHistories)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CodeHistories  $codeHistories
     * @return \Illuminate\Http\Response
     */
    public function edit(CodeHistories $codeHistories)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CodeHistories  $codeHistories
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CodeHistories $codeHistories)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CodeHistories  $codeHistories
     * @return \Illuminate\Http\Response
     */
    public function destroy(CodeHistories $codeHistories)
    {
        //
    }
}
