<?php

namespace App\Http\Controllers;

use App\Models\CodeHistories;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

class AuthenticatedUserController extends Controller
{
    public function dashboard(Request $request)
    {

        //Count All Data
        $user_count = count(User::all());
        $role_count = count(Role::all());
        $material_count = count(Material::all());
        $quiz_count = count(Quiz::all());
        $code_histories_count = count(CodeHistories::all());

        $dataCount = [
            'user' => +$user_count,
            'material' => +$material_count,
            'role' => +$role_count,
            'code_histories' => +$code_histories_count,
            'quiz' => +$quiz_count,

        ];

        //All Roles Count
        $all_roles_in_database = Role::all()->pluck('name');
        $all_roles_count = [];
        foreach ($all_roles_in_database as $value) {
            // $role = ;
            array_push($all_roles_count, count(User::role($value)->get()));
        }

        //Material Count by month
        $data_materials = Material::select('id', 'created_at')->get()->groupBy(function ($data) {
            return Carbon::parse($data->created_at)->format('M');
        });

        $data_materials_months = [];
        $data_materials_month_count = [];
        foreach ($data_materials as $month => $values) {
            $data_materials_months[] = $month;
            $data_materials_month_count[] = count($values);
        }

        $data_materials_count = [
            'data' => $data_materials,
            'months' => $data_materials_months,
            'month_count' => $data_materials_month_count
        ];

        $data = [
            'all_data_count' => $dataCount,
            'all_roles_count' => [
                'names' => $all_roles_in_database,
                'count' => $all_roles_count
            ],
            'data_materials_count_by_month' => $data_materials_count,
        ];
        return $this->responseSuccess('Data Fetched Successfully', $data, 200);
    }
}
