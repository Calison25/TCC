<?php  

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(50000);
ini_set('memory_limit', '-1');

include "Classes/GenBankCrawler.php";
require 'vendor/autoload.php';


$genBankCrawler = new GenBankCrawler();




