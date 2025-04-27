<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Genre;
use App\Models\PopularGame;
use App\Services\HtmlFetcherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParsePopularGames extends Command
{
    protected $signature = 'app:parse-popular-games';

    protected $description = 'Command description';

    private HtmlFetcherService $htmlFetcher;

    public function __construct(HtmlFetcherService $htmlFetcher)
    {
        parent::__construct();
        $this->htmlFetcher = $htmlFetcher;
    }

    public function handle()
    {
        DB::table('popular_games')->delete();

        $url = "https://404game.ru";

        $games = $this->htmlFetcher->getGamesFromHtml($url);

        if (empty($games)) {
            $this->info('No more games found.');
            return false;
        }

        for ($i=0; $i <= 4; $i++) { 
            PopularGame::create([
                'title' => $games[$i]['title'],
                'price' => $games[$i]['price'],
                'preview' => $games[$i]['preview'],
                'description' => $games[$i]['description'],
                'date_exit' => $games[$i]['date_exit'],
                'language' => $games[$i]['language'],
                'category_id' => Category::inRandomOrder()->first()->id,
                'genre_id' => Genre::inRandomOrder()->first()->id,
            ]);
        }

        Log::info('Парсинг популярных игр');
        return 0;
    }
}
