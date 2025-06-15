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
        $url = 'https://igroarenda.ru/';
        $crawler = $this->htmlFetcher->fetchHtml($url);

        $genres = $crawler->filter('ul.navbar-nav > li > a')->each(function (Crawler $node) {
            return [
                'text' => $node->text(),
                'href' => $node->attr('href'),
            ];;
        });

        foreach ($genres as $genre) {
            Genre::firstOrCreate(['name' => trim($genre['text']), 'href' => $genre['href']]);
        }

        $this->info('Genres parsed successfully!');

        return $genres;
    }

    private function parseGames()
    {
        $page = 1;
        $max_page = false;

        $genres = Genre::all();

        foreach ($genres as $genre) {
            $max_page = true;

             do {
                $url = "{$genre['href']}?page={$page}";

                $games = $this->htmlFetcher->getGamesFromHtml($url);

                if (empty($games)) {
                    $this->info('No more games found.');
                    $max_page = false;
                    $page = 1;
                    break;
                }

                foreach ($games as $gameData) {
                    $game = Game::updateOrCreate(['title' => $gameData['title']], // Условие поиска
                    [                              // Данные для обновления/создания
                        'price' => $gameData['price'],
                        'preview' => $gameData['preview'],
                        'description' => $gameData['description'],
                        'date_exit' => $gameData['date_exit'],
                        'language' => $gameData['language'],
                        'category_id' => Category::inRandomOrder()->first()->id,
                    ]);

                    $game->genres()->syncWithoutDetaching([$genre['id']]);
                }

                $page++;
            } while ($max_page);
        }

       

        $this->info('Games parsed successfully!');
    }
}
