<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    // 使用するモデルを指定
    protected $model = Company::class;

    /**
     * 定義するデータのテンプレート
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'テスト',  // 会社名
            'postal_code' => '0000000',  // 郵便番号
            'address' => 'テスト',  // 住所
            'representative' => 'テスト',  // 代表者
            'establishment_date' => 'テスト',  // 設立日
            'capital' => 'テスト',  // 資本金
            'business' => 'テスト',  // 事業内容
            'number_of_employees' => 'テスト',  // 従業員数
        ];
    }
}
