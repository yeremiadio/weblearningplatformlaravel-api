<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Result;
use App\Models\ResultQuiz;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ResultController extends Controller
{
    public function quizStore($slug, Request $request)
    {
        $quiz = Quiz::where('slug', $slug)->first();
        if (!$quiz) return $this->responseFailed('Submit failed', '', 404);

        $isResult = Result::where([
            'quiz_id' => $quiz->id,
            'user_id' => auth()->user()->id
        ])->first();
        if (isset($isResult)) {
            return $this->responseFailed('Submit failed', 'User already submitted this quiz', 400);
        }

        $isAvailable = Carbon::parse($quiz->end_date)->toDateTimeString() > Carbon::now()->toDateTimeString() ? true : false;
        if (!$isAvailable) {
            return $this->responseFailed('Submit failed', 'Deadline has passed', 400);
        }

        $inputRaw = $request->only('data');
        $validator = Validator::make($inputRaw, [
            'data' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->responseFailed('Validation Error', $validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $input = json_decode($inputRaw['data']);

            $data = [
                'user_id' => auth()->user()->id,
                'quiz_id' => $input[0]->quiz_id
            ];
            $result = Result::create($data);
            $score = 0;
            $correct_answers = 0;

            foreach ($input as $item) {
                foreach ($item->options as $option) {
                    if (!property_exists($option, 'selected')) {
                        $optData = [
                            'result_id' => $result->id,
                            'question_id' => $option->question_id,
                            'option_id' => null,
                            'correct' => false,
                        ];
                        ResultQuiz::create($optData);
                        break;
                    }
                    if (isset($option->selected) && $option->selected == 1) {
                        $optData = [
                            'result_id' => $result->id,
                            'question_id' => $option->question_id,
                            'option_id' => $option->id,
                            'correct' => $option->correct == $option->selected ? true : false,
                        ];
                        $res = ResultQuiz::create($optData);
                        if ($res->correct) {
                            $score += 10;
                            $correct_answers += 1;
                        }
                        break;
                    }
                }
            }
            $result->update(['score' => $score]);

            DB::commit();

            $data_result = [
                'score' => $score,
                'correct_answers' => $correct_answers
            ];

            return $this->responseSuccess('Result has been stored', $data_result, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseFailed('Failed to save result');
        }
    }
}
