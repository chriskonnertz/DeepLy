<?php

// Ensure backward compatibility
// @see http://stackoverflow.com/questions/42811164/class-phpunit-framework-testcase-not-found#answer-42828632
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

    public function testGetAndSetConnector()
    {
        $deepLy = $this->getInstance();

        $connector = $deepLy->getConnector();

        $this->assertNotNull($connector);

        $deepLy->setConnector($connector);
    }

    public function testTranslation()
    {
        $deepLy = $this->getInstance();

        $translatedText = $deepLy->translate('Hello world!', 'DE', 'EN');

        $this->assertEquals($translatedText, 'Hallo Welt!');
    }

    public function testGetResultBag()
    {
        $deepLy = $this->getInstance();

        $resultBag = $deepLy->getResultBag();

        $this->assertNull($resultBag);
    }

    public function testGetLang()
    {
        $deepLy = $this->getInstance();

        $langCodes = $deepLy->getLangCodes();

        $this->assertNotNull($langCodes);
    }

}
