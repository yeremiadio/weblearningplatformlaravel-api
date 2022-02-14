<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'slug', 'content', 'thumbnail'];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? false, function ($query, $search) {
            return $query->where('title', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
        });

        $query->when(request('limit') ?? false, function ($query, $limit) {
            return $query->limit($limit);
        });

        $query->when($filters['sort'] ?? false, function ($query, $sort) {
            return $query->reorder('price', $sort);
        });

        $query->when($filters['orderby'] ?? false, function ($query, $orderby) {
            return $query->orderBy($orderby ?? 'title', 'desc');
        });

        // $query->when($filters['min_price'] ?? false, function ($query, $min_price) {
        //     $minFilter = (int) $min_price;
        //     $query->where(function ($query) use ($minFilter) {
        //         $query->where('price', '>=', $minFilter);
        //     });
        // });

        // $query->when($filters['max_price'] ?? false, function ($query, $max_price) {
        //     $maxFilter = (int) $max_price;
        //     $query->where(function ($query) use ($maxFilter) {
        //         $query->where('price', '<=', $maxFilter);
        //     });
        // });

        // $query->when($filters['category'] ?? false, function ($query, $category) {
        //     return $query->whereHas('category', function ($query) use ($category) {
        //         $query->where('category_slug', $category);
        //     });
        // });
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
