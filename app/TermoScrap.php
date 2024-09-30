<?php

namespace App;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;

class TermoScrap
{

  private RemoteWebDriver $driver;
  private int $attempts = 0;



  function __construct()
  {

    $chromeOptions = new ChromeOptions();
    $chromeOptions->addArguments(['--start-maximized']);
    $capabilities = DesiredCapabilities::chrome();
    $capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $chromeOptions);

    //putenv('WEBDRIVER_CHROME_DRIVER=' . CHROME_DRIVER_PATH);
    // $this->driver = ChromeDriver::start($capabilities);
    $this->driver = RemoteWebDriver::create("http://localhost:4444/", $capabilities);
  }

  public function finish()
  {
    $this->driver->quit();
  }

  public function start(): void
  {

    $this->driver->get('https://term.ooo/');

    $this->driver->wait()->until(
      WebDriverExpectedCondition::elementTextContains(WebDriverBy::className(class_name: 'help_termo'), 'palavra')
    );

    $this->driver->findElement(WebDriverBy::tagName("body"))->sendKeys(WebDriverKeys::ESCAPE);

  }

  public function getWcRows(): array
  {
    $element = $this->driver->findElement(WebDriverBy::id('board0'));
    $shadowRoot = $element->getShadowRoot();
    $elementInShadow = $shadowRoot->findElement(WebDriverBy::id('hold'));

    return $elementInShadow->findElements(WebDriverBy::tagName("wc-row"));
  }

  public function getLineAttempt(): array
  {

    $wcRows = $this->getWcRows();
    $shadowRoot = $wcRows[$this->attempts]->getShadowRoot();
    $lineElements = $shadowRoot->findElements(WebDriverBy::className("letter"));

    return $lineElements;
  }

  public function resultByLineAttempt($word): array
  {
    $wcRows = $this->getWcRows();
    $shadowRoot = $wcRows[$this->attempts]->getShadowRoot();
    $lineElements = $shadowRoot->findElements(WebDriverBy::className("letter"));

    $wordResult = [];
    $inc = 0;
    foreach ($lineElements as $key => $lineEl) {
      $wordResult[$inc] = [$word[$key] => trim(str_replace("letter", "", $lineEl->getAttribute("class")))];
      $inc++;
    }

    return $wordResult;
  }

  public function find($word): array
  {
    $lineElements = $this->getLineAttempt();


    foreach ($lineElements as $key => $lineEl) {
      $lineEl->sendKeys($word[$key]);
      usleep(200);
    }
    sleep(1);
    $this->driver->findElement(WebDriverBy::tagName("body"))->sendKeys(WebDriverKeys::ENTER);
    sleep(4);

    $attemptResult = $this->resultByLineAttempt($word);

    $this->attempts++;


    return [
      "success" => Words::checkWordResult($attemptResult),
      "word" => $word,
      "attempt" => $this->attempts,
      "letters" => $attemptResult
    ];

  }
}
