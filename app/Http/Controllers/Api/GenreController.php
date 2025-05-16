<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function getAll()
    {
        
        $genres = Genre::all();
        
        return response()->json([
            'games' => $genres,
        ]);
    }
}
