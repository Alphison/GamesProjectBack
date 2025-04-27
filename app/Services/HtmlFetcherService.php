<?php

namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class HtmlFetcherService
{
    private Client $client;

    private $percent = 0.2;


    public function __construct()
    {
        $this->client = new Client();
    }

    public function fetchHtml(string $url): Crawler
    {
        $response = $this->client->get($url);
        $html = $response->getBody()->getContents();

        return new Crawler($html);
    }

    public function getGamesFromHtml(String $url) {
        $crawler = $this->fetchHtml($url);

        $games = $crawler->filter('div.short')->each(function (Crawler $node) {
            $urlOnegame = $node->filter('a.s-img')->attr('href');
            $crawlerGame = $this->fetchHtml($urlOnegame); // страница одной игры

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

        return $games;
    }
}