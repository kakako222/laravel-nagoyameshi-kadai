<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 100件のダミーユーザーデータを生成
        User::factory()->count(100)->create();
        $user = new User();
        $user->name = "課題レビュー";
        $user->kana = "カダイレビュー";
        $user->email = "kadai@example.com";
        $user->email_verified_at = Carbon::now();
        $user->password = Hash::make('password');
        $user->postal_code = "1234567";
        $user->address = "東京都";
        $user->phone_number = "85274597894";
        $user->save();
    }
}
