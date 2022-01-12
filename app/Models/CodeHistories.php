<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeHistories extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'description', 'code'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
