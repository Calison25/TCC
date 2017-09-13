<?php


use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class GenBankCrawler
{

    const DEFAULT_URL = 'https://www.ncbi.nlm.nih.gov/';

    const DOWNLOAD_URL = 'https://www.ncbi.nlm.nih.gov/sviewer/viewer.cgi?tool=portal&save=file&log$=seqview&db=nuccore&report=xml&query_key=1&filter=all&sort=ACCN';

    const PUBMED_URL = 'https://www.ncbi.nlm.nih.gov/pubmed/';

    const PUBMED_ARTICLES = 'https://www.ncbi.nlm.nih.gov/pmc/articles/';

    const XML_NAME = 'pubmed.txt';

    public function __construct()
    {
        $cookie = new CookieJar();
        $ids = [];
        $filename = 'teste.pdf';
        $searchType = 'nuccore';
        $searchTerms = 'zika';
        $contentFile = '';

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

      $pubMedIds = $this->getAllPubMedId(self::XML_NAME, 4096);

      foreach ($pubMedIds as $id){
          try{
              $response = $client->get(self::PUBMED_URL.$id);

              $content = $response->getBody()->getContents();

              preg_match('/PMC\d{7}/', $content, $match);
              if(!empty($match)){
                  $response = $client->get(self::PUBMED_ARTICLES.$match[0]);
                  preg_match('/pdf\/(.*)\.pdf/',$response->getBody()->getContents(),$match);
                  if(!empty($match)){
                      $pdfName = $match[1];

                      $response = $client->get(self::PUBMED_ARTICLES.$id.'/pdf/'.$pdfName.'.pdf');
                      file_put_contents('pdfs/'.$pdfName.'.pdf',$response->getBody()->getContents());
                  }
              }
          }catch (\Exception $exception){
              echo "deu erro mas segue\n";
          }
      }


//        https://www.ncbi.nlm.nih.gov/pmc/articles/PMC5547780/pdf/16-2007.pdf
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

    private function getAllPubMedId($fileName, $chunkSize)
    {

        $handle = fopen($fileName, "r");
        $pubMedIds = [];
        while (!feof($handle)){
            $content = fread($handle,$chunkSize);
            if(strpos($content,'PubMedId')){
                preg_match_all('/<PubMedId>(\d+)/', $content, $matches);
                foreach ($matches[0] as $match){
                    array_push($pubMedIds, $match);
                }
            }
        }
        fclose($handle);

        $pubMedIds = array_values(array_unique($pubMedIds));

        $pubMedIds = array_map(function ($id){
            return strip_tags($id);
        },$pubMedIds);

        return $pubMedIds;
    }
}


