<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->type == 'quiz') {
            $data = Quiz::where('type', 'quiz')->with(['questions' => function ($q) {
                $q->select('id', 'quiz_id', 'question', 'file');
            }, 'questions.options' => function ($q) {
                $q->select('id', 'question_id', 'title', 'correct');
            }])->get();
        } else {
            $data = Quiz::where('type', 'essay')->with(['questions' => function ($q) {
                $q->select('id', 'quiz_id', 'question', 'file');
            }])->get();
        }

        return $this->responseSuccess('Quiz Data', $data);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'type' => 'required|string',
            'title' => 'required|string',
            'deadline' => 'required|date',
            'thumbnail' => 'nullable|mimes:jpeg,png,jpg',
            'questions' => 'required|array|between:1,10',
            'questions.*.question' => 'required|string',
            'questions.*.file' => 'nullable|mimes:jpeg,png,jpg,doc,docx,pdf',
            'questions.*.options' => 'sometimes|array|between:1,5',
            'questions.*.options.*.title' => 'required|string',
            'questions.*.options.*.correct' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Validasi error', $validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $input['thumbnail'] = null;
            if ($request->hasFile('thumbnail')) {
                $input['thumbnail'] = cloudinary()->upload($request->file('thumbnail')->getRealPath())->getSecurePath();
                // $input['banner'] = rand() . '.' . request()->banner->getClientOriginalExtension();

                // request()->banner->move(public_path('assets/images/quiz/'), $input['banner']);
            }

            $quiz = Quiz::create([
                'title' => $input['title'],
                'slug' =>  Str::slug($input['title']) . '-' . uniqid(),
                'type' => $input['type'],
                'deadline' => $input['deadline'],
                'thumbnail' => $input['thumbnail']
            ]);

            foreach ($input['questions'] as $key => $questionValue) {
                $questionValue['file'] = null;
                if ($request->hasFile('questions.' . $key . '.file') && $quiz->type == 'quiz') {
                    $questionValue['file'] = cloudinary()->upload($request->file('questions.' . $key . '.file')->getRealPath())->getSecurePath();
                    // $questionValue['file'] = rand().'.'.$request->questions[$key]['file']->getClientOriginalExtension();

                    // $request->questions[$key]['file']->move(public_path('assets/files/quiz/'), $questionValue['file']);
                }

                if ($request->hasFile('questions.' . $key . '.file') && $quiz->type == 'essay') {
                    $questionValue['file'] = cloudinary()->uploadFile($request->file('questions.' . $key . '.file')->getRealPath())->getSecurePath();
                    // $questionValue['file'] = rand().'.'.$request->questions[$key]['file']->getClientOriginalExtension();

                    // $request->questions[$key]['file']->move(public_path('assets/files/quiz/'), $questionValue['file']);
                }

                $question = Question::create([
                    'quiz_id' => $quiz->id,
                    'question' => $questionValue['question'],
                    'file' => $questionValue['file']
                ]);

                if ($quiz->type == 'quiz') {
                    foreach ($questionValue['options'] as $optionValue) {
                        Option::create([
                            'question_id' => $question->id,
                            'title' => $optionValue['title'],
                            'correct' => +$optionValue['correct']
                        ]);
                    }
                }
            }

            DB::commit();

            if ($quiz->type == 'quiz') {
                $data = Quiz::where('slug', $quiz->slug)->with(['questions' => function ($q) {
                    $q->select('id', 'quiz_id', 'question', 'file');
                }, 'questions.options' => function ($q) {
                    $q->select('id', 'question_id', 'title', 'correct');
                }])->first();
            } else {
                $data = Quiz::where('slug', $quiz->slug)->with(['questions' => function ($q) {
                    $q->select('id', 'quiz_id', 'question', 'file');
                }])->first();
            }

            return $this->responseSuccess('Data created succesfully', $data, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseFailed('Failed create data');
        }
    }

    public function storeCodeQuiz(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'type' => 'required|string',
            'title' => 'required|string',
            'deadline' => 'required|date',
            'thumbnail' => 'nullable|mimes:png,jpg,jpeg|image|max:2048',
            'questions' => 'required|array|between:1,10',
            'questions.*.question' => 'required|string',
            'questions.*.file' => 'nullable|mimes:jpeg,png,jpg,doc,docx,pdf',
            'questions.*.options' => 'sometimes|array|between:1,5',
            'questions.*.options.*.title' => 'required|string',
            'questions.*.options.*.correct' => 'required',
        ]);
        if ($validator->fails()){
            $this->responseFailed('Validator fail','',400);
        }

        try {
            // $quiz = Quiz::create([

            // ])
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function getCodeQuiz(Request $request)
    {

    }

    public function show($slug)
    {
        $quiz = Quiz::where('slug', $slug)->first();
        if (!$quiz) return $this->responseFailed('Data not found', '', 404);

        if ($quiz->type == 'quiz') {
            $isResult = Result::where([
                'quiz_id' => $quiz->id,
                'user_id' => auth()->user()->id
            ])->first();
            if (isset($isResult)) {
                return $this->responseFailed('Failed', 'User already submitted the quiz', 400);
            }

            $data = Quiz::where('slug', $quiz->slug)->with(['questions' => function ($q) {
                $q->select('id', 'quiz_id', 'question', 'file');
            }, 'questions.options' => function ($q) {
                $q->select('id', 'question_id', 'title', 'correct');
            }])->first();
        } else {
            $data = Quiz::where('slug', $quiz->slug)->with(['questions' => function ($q) {
                $q->select('id', 'quiz_id', 'question', 'file');
            }])->first();
        }

        return $this->responseSuccess('Detail data', $data);
    }

    public function update(Request $request, $slug)
    {
        $quiz = Quiz::where('slug', $slug)->with('questions')->first();
        if (!$quiz) return $this->responseFailed('Data tidak ditemukan', '', 404);
        if ($quiz->type == 'quiz') {
            $quiz = Quiz::where('slug', $slug)->with('questions.options')->first();
        }

        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required|string',
            'deadline' => 'required|date',
            'thumbnail' => 'nullable|mimes:jpeg,png,jpg',
            'questions' => 'required|array|between:1,10',
            'questions.*.id' => 'required|numeric',
            'questions.*.question' => 'required|string',
            'questions.*.file' => 'nullable',
            'questions.*.options' => 'sometimes|array|between:1,5',
            'questions.*.options.*.id' => 'required|numeric',
            'questions.*.options.*.title' => 'required|string',
            'questions.*.options.*.correct' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Validasi error', $validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $oldThumbnail = $quiz->thumbnail;
            if ($request->hasFile('thumbnail')) {
                // $input['thumbnail'] = $oldThumbnail;
                $input['thumbnail'] = cloudinary()->upload($request->file('thumbnail')->getRealPath())->getSecurePath();
                // File::delete('assets/images/quiz/' . $oldBanner);
                // $input['banner'] = rand() . '.' . request()->banner->getClientOriginalExtension();

                // request()->banner->move(public_path('assets/images/quiz/'), $input['banner']);
            } else {
                $input['thumbnail'] = $oldThumbnail;
            }

            $quiz->update([
                'title' => $input['title'],
                'deadline' => $input['deadline'],
                'thumbnail' => $input['thumbnail']
            ]);

            foreach ($input['questions'] as $key => $questionValue) {
                if ($questionValue['id'] == -1) {
                    $questionValue['file'] = null;
                    if ($request->hasFile('questions.' . $key . '.file')) {
                        $questionValue['file'] = cloudinary()->upload($request->file('questions.' . $key . '.file')->getRealPath())->getSecurePath();
                    }

                    $question = Question::create([
                        'quiz_id' => $quiz->id,
                        'question' => $questionValue['question'],
                        'file' => $questionValue['file']
                    ]);

                    if ($quiz->type == 'quiz') {
                        foreach ($questionValue['options'] as $optionValue) {
                            if ($optionValue['id'] == -1) {
                                Option::create([
                                    'question_id' => $question->id,
                                    'title' => $optionValue['title'],
                                    'correct' => +$optionValue['correct']
                                ]);
                            }
                        }
                    }
                } else {
                    $oldFile = $quiz->questions[$key]->file;
                    if ($request->hasFile('questions.' . $key . '.file')) {
                        $questionValue['file'] = cloudinary()->upload($request->file('questions.' . $key . '.file')->getRealPath())->getSecurePath();
                        // File::delete('assets/files/quiz/' . $oldFile);
                        // $questionValue['file'] = rand() . '.' . $request->questions[$key]['file']->getClientOriginalExtension();

                        // $request->questions[$key]['file']->move(public_path('assets/files/quiz/'), $questionValue['file']);
                    } else {
                        $questionValue['file'] = $oldFile;
                    }

                    Question::where('id', $questionValue['id'])
                        ->update([
                            'question' => $questionValue['question'],
                            'file' => $questionValue['file']
                        ]);

                    if ($quiz->type == 'quiz') {
                        foreach ($questionValue['options'] as $key2 => $optionValue) {
                            if ($optionValue['id'] == -1) {
                                Option::create([
                                    'question_id' => $questionValue['id'],
                                    'title' => $optionValue['title'],
                                    'correct' => +$optionValue['correct']
                                ]);
                            } else {
                                Option::where('id', $optionValue['id'])
                                    ->update([
                                        'title' => $optionValue['title'],
                                        'correct' => +$optionValue['correct']
                                    ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            if ($quiz->type == 'quiz') {
                $data = Quiz::where('slug', $quiz->slug)->with(['questions' => function ($q) {
                    $q->select('id', 'quiz_id', 'question', 'file');
                }, 'questions.options' => function ($q) {
                    $q->select('id', 'question_id', 'title', 'correct');
                }])->first();
            } else {
                $data = Quiz::where('slug', $quiz->slug)->with(['questions' => function ($q) {
                    $q->select('id', 'quiz_id', 'question', 'file');
                }])->first();
            }

            return $this->responseSuccess('Data berhasil diubah', $data, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseFailed('Data gagal diubah');
        }
    }

    public function destroy($slug)
    {
        $quiz = Quiz::where('slug', $slug)->with('questions')->first();
        if (!$quiz) return $this->responseFailed('Data not found', '', 404);

        // if ($quiz->banner) {
        //     File::delete('assets/images/quiz/' . $quiz->banner);
        // }

        // foreach ($quiz->questions as $questionValue) {
        //     if ($questionValue->file) {
        //         File::delete('assets/files/quiz/' . $questionValue->file);
        //     }
        // }

        $quiz->delete();

        return $this->responseSuccess('Data deleted successfully');
    }
}
