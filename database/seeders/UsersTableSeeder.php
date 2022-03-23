<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Main User
        $user_one = User::create([
            'name' => 'Admin',
            'email' => 'interweblearningplatform@gmail.com',
            'password' => bcrypt('WebLearningPlatform123`'),
            'email_verified_at' => Carbon::now(),
            'remember_token' => Str::Random(50),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user_one->assignRole('admin');
    }
}
