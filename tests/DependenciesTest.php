<?php

// Ensure backward compatibility
// @see http://stackoverflow.com/questions/42811164/class-phpunit-framework-testcase-not-found#answer-42828632
use ChrisKonnertz\DeepLy\HttpClient\CurlHttpClient;
use ChrisKonnertz\DeepLy\Protocol\JsonRpcProtocol;
use ChrisKonnertz\DeepLy\TranslationBag\TranslationBag;

if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

/**
 * Class DependenciesTest for tests with PHPUnit.
 * The focus is here on all other classes than the main class ChrisKonnertz\DeePly.
 */
class DependenciesTest extends \PHPUnit\Framework\TestCase
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

    public function testGetAndSetSslVerifyPeer()
    {
        $protocol = new JsonRpcProtocol();
        $curlHttpClient = new CurlHttpClient($protocol);

        $curlHttpClient->setSslVerifyPeer(true);
        $sslVerifyPeer = $curlHttpClient->getSslVerifyPeer();
        $this->assertEquals($sslVerifyPeer, true);

        $curlHttpClient->setSslVerifyPeer(false);
        $sslVerifyPeer = $curlHttpClient->getSslVerifyPeer();
        $this->assertEquals($sslVerifyPeer, false);
    }

    public function testCreateRequestData()
    {
        $protocol = new JsonRpcProtocol();

        $result = $protocol->createRequestData([], ['method' => 'something']);

        $this->assertNotNull($result);
    }

    public function testValidateId()
    {
        $protocol = new JsonRpcProtocol();

        $protocol->setValidateId(true);
        $validateId = $protocol->getValidateId();
        $this->assertEquals($validateId, true);

        $protocol->setValidateId(false);
        $validateId = $protocol->getValidateId();
        $this->assertEquals($validateId, false);
    }

    public function testTranslationAfterResponse()
    {
        $rawResponseData = '{"id":2,"jsonrpc":"2.0","result":{"source_lang":"EN","source_lang_is_confident":0,'.
            '"target_lang":"DE","translations":[{"beams":[{"num_symbols":4,"postprocessed_sentence":"Hallo Welt!"'.
            ',"score":-5000.23,"totalLogProb":-0.577141},{"num_symbols":5,"postprocessed_sentence":"Hallo, Welt!",'.
            '"score":-5001.52,"totalLogProb":-3.94975},{"num_symbols":4,"postprocessed_sentence":"Hello World!"'.
            ',"score":-5001.55,"totalLogProb":-3.84743}],"timeAfterPreprocessing":0,"timeReceivedFromEndpoint":190,'.
            '"timeSentToEndpoint":15,"total_time_endpoint":1}]}}';

        $protocol = new JsonRpcProtocol();

        $responseContent = $protocol->processResponseData($rawResponseData);

        $translationBag = new TranslationBag($responseContent);

        $translation = $translationBag->getTranslation();

        $this->assertEquals($translation, 'Hallo Welt!');
    }


}
