<?php

// Ensure backward compatibility
// @see http://stackoverflow.com/questions/42811164/class-phpunit-framework-testcase-not-found#answer-42828632
if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

/**
 * Class MainClassTest for tests with PHPUnit.
 * The focus is here on the main class of this library, ChrisKonnertz\DeePly.
 */
class MainClassTest extends \PHPUnit\Framework\TestCase
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

    public function testGetAndSetProtocol()
    {
        $deepLy = $this->getInstance();

        $protocol = $deepLy->getProtocol();

        $this->assertNotNull($protocol);

        $deepLy->setProtocol($protocol);
    }

    public function testGetAndSetHttpClient()
    {
        $deepLy = $this->getInstance();

        $httpClient = $deepLy->getHttpClient();

        $this->assertNotNull($httpClient);

        $deepLy->setHttpClient($httpClient);
    }

    public function testPing()
    {
        $deepLy = $this->getInstance();

        // We assume that the ping will be successful.
        // If the API is not reachable this will not be the case, of course,
        // and the test will fail.
        $deepLy->ping();
    }

    public function testTranslation()
    {
        $deepLy = $this->getInstance();

        $translatedText = $deepLy->translate('Hello world!', 'DE', 'EN');

        $this->assertEquals($translatedText, 'Hallo Welt!');

        $translationBag = $deepLy->getTranslationBag();

        $this->assertNotNull($translationBag);

        $translatedSentences = $translationBag->getTranslatedSentences();

        $this->assertNotNull($translatedSentences);

        $translationAlternatives = $translationBag->getTranslationAlternatives();

        $this->assertNotNull($translationAlternatives);
    }

    public function testGetTranslationBag()
    {
        $deepLy = $this->getInstance();

        $translationBag = $deepLy->getTranslationBag();

        $this->assertNull($translationBag);
    }

    public function testGetLangCodes()
    {
        $deepLy = $this->getInstance();

        $langCodes = $deepLy->getLangCodes();

        $this->assertNotNull($langCodes);
    }

    public function testSupportsLangCode()
    {
        $deepLy = $this->getInstance();

        $langCodes = $deepLy->getLangCodes(false);
        $langCode = current($langCodes);

        $supportsLang = $deepLy->supportsLangCode($langCode);

        $this->assertEquals($supportsLang, true);
    }

}
