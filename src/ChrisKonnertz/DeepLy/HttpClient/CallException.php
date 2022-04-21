<?php

namespace ChrisKonnertz\DeepLy\HttpClient;

/**
 * This exception might be thrown during an API call.
 * The "message" and "code" property will be set.
 *
 * @see HttpClientInterface::ERRORS
 */
class CallException extends \RuntimeException
{

}
