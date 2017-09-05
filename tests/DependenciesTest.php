<?php

// Ensure backward compatibility
// @see http://stackoverflow.com/questions/42811164/class-phpunit-framework-testcase-not-found#answer-42828632
use ChrisKonnertz\DeepLy\HttpClient\CurlHttpClient;
use ChrisKonnertz\DeepLy\Protocol\JsonRpcProtocol;

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

        $currentValue = $curlHttpClient->getSslVerifyPeer();

        $this->assertNotNull($currentValue);

        $curlHttpClient->setSslVerifyPeer($currentValue);

        $internalValue = $curlHttpClient->getSslVerifyPeer();

        $this->assertEquals($currentValue, $internalValue);
    }

}
