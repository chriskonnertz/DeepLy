<?php

use ChrisKonnertz\DeepLy\HttpClient\CurlHttpClient;

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
        $apiKey = getenv('DEEPL_API_KEY');

        if (! $apiKey) {
            die("No API key set. Please set it via environment var: DEEPL_API_KEY\n");
        }

        return new ChrisKonnertz\DeepLy\DeepLy($apiKey);
    }

    public function testGetAndSetSslVerifyPeer()
    {
        $curlHttpClient = new CurlHttpClient();

        $curlHttpClient->setSslVerifyPeer(true);
        $sslVerifyPeer = $curlHttpClient->getSslVerifyPeer();
        $this->assertEquals(true, $sslVerifyPeer);

        $curlHttpClient->setSslVerifyPeer(false);
        $sslVerifyPeer = $curlHttpClient->getSslVerifyPeer();
        $this->assertEquals(false, $sslVerifyPeer);
    }

}
