<?php

use ChrisKonnertz\DeepLy\HttpClient\CurlHttpClient;
use ChrisKonnertz\DeepLy\Protocol\JsonRpcProtocol;
use ChrisKonnertz\DeepLy\ResponseBag\TranslationBag;

// Ensure backward compatibility
// @see http://stackoverflow.com/questions/42811164/class-phpunit-framework-testcase-not-found#answer-42828632
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
        $this->assertEquals(true, $sslVerifyPeer);

        $curlHttpClient->setSslVerifyPeer(false);
        $sslVerifyPeer = $curlHttpClient->getSslVerifyPeer();
        $this->assertEquals(false, $sslVerifyPeer);
    }

    public function testCreateRequestData()
    {
        $protocol = new JsonRpcProtocol();

        $result = $protocol->createRequestData([], 'some_method');
        $this->assertNotNull($result);
    }

    public function testValidateId()
    {
        $protocol = new JsonRpcProtocol();

        $protocol->setValidateId(true);
        $validateId = $protocol->getValidateId();
        $this->assertEquals(true, $validateId);

        $protocol->setValidateId(false);
        $validateId = $protocol->getValidateId();
        $this->assertEquals(false, $validateId);
    }

    public function testTranslationAfterResponse()
    {
        $rawResponseData = '{"id":0,"jsonrpc":"2.0","result":{"source_lang":"EN","source_lang_is_confident":0,'.
            '"target_lang":"DE","translations":[{"beams":[{"num_symbols":4,"postprocessed_sentence":"Hallo Welt!"'.
            ',"score":-5000.23,"totalLogProb":-0.577141},{"num_symbols":5,"postprocessed_sentence":"Hallo, Welt!",'.
            '"score":-5001.52,"totalLogProb":-3.94975},{"num_symbols":4,"postprocessed_sentence":"Hello World!"'.
            ',"score":-5001.55,"totalLogProb":-3.84743}],"timeAfterPreprocessing":0,"timeReceivedFromEndpoint":190,'.
            '"timeSentToEndpoint":15,"total_time_endpoint":1}]}}';

        $protocol = new JsonRpcProtocol();

        // To avoid problems with the ID being set to another value by previous use of the class.
        // Yeah, there we have it, the "singleton is an anti pattern" thing...
        $protocol->setValidateId(false);

        $responseContent = $protocol->processResponseData($rawResponseData);

        $translationBag = new TranslationBag($responseContent);

        $translation = $translationBag->getTranslation();

        $this->assertEquals('Hallo Welt!', $translation);
    }


}
