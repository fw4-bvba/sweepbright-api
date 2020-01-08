<?php
/*
 * This file is part of the fw4/sweepbright-api library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SweepBright\Tests;

use League\OAuth2\Client\Token\AccessTokenInterface;
use SweepBright\ApiAdapter\ApiAdapter;

final class TestApiAdapter extends ApiAdapter
{
    protected $responseQueue = [];
    
    public function setAccessToken(AccessTokenInterface $token): void
    {
    }
    
    public function requestAccessToken(string $client_id, string $client_secret): AccessTokenInterface
    {
        throw new \Exception('TestApiAdapter does not support access tokens');
    }

    public function queueResponse(string $body)
    {
        $this->responseQueue[] = $body;
    }

    public function queueResponseFromFile(string $filename)
    {
        $response = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'responses' . DIRECTORY_SEPARATOR . $filename);
        $this->queueResponse($response);
    }

    public function requestBody(string $method, string $endpoint, ?string $body = null): ?string
    {
        return array_shift($this->responseQueue) ?? '';
    }
}
