<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PopularGame;

class PopularGameController extends Controller
{
    public function getAll()
    {
        $games = PopularGame::all();
        
        return response()->json([
            'games' => $games,
        ]);
    }

    public function show($id)
    {
        $game = PopularGame::find($id);
        
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
