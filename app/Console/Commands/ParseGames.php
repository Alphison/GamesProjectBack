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

    private $percent = 0.2;

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
            $crawler = $this->htmlFetcher->fetchHtml($url);

            $games = $crawler->filter('div.short')->each(function (Crawler $node) {
                $urlOnegame = $node->filter('a.s-img')->attr('href');
                $crawlerGame = $this->htmlFetcher->fetchHtml($urlOnegame); // страница одной игры

                $priceText = $node->filter('div.s-price')->text();
                $releaseDate = null;
                $language = null;

                if (preg_match('/(\d+)\s*руб/', $priceText, $matches)) {
                    $price = $matches[1];
                } else {
                    $price = null;
                }

                if ($crawlerGame->filter('ul.finfo li:contains("Дата выхода:")')->count() > 0) {
                    $dateText = $crawlerGame->filter('ul.finfo li:contains("Дата выхода:")')->text();
                    $releaseDate = trim(str_replace('Дата выхода:', '', $dateText));
                }

                if ($crawlerGame->filter('ul.finfo li:contains("Локализация:")')->count() > 0) {
                    $languageText = $crawlerGame->filter('ul.finfo li:contains("Локализация:")')->text();
                    $language = trim(str_replace('Локализация:', '', $languageText));
                }
                
                return [
                    'title' => $node->filter('a.s-title')->text(),
                    'price' => $price + ((int)$price * $this->percent),
                    'preview' => $node->filter('img')->attr('src'),
                    'description' => $crawlerGame->filter('div.full-text')->text(),
                    'date_exit' => $releaseDate,
                    'language' => $language,
                ];
            });

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
