<?php

namespace Facebook\WebDriver;

use App\TermoScrap;
use App\Words;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;

require_once('config.php');
require_once('vendor/autoload.php');


$wordList = new Words();
$word = $wordList->randomWord();
// $word = $wordList->bestVowelWord();


$termoScrap = new TermoScrap();
$termoScrap->start();

for ($i = 0; $i < 6; $i++) {


  if ($word == null) {
    break;
  }

  echo "\033[35m[+] Testando palavra: $word \033[0m\n";
  $result = $termoScrap->find($word);

  if ($result["success"] == true) {
    break;
  }

  $word = $wordList->updateWordlist($result["letters"], $result["attempt"]);

  sleep(1);
}

echo "\n\n\033[32m[RESULT]\033[0m\n\n";

if ($result["success"] == true) {
  echo "[+] Correct word: \033[36m" . $result['word'] . "\033[0m" . PHP_EOL . PHP_EOL;
} else {
  echo "[-] Word not founded " . PHP_EOL . PHP_EOL;
}

sleep(60);
$termoScrap->finish();

