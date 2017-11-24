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

        $this->assertInstanceOf(ChrisKonnertz\DeepLy\DeepLy::class, $deepLy);
    }

    public function testGetAndSetProtocol()
    {
        $deepLy = $this->getInstance();

        $protocolOne = $deepLy->getProtocol();
        $this->assertInstanceOf(ChrisKonnertz\DeepLy\Protocol\ProtocolInterface::class, $protocolOne);

        $deepLy->setProtocol($protocolOne);
        $protocolTwo = $deepLy->getProtocol();
        $this->assertEquals($protocolOne, $protocolTwo);
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
        $deepLy->ping();
    }

    public function testSplitText()
    {
        $deepLy = $this->getInstance();

        $sentencesBag = $deepLy->requestSplitText('Hello world! What a wonderful world.', 'EN');

        $lang = $sentencesBag->getLanguage();
        $this->assertEquals('EN', $lang);

        $sentences = $sentencesBag->getAllSentences();

        $expectedSentences = ['Hello world!', 'What a wonderful world.'];
        $this->assertEquals($expectedSentences, $sentences);
    }

    public function testDetectLanguage()
    {
        $deepLy = $this->getInstance();

        $languageCode = $deepLy->detectLanguage('Hello world!');
        $this->assertEquals('EN', $languageCode);

        $languageCode = $deepLy->detectLanguage('Hallo Welt!');
        $this->assertEquals('DE', $languageCode);
    }

    /**
     * @expectedException     \ChrisKonnertz\DeepLy\ResponseBag\BagException
     * @expectedExceptionCode 130
     */
    public function testExceptionDetectionFailed()
    {
        $deepLy = $this->getInstance();

        // DeepL cannot detected the language for this "text" so an exception has to be thrown
        $deepLy->detectLanguage('a');
    }

    public function testTranslation()
    {
        $deepLy = $this->getInstance();

        $translatedText = $deepLy->translate('Hello world!', 'DE', 'EN');
        $this->assertEquals('Hallo Welt!', $translatedText);

        $translationBag = $deepLy->getTranslationBag();
        $this->assertInstanceOf(ChrisKonnertz\DeepLy\ResponseBag\TranslationBag::class, $translationBag);

        $translatedSentences = $translationBag->getTranslatedSentences();
        $this->assertNotNull($translatedSentences);

        $translationAlternatives = $translationBag->getTranslationAlternatives();
        $this->assertNotNull($translationAlternatives);
    }

    public function testItalianTranslation()
    {
        $deepLy = $this->getInstance();

        $translatedText = $deepLy->translate('ciao', 'EN', 'IT');
        $this->assertEquals('hello', $translatedText);
    }

    public function testProposeTranslations()
    {
        $deepLy = $this->getInstance();

        // Sometimes - for an unknown reason - the API does not return the expected proposals.
        // We simply repeat the request until we get the expected result or until a specific limit
        for ($i = 0; $i < 5; $i++) {
            $proposals = $deepLy->proposeTranslations('The old man an the sea', 'DE', 'EN');

            if (sizeof($proposals) == 4) {
                break;
            }
        }

        // Sometimes - for an unknown reason - the API does not return the expected proposals.
        // We will just ignore this rare case.
        if ($proposals == ['Der alte Mann am Meer']) {
            return;
        }

        // We assume that the result will look like this.
        // If the result will change for some reason,
        // of course the test will fail.
        // Unfortunately the result tends to alter.
        $expectedProposals = [           
            'Der alte Mann und das Meer',
            'Der Alte und das Meer',
            'Der alte Mann und die See',
            'Der alte Mann am Meer',
        ];

        $this->assertEquals($expectedProposals, $proposals);
    }

    public function testTranslateSentences()
    {
        $deepLy = $this->getInstance();

        $sentences = ['Hello world!', 'What a wonderful world.'];
        $sentences = $deepLy->translateSentences($sentences, 'DE', 'EN');

        $expectedSentences = ['Hallo Welt!', 'Was für eine wunderbare Welt.'];
        $this->assertEquals($expectedSentences, $sentences);
    }

    public function testTranslationWithLineBreaks()
    {
        $deepLy = $this->getInstance();

        $text = 'Hallo Welt. Wie geht es dir?'.PHP_EOL.PHP_EOL.
            'Mir geht es gut. Ich habe viel Spaß beim Programmieren.';
        $translated = $deepLy->translate($text, 'EN', 'DE');

        $expectedTranslated = 'Hello, world. How are you feeling?'.PHP_EOL.PHP_EOL.
            'I\'m all right. I have a lot of fun programming.';
        $this->assertEquals($expectedTranslated, $translated);
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

    public function testGuzzle()
    {
        $deepLy = $this->getInstance();

        $protocol = $deepLy->getProtocol();
        $guzzleHttpClient = new \ChrisKonnertz\DeepLy\HttpClient\GuzzleHttpClient($protocol);
        $deepLy->setHttpClient($guzzleHttpClient);

        $httpClient = $deepLy->getHttpClient();
        $this->assertEquals($guzzleHttpClient, $httpClient);

        $guzzleOne = $guzzleHttpClient->getGuzzle();
        $this->assertInstanceOf(\GuzzleHttp\Client::class, $guzzleOne);

        $guzzleHttpClient->setGuzzle($guzzleOne);
        $guzzleTwo = $guzzleHttpClient->getGuzzle();
        $this->assertEquals($guzzleOne, $guzzleTwo);

        // We assume that the ping will be successful.
        // If the API is not reachable this will not be the case, of course,
        // and the test will fail.
        $deepLy->ping();

        $translatedText = $deepLy->translate('Hello world!', 'DE', 'EN');

        $this->assertEquals('Hallo Welt!', $translatedText);
    }

}
