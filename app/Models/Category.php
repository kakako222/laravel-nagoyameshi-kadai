<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // 更新可能なカラム
    protected $fillable = [
        'name',
    ];
    /**
     * このカテゴリが関連するレストランを取得する。
     */
    public function restaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'category_restaurant');
    }
}
