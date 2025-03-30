<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['title', 'description', 'preview', 'price', 'date_exit', 'language', 'category_id', 'genre_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }
}
