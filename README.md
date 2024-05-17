# PHP client for Canto API

## Setup

```shell
composer require fairway/canto-saas-api guzzlehttp/guzzle
```

## Example usage

```php
use Fairway\CantoSaasApi\ClientOptions;
use Fairway\CantoSaasApi\Client;
use Fairway\CantoSaasApi\Http\LibraryTree\GetTreeRequest;

$clientOptions = new ClientOptions([
    'cantoName' => 'my-canto-name',
    'cantoDomain' => 'canto.de',
    'appId' => '123456789',
    'appSecret' => 'my-app-secret',
]);
$client = new Client($clientOptions);
$accessToken = $client->authorizeWithClientCredentials('my-user@email.com')
                      ->getAccessToken();
$client->setAccessToken($accessToken);
$allFolders = $client->libraryTree()
                     ->getTree(new GetTreeRequest())
                     ->getResults();
```

### Use with any PSR-17 and PSR-18 compatible http-client and http-factory

If you don't want to use [guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle) as your http-client, you can work with any [PSR-17](https://www.php-fig.org/psr/psr-17/) compatible http-factory and [PSR-18](https://www.php-fig.org/psr/psr-18/) compatible http-client.

Simply pass instances to the `ClientOptions` options, like that:

```php
$clientOptions = new ClientOptions([
    'cantoName' => 'my-canto-name',
    'cantoDomain' => 'canto.de',
    'appId' => '123456789',
    'appSecret' => 'my-app-secret',
    'httpClient' => new Psr18CompatibleHttpClient(),
    'httpRequestFactory' => new Psr17CompatibleHttpRequestFactory(),
]);

# instantiate and use the client as normal.
$client = new Client($clientOptions);

# if you want to create a request, you can do this from the client itself:
$client->createRequest($method, $url); # will return a \Psr\Http\Message\RequestInterface
```
