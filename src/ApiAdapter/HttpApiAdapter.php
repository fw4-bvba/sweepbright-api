<?php
/*
 * This file is part of the fw4/sweepbright-api library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SweepBright\ApiAdapter;

use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;
use SweepBright\OAuthProvider;
use SweepBright\Exception\ServerValidationException;

final class HttpApiAdapter extends ApiAdapter
{
    private const BASE_URL = 'https://website.sweepbright.com/api/';
    
    private $oAuthProvider;
    private $accessToken;
    
    public function __construct(int $version)
    {
        $package_version = \PackageVersions\Versions::getVersion('fw4/sweepbright-api');
        
        $this->oAuthProvider = new OAuthProvider([
            'urlAccessToken' => self::BASE_URL . 'oauth/token',
            'headers' => [
                'Accept'       => 'application/vnd.sweepbright.v' . $version . '+json',
                'Content-Type' => 'application/json',
                'User-Agent'   => 'fw4-sweepbright-api/' . $package_version,
            ],
        ]);
    }
    
    public function setAccessToken(AccessTokenInterface $token): void
    {
        $this->accessToken = $token;
    }
    
    public function requestAccessToken(string $client_id, string $client_secret): AccessTokenInterface
    {
        if (empty($this->accessToken) || $this->accessToken->hasExpired()) {
            $this->accessToken = $this->oAuthProvider->getAccessToken('client_credentials', [
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
            ]);
        }
        return $this->accessToken;
    }
    
    private function getAccessToken(): AccessTokenInterface
    {
        if (empty($this->accessToken)) {
            throw new IdentityProviderException('Missing access token');
        }
        return $this->accessToken;
    }
    
    public function requestBody(string $method, string $endpoint, ?string $body = null): ?string
    {
        $options = [];
        if (isset($body)) {
            $options['body'] = $body;
        }
        $url = self::BASE_URL . ltrim($endpoint, '/');
        $request = $this->oAuthProvider->getAuthenticatedRequest($method, $url, $this->getAccessToken(), $options);
        
        try {
            $response = $this->getHttpClient()->send($request);
        } catch (ClientException $exception) {
            if ($exception->getCode() === 422) {
                $response_body = $exception->getResponse()->getBody()->getContents();
                $response_body = json_decode($response_body, true);
                throw new ServerValidationException(
                    $exception->getMessage(),
                    $exception->getRequest(),
                    $exception->getResponse(),
                    $response_body['errors'] ?? [],
                    $exception
                );
            } else if ($exception->getCode() === 404) {
                return null;
            } else {
                throw $exception;
            }
        }
        return $response->getBody()->getContents();
    }
    
    private function getHttpClient()
    {
        return $this->oAuthProvider->getHttpClient();
    }
}
