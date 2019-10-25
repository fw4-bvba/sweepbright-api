<?php
/*
 * This file is part of the fw4/sweepbright-api library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SweepBright;

use InvalidArgumentException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use League\OAuth2\Client\Provider\AbstractProvider;
use Psr\Http\Message\ResponseInterface;

class OAuthProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    protected $urlAccessToken;

    /**
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        if (!isset($options['urlAccessToken'])) {
            throw new InvalidArgumentException(
                'Required option not defined: urlAccessToken'
            );
        }

        parent::__construct($options, $collaborators);
    }
    
    /**
     * @inheritdoc
     */
    protected function getAllowedClientOptions(array $options)
    {
        return array_merge(
            parent::getAllowedClientOptions($options),
            [
            'http_errors',
            'headers',
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getBaseAuthorizationUrl()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->urlAccessToken;
    }

    /**
     * @inheritdoc
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScopes()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $data['error_description'] ?? 'Error',
                $response->getStatusCode(),
                $data
            );
        }
    }

    /**
     * @inheritdoc
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return null;
    }
}
