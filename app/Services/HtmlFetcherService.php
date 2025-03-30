<?php

namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class HtmlFetcherService
{
    private Client $client;

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
}