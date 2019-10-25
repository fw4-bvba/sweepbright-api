<?php
/*
 * This file is part of the fw4/sweepbright-api library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SweepBright;

use League\OAuth2\Client\Token\AccessTokenInterface;
use SweepBright\Response\Response;
use SweepBright\ApiAdapter\ApiAdapterInterface;
use SweepBright\ApiAdapter\HttpApiAdapter;

final class SweepBright
{
    private $apiAdapter;
    
    /**
     * Get estate data. Only callable after the webhook was triggered for the
     * estate in question.
     *
     * @param string $estate_id
     */
    public function getEstate(string $estate_id): Response
    {
        $response = $this->getApiAdapter()->request('GET', 'estates/' . $estate_id);
        return new Response($response);
    }
    
    /**
     * Set or change the public URL for an estate.
     *
     * @param string $estate_id
     * @param string $url
     */
    public function setEstateUrl(string $estate_id, string $url): void
    {
        $request = new Request\SetEstateUrlRequest([
            'url' => $url,
        ]);
        $this->getApiAdapter()->request('PUT', 'estates/' . $estate_id . '/url', $request);
    }
    
    /**
     * Save contact information for a lead on general contact forms.
     *
     * @param mixed $data                Associative array or instance of SaveContactRequest
     * @param mixed $preferences         Associative array or instance of SaveContactRequestPreferences
     * @param mixed $location_preference Associative array or instance of SaveContactRequestLocationPreference
     */
    public function saveContact($data, $preferences = null, $location_preference = null): void
    {
        $request = ($data instanceof Request\SaveContactRequest) ? $data : new Request\SaveContactRequest($data);
        if (isset($preferences)) {
            if ($preferences instanceof Request\SaveContactRequestPreferences) {
                $request->preferences = $preferences;
            } else {
                $request->preferences = new Request\SaveContactRequestPreferences($preferences);
            }
        }
        if (isset($location_preference)) {
            if ($location_preference instanceof Request\SaveContactRequestLocationPreference) {
                $request->location_preference = $location_preference;
            } else {
                $request->location_preference = new Request\SaveContactRequestLocationPreference($location_preference);
            }
        }
        $this->getApiAdapter()->request('POST', 'contacts', $request);
    }
    
    /**
     * Save contact information for a lead on estate-specific contact forms.
     *
     * @param string $estate_id
     * @param mixed  $data      Associative array or instance of SaveContactRequest
     */
    public function saveEstateContact(string $estate_id, $data): void
    {
        $request = ($data instanceof Request\SaveContactRequest) ? $data : new Request\SaveContactRequest($data);
        $this->getApiAdapter()->request('POST', 'estates/' . $estate_id . '/contacts', $request);
    }
    
    // Access token
    
    /**
     * Reuse a previously requested access token.
     *
     * @param League\OAuth2\Client\Token\AccessTokenInterface $token
     */
    public function setAccessToken(AccessTokenInterface $token): void
    {
        $this->getApiAdapter()->setAccessToken($token);
    }
    
    /**
     * Request a new access token using client credentials.
     *
     * @param string $client_id
     * @param string $client_secret
     */
    public function requestAccessToken(string $client_id, string $client_secret): AccessTokenInterface
    {
        return $this->getApiAdapter()->requestAccessToken($client_id, $client_secret);
    }
    
    // Api adapter
    
    public function setApiAdapter(ApiAdapterInterface $adapter): void
    {
        $this->apiAdapter = $adapter;
    }
    
    private function getApiAdapter(): ApiAdapterInterface
    {
        if (!isset($this->apiAdapter)) {
            $this->apiAdapter = new HttpApiAdapter();
        }
        return $this->apiAdapter;
    }
}
