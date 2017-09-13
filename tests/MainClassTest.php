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

    public function testSplitText()
    {
        $deepLy = $this->getInstance();

        $sentences = $deepLy->splitText('Hello world! What a wonderful world.', 'EN');

        $numberOfSentences = sizeof($sentences);
        $this->assertEquals(2, $numberOfSentences);

        $expectedSentences = ['Hello world!', 'What a wonderful world.'];
        $this->assertEquals($expectedSentences, $sentences);
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

    public function testProposeTranslations()
    {
        $deepLy = $this->getInstance();

        $proposals = $deepLy->proposeTranslations('The old man an the sea', 'DE', 'EN');

        $numberOfProposals = sizeof($proposals);
        $this->assertEquals(4, $numberOfProposals);

        // We assume that the result will look like this.
        // If the result will change for some reason,
        // of course the test will fail
        $expectedProposals = [
            'Der alte Mann am Meer',
            'Der alte Mann und das Meer',
            'Der Alte und das Meer',
            'Der Alte am Meer',
        ];
        $this->assertEquals($expectedProposals, $proposals);
    }

    public function testTranslateSentences()
    {
        $deepLy = $this->getInstance();

        $sentences = $deepLy->translateSentences('Hello world! What a wonderful world.', 'DE', 'EN');

        $numberOfSentences = sizeof($sentences);
        $this->assertEquals(2, $numberOfSentences);

        $expectedSentences = ['Hallo Welt!', 'Was für eine wunderbare Welt.'];
        $this->assertEquals($expectedSentences, $sentences);
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
