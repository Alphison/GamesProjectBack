<?php

namespace App\Http\Controllers\Api;

use App\Models\Game;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PopularGame;

class GameController extends Controller
{
    public function getAll(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);
        $genre_id = $request->input('genre_id');
        
        $query = Game::query();
    
        if ($genre_id) {
            $query->where('genre_id', $genre_id);
        }
        
        $games = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'games' => $games->items(),
            'next_page' => $games->hasMorePages() ? $games->currentPage() + 1 : null,
            'has_more' => $games->hasMorePages(),
            'total' => $games->total(),
        ]);
    }

    public function show($id)
    {
        $game = Game::find($id);
        
        if (!$game) {
            return response()->json([
                'message' => 'Game not found'
            ], 404);
        }
        
        return response()->json([
            'game' => $game
        ]);
    }


}
