<?php


use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class GenBankCrawler
{

    const DEFAULT_URL = 'https://www.ncbi.nlm.nih.gov/';

    const DOWNLOAD_URL = 'https://www.ncbi.nlm.nih.gov/sviewer/viewer.cgi?tool=portal&save=file&log$=seqview&db=nuccore&report=xml&query_key=1&filter=all&sort=ACCN';

    const PUBMED_URL = 'https://www.ncbi.nlm.nih.gov/pubmed/';

    public function __construct()
    {
        $cookie = new CookieJar();
        $ids = [];
        $filename = 'teste.pdf';
        $searchType = 'nuccore';
        $searchTerms = 'zika';

        // create a Client
        $client = new Client(['cookies' => $cookie, 'verify' => false]);
        $client->get(self::DEFAULT_URL);

        $client->get(self::DEFAULT_URL.$searchType.'/?term='.$searchTerms);


        $headers = [
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'pt-BR,pt;q=0.8,en-US;q=0.6,en;q=0.4',
            'Connection' => 'keep-alive',
            'Host' => 'www.ncbi.nlm.nih.gov',
            'Referer' => self::DEFAULT_URL.$searchType.'/?term='.$searchTerms,
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
        ];

        $form_params = [
            'tool' => 'portal',
            'save' => 'file',
            'log$' => 'seqview',
            'db' => $searchType,
            'report' => 'xml',
            'query_key' => '1',
            'filter' => 'all',
            'sort' => 'ACCN'
        ];


//        $response = $client->get(self::DOWNLOAD_URL, ['headers' => $headers, 'form_params' => $form_params]);
//        file_put_contents('pubmed.txt',$response->getBody()->getContents());
        $contentFile = file_get_contents('pubmed.txt');

      preg_match_all('/<PubMedId>(\d+)/', $contentFile, $matches);


      foreach ($matches[0] as $match){
          $pubMedIds[] = $match;
      }

      $pubMedIds = array_values(array_unique($pubMedIds));

      $pubMedIds = array_map(function ($id){
          return strip_tags($id);
      },$pubMedIds);

      $response = $client->get(self::PUBMED_URL.$pubMedIds[90]);

      preg_match('/PMC\d{7}/', $response->getBody()->getContents(), $match);
      $response = $client->get('https://www.ncbi.nlm.nih.gov/pmc/articles/'.$match[0
          ]);

      preg_match('/pdf\/(.*)\.pdf/',$response->getBody()->getContents(),$match);

      $pdfName = $match[1];
//        https://www.ncbi.nlm.nih.gov/pmc/articles/PMC5547780/pdf/16-2007.pdf
      $response = $client->get('https://www.ncbi.nlm.nih.gov/pmc/articles/PMC5547780/pdf/'.$pdfName.'.pdf');
      file_put_contents('pdfFinal.pdf',$response->getBody()->getContents());
      echo 'finalizou';
      die;

        //       $pdfContent = $this->getTextFromPdf($filename);
    }

    /**
     * @param $filename
     * @return string
     */
    private function getTextFromPdf($filename)
    {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($filename);

        return $pdf->getText();
    }
}


