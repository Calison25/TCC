<?php 


use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class GenBankCrawler
{
	
	const DEFAULT_URL = 'https://www.ncbi.nlm.nih.gov/';

	public function __construct()
	{
		$cookie = new CookieJar();
		$ids = [];

        // instancia pro client
        $client = new Client(['cookies' => $cookie, 'verify' => false]);

        $client->get(self::DEFAULT_URL.'genbank/');

        $response = $client->get(self::DEFAULT_URL.'genome/?term=dengue');

        $content = strip_tags($response->getBody()->getContents());

        preg_match_all('/ID: \d+/', $content, $matches);

        $ids = array_map(function($match){
        	return trim(str_replace('ID:', '', $match));
        }, $matches[0]);
	}
}


