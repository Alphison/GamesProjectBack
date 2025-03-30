<?php

namespace App\Http\Controllers\Api;

use App\Models\Game;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GameController extends Controller
{
    public function getAll(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);
        
        $games = Game::query()->paginate($perPage, ['*'], 'page', $page);
        
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
