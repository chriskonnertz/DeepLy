<?php

// Ensure backward compatibility
// @see http://stackoverflow.com/questions/42811164/class-phpunit-framework-testcase-not-found#answer-42828632
if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

/**
 * Class MainClassTest for tests with PHPUnit.
 * The focus is here on the main class of this library, the DeepLy class.
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
        $apiKey = getenv('DEEPL_API_KEY');

        return new ChrisKonnertz\DeepLy\DeepLy($apiKey);
    }

    public function testInstancing()
    {
        $deepLy = $this->getInstance();

        $this->assertInstanceOf(ChrisKonnertz\DeepLy\DeepLy::class, $deepLy);
    }

    public function testGetAndSetHttpClient()
    {
        $deepLy = $this->getInstance();

        $httpClientOne = $deepLy->getHttpClient();
        $this->assertInstanceOf(ChrisKonnertz\DeepLy\HttpClient\HttpClientInterface::class, $httpClientOne);

        $deepLy->setHttpClient($httpClientOne);
        $httpClientTwo = $deepLy->getHttpClient();
        $this->assertEquals($httpClientOne, $httpClientTwo);
    }

    public function testPing()
    {
        $deepLy = $this->getInstance();

        // We assume that the ping will be successful.
        // If the API is not reachable this will not be the case, of course,
        // and the test will fail.
        $duration = $deepLy->ping();

        $this->assertIsFloat($duration);
    }

    public function testDetectLanguage()
    {
        $deepLy = $this->getInstance();

        $languageCode = $deepLy->detectLanguage('Hello world! Where do you want to go today?');
        $this->assertEquals('EN', $languageCode);

        $languageCode = $deepLy->detectLanguage('Hallo Welt! Wohin möchtest du heute gehen?');
        $this->assertEquals('DE', $languageCode);
    }

    public function testTranslation()
    {
        $deepLy = $this->getInstance();

        $translatedText = $deepLy->translate('Hello world!', 'DE', 'EN');
        $this->assertEquals('Hallo Welt!', $translatedText);
    }

    public function testItalianTranslation()
    {
        $deepLy = $this->getInstance();

        $translatedText = $deepLy->translate('ciao', 'EN', 'IT');
        $this->assertEquals('hello', $translatedText);
    }

    public function testTranslationWithLineBreaks()
    {
        $deepLy = $this->getInstance();

        $text = 'Hallo Welt. Wie geht es dir?'.PHP_EOL.PHP_EOL.
            'Ich habe viel Spaß beim Programmieren.';
        $translated = $deepLy->translate($text, 'EN', 'DE');

        $expectedTranslated = 'Hello world. How are you?'.PHP_EOL.PHP_EOL.
            'I\'m having a lot of fun programming.';
        $this->assertEquals($expectedTranslated, $translated);
    }

    public function testGetLangCodes()
    {
        $deepLy = $this->getInstance();

        $langCodes = $deepLy->getLangCodes();

        $this->assertNotNull($langCodes);
        $this->assertGreaterThan(0, sizeof($langCodes));
        $this->assertEquals('auto', current($langCodes)); // The auto lang code must be the first item in the array
    }

    public function testSupportsLangCode()
    {
        $deepLy = $this->getInstance();

        $langCodes = $deepLy->getLangCodes(false);
        $langCode = current($langCodes);

        $supportsLang = $deepLy->supportsLangCode($langCode);

        $this->assertEquals(true, $supportsLang);
    }

    public function testGetLangName()
    {
        $deepLy = $this->getInstance();

        $langName = $deepLy->getLangName('EN');
        $this->assertEquals('English', $langName);

        $langName = $deepLy->getLangName('DE');
        $this->assertEquals('German', $langName);
    }

    public function testGetLangCodeByName()
    {
        $deepLy = $this->getInstance();

        $langCode = $deepLy->getLangCodeByName('English');
        $this->assertSame('EN', $langCode);
        $langCode = $deepLy->getLangCodeByName('German');
        $this->assertSame('DE', $langCode);
    }

}
