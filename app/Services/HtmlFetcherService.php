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

    public function getGamesFromHtml(string $url): array
    {
        $crawler = $this->fetchHtml($url);

        $games = $crawler->filter('div.product-thumb')->each(function (Crawler $node) {
            // Инициализация переменных с null по умолчанию
            $urlOnegame = null;
            $price = null;
            $htmlContentDes = null;
            $releaseDate = null;
            $language = null;
            $preview = null;
            $title = null;

            // Получаем URL игры (с проверкой существования элемента)
            $titleLink = $node->filter('div.caption h4 a');
            if ($titleLink->count() > 0) {
                $urlOnegame = $titleLink->attr('href');
                $title = $titleLink->text();
            }

            // Если URL игры не найден, пропускаем игру
            if (empty($urlOnegame)) {
                return null;
            }

            try {
                $crawlerGame = $this->fetchHtml($urlOnegame);

                // Получаем цену (с проверкой существования элемента)
                $priceElement = $crawlerGame->filter('span.autocalc-product-special');
                $priceElement2 = $crawlerGame->filter('span.autocalc-product-price');
                
                if ($priceElement->count() > 0) {
                    $priceText = $priceElement->text();
                    if (preg_match('/(\d+)\s*руб/', $priceText, $matches)) {
                        $price = $matches[1] + ((int)$matches[1] * $this->percent);
                    }
                }else if($priceElement2->count() > 0){
                    $priceText = $priceElement2->text();
                    if (preg_match('/(\d+)\s*руб/', $priceText, $matches)) {
                        $price = $matches[1] + ((int)$matches[1] * $this->percent);
                    }
                }

                // Получаем описание
                $tab1 = $crawlerGame->filter('div.tab-pane')->eq(0);
                if ($tab1->count() > 0) {
                    $paragraphs = $tab1->filter('p');
                    $pCount = $paragraphs->count();
                    
                    // Удаляем последние 2 параграфа
                    for ($i = max(0, $pCount - 2); $i < $pCount; $i++) {
                        $paragraphs->eq($i)->getNode(0)->parentNode->removeChild($paragraphs->eq($i)->getNode(0));
                    }
                    
                    $htmlContentDes = $tab1->html();
                }

                // Получаем дату выхода и язык
                $tab2 = $crawlerGame->filter('div.tab-pane')->eq(1);
                if ($tab2->count() > 0) {
                    $tr = $tab2->filter('table tbody tr')->eq(0);
                    if ($tr->count() > 0) {
                        $releaseDate = $tr->filter('td')->eq(1)->text();
                    }

                    $tr4 = $tab2->filter('table tbody tr')->eq(4);
                    if ($tr4->count() > 0) {
                        $language = $tr4->filter('td')->eq(1)->text();
                    }
                }

                // Получаем превью
                $previewElement = $crawlerGame->filter('a.thumbnail img');
                if ($previewElement->count() > 0) {
                    $preview = $previewElement->attr('src');
                }

                return [
                    'title' => $title,
                    'price' => $price,
                    'preview' => $preview,
                    'description' => $htmlContentDes,
                    'date_exit' => $releaseDate,
                    'language' => $language,
                ];
            } catch (\Exception $e) {
                // Логируем ошибку и пропускаем эту игру
                error_log("Error processing game {$urlOnegame}: " . $e->getMessage());
                return null;
            }
        });

        // Удаляем null-значения (пропущенные игры)
        return array_filter($games);
    }
}