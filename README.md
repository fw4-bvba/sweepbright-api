# SweepBright API

PHP client for the [SweepBright](https://www.sweepbright.com) Website API. For terms of use and API credentials, refer to [the official documentation](https://website.sweepbright.com/docs/).

## Installation

`composer require fw4/sweepbright-api`

## Usage

```php
$client = new \SweepBright\SweepBright();
$accessToken = getAccessTokenFromDataStore();
if (empty($accessToken) || $accessToken->hasExpired()) {
    $accessToken = $client->requestAccessToken($clientId, $clientSecret);
    saveAccessTokenToDataStore($accessToken);
} else {
    $client->setAccessToken($accessToken);
}
$estate = $client->getEstate($estateId);
```

The API client provides the following methods:
* `public function getEstate(string $estate_id): Response`
* `public function setEstateUrl(string $estate_id, string $url): void`
* `public function saveContact(array $data, ?array $preferences = null, ?array $location_preference = null): void`
* `public function saveEstateContact(string $estate_id, array $data): void`

For more information about available request parameters and response properties, refer to [the official API documentation](https://website.sweepbright.com/docs/). Properties on both requests and responses are style agnostic, and are accessible through both snake_case and camelCase.