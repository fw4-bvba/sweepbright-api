<?php
/*
 * This file is part of the fw4/sweepbright-api library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SweepBright\ApiAdapter;

use League\OAuth2\Client\Token\AccessTokenInterface;

interface ApiAdapterInterface
{
    public function setAccessToken(AccessTokenInterface $token): void;
    public function requestAccessToken(string $client_id, string $client_secret): AccessTokenInterface;
    public function requestBody(string $method, string $endpoint, ?string $body = null): string;
}
