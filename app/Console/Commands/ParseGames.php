<?php

namespace App\Console\Commands;

use App\Services\HtmlFetcherService;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Game;
use App\Models\Category;
use App\Models\Genre;
use BcMath\Number;

class ParseGames extends Command
{
    protected $signature = 'parse:games';
    protected $description = 'Parse games and genres from HTML page';

    private HtmlFetcherService $htmlFetcher;

    public function __construct(HtmlFetcherService $htmlFetcher)
    {
        parent::__construct();
        $this->htmlFetcher = $htmlFetcher;
    }

    public function handle()
    {
        $categories = [
            Category::firstOrCreate(['name' => 'Category 1']),
            Category::firstOrCreate(['name' => 'Category 2']),
        ];

        $this->parseGenres();

        $this->parseGames();

        $this->info('Parsing completed successfully!');
    }

    private function parseGenres()
    {
        $url = 'https://404game.ru/';
        $crawler = $this->htmlFetcher->fetchHtml($url);

        $genres = $crawler->filter('ul.side-bc > li > a')->each(function (Crawler $node) {
            return $node->text();
        });

        foreach ($genres as $genreName) {
            Genre::firstOrCreate(['name' => trim($genreName)]);
        }

        $this->info('Genres parsed successfully!');
    }

    private function parseGames()
    {
        $page = 1;
        $max_page = 30;

        do {
            $url = "https://404game.ru/genre/page/{$page}";

            $games = $this->htmlFetcher->getGamesFromHtml($url);

            if (empty($games)) {
                $this->info('No more games found.');
                break;
            }

            foreach ($games as $gameData) {
                Game::create([
                    'title' => $gameData['title'],
                    'price' => $gameData['price'],
                    'preview' => $gameData['preview'],
                    'description' => $gameData['description'],
                    'date_exit' => $gameData['date_exit'],
                    'language' => $gameData['language'],
                    'category_id' => Category::inRandomOrder()->first()->id,
                    'genre_id' => Genre::inRandomOrder()->first()->id,
                ]);
            }

            $page++;
        } while ($page <= $max_page);

        $this->info('Games parsed successfully!');
    }
}
