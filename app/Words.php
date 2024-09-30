<?php

namespace App;

class Words
{

  private array $wordList = [];

  public function __construct()
  {
    $this->wordList = file(WORDLIST, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($this->wordList as $key => $word) {
      if (strlen($word) > 5) {
        unset($this->wordList[$key]);
      }

      $this->wordList[$key] = static::prepareWord($word);
    }
  }

  public function randomWord()
  {
    ksort($this->wordList, SORT_NUMERIC);
    $this->wordList = array_values($this->wordList);
    return $this->wordList[rand(0, count($this->wordList) - 1)];
  }

  public function checkWordList(): bool
  {
    if (count($this->wordList) < 1) {
      return false;
    }
    return true;
  }

  function palavraComMaisVogaisUnicas(array $palavras): string
  {
    $vogais = ['A', 'E', 'I', 'O', 'U'];
    $palavraComMaisVogais = '';
    $maxVogais = 0;

    foreach ($palavras as $palavra) {
      $vogaisNaPalavra = [];
      foreach (str_split($palavra) as $letra) {
        if (in_array($letra, $vogais) && !in_array($letra, $vogaisNaPalavra)) {
          $vogaisNaPalavra[] = $letra;
        }
      }

      if (count($vogaisNaPalavra) > $maxVogais) {
        $maxVogais = count($vogaisNaPalavra);
        $palavraComMaisVogais = $palavra;
      }
    }

    return $palavraComMaisVogais;
  }

  public function bestVowelWord()
  {
    ksort($this->wordList, SORT_NUMERIC);
    $this->wordList = array_values($this->wordList);
    return $this->palavraComMaisVogaisUnicas($this->wordList);
    //$this->wordList[rand(0, count($this->wordList) - 1)];
  }

  public function removeWordNotContainsLetter($letter)
  {

    foreach ($this->wordList as $keyWord => $word) {
      if (!str_contains($word, $letter)) {
        unset($this->wordList[$keyWord]);
        continue;
      }
    }
  }



  public function removeWordNotContainsLetterPosition($letter, $letterPosition)
  {

    foreach ($this->wordList as $keyWord => $word) {
      if (!str_contains($word, $letter)) {
        unset($this->wordList[$keyWord]);
        continue;
      }

      if ($word[$letterPosition] != $letter) {
        unset($this->wordList[$keyWord]);
      }
    }
  }

  public function removeWordContainsLetter($letter, $letterPosition)
  {

    foreach ($this->wordList as $keyWord => $word) {
      if ($word[$letterPosition] == $letter) {
        unset($this->wordList[$keyWord]);
      }

    }
  }

  public function updateWordlist($termooResult, $attempt): string
  {

    foreach ($termooResult as $letterPosition => $termoLetter) {
      foreach ($termoLetter as $letter => $letterResult) {

        switch ($letterResult) {
          case 'wrong':
            $this->removeWordContainsLetter($letter, $letterPosition);
            break;

          case 'place':
            $this->removeWordNotContainsLetter($letter);
            $this->removeWordContainsLetter($letter, $letterPosition);
            break;

          case 'right':
            $this->removeWordNotContainsLetterPosition($letter, $letterPosition);
            break;
        }
      }
    }

    echo "[!] Worlist updated to " . count($this->wordList) . " entries \n";




    return $this->getBestWord($attempt);
    //return $this->randomWord();
  }

  public function getBestWord($attempt): mixed
  {

    if (!$this->checkWordList()) {
      return null;
    }


    switch ($attempt) {
      case 1:
      case 3:
        return $this->bestVowelWord();

      default:
        return $this->randomWord();
    }
  }


  static function checkWordResult($result): bool
  {

    foreach ($result as $res) {

      foreach ($res as $re) {
        if ($re == "wrong" || $re == "place") {
          return false;
        }
      }
    }

    return true;
  }

  static function prepareWord($word): string
  {

    $word = trim($word);

    $word = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $word); //SEM IGNORE - Detected an illegal character in input string
    $word = preg_replace('/[^a-z\s]/i', '', $word);

    $word = mb_strtoupper($word);


    return $word;
  }
}
