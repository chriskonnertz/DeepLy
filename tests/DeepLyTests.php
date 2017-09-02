<?php

// Ensure backward compatibility
// @see http://stackoverflow.com/questions/42811164/class-phpunit-framework-testcase-not-found#answer-42828632
use ChrisKonnertz\DeepLy\HttpClient\CurlHttpClient;

if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

/**
 * Class DeepLyTests for tests with PHPUnit.
 */
class DeepLyTests extends \PHPUnit\Framework\TestCase
{

    /**
     * Creates and returns an instance of the main class
     *
     * @return \ChrisKonnertz\DeepLy\DeepLy
     */
    protected function getInstance()
    {
        return new ChrisKonnertz\DeepLy\DeepLy();
    }

    public function testInstancing()
    {
        $deepLy = $this->getInstance();

        $this->assertNotNull($deepLy);
    }

    public function testGetAndSetHttpClient()
    {
        $deepLy = $this->getInstance();

        $httpClient = $deepLy->getHttpClient();

        $this->assertNotNull($httpClient);

        $deepLy->setHttpClient($httpClient);
    }

    public function testGetAndSetSslVerifyPeer()
    {
        $curlHttpClient = new CurlHttpClient();

        $currentValue = $curlHttpClient->getSslVerifyPeer();

        $this->assertNotNull($currentValue);

        $curlHttpClient->setSslVerifyPeer($currentValue);

        $internalValue = $curlHttpClient->getSslVerifyPeer();

        $this->assertEquals($currentValue, $internalValue);
    }

    public function testTranslation()
    {
        $deepLy = $this->getInstance();

        $translatedText = $deepLy->translate('Hello world!', 'DE', 'EN');

        $this->assertEquals($translatedText, 'Hallo Welt!');
    }

    public function testGetTranslationBag()
    {
        $deepLy = $this->getInstance();

        $translationBag = $deepLy->getTranslationBag();

        $this->assertNull($translationBag);
    }

    public function testGetLang()
    {
        $deepLy = $this->getInstance();

        $langCodes = $deepLy->getLangCodes();

        $this->assertNotNull($langCodes);
    }

    public function testGetSupported()
    {
        $deepLy = $this->getInstance();

        $langCodes = $deepLy->getLangCodes();
        $langCode = current($langCodes);

        $supportsLang = $deepLy->supportsLang($langCode);

        $this->assertEquals($supportsLang, true);
    }

}
