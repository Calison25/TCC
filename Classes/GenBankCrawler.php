<?php 


use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class GenBankCrawler
{

	public function __construct()
	{
		$cookie = new CookieJar();

        // instancia pro client
        $client = new Client(['cookies' => $cookie, 'verify' => false]);

        $response = $client->get('https://www.google.com.br/?gws_rd=cr&ei=UPiYWc6JIIqxwASN-KygDA');
	}
}


