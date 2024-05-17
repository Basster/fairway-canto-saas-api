<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\CantoSaasApi;

use Fairway\CantoSaasApi\Endpoint\Asset;
use Fairway\CantoSaasApi\Endpoint\Authorization\OAuth2;
use Fairway\CantoSaasApi\Endpoint\LibraryTree;
use Fairway\CantoSaasApi\Endpoint\Upload;
use Fairway\CantoSaasApi\Exception\HttpClientException;
use Fairway\CantoSaasApi\Helper\MdcUrlHelper;
use Fairway\CantoSaasApi\Http\Authorization\OAuth2Request;
use Fairway\CantoSaasApi\Http\Authorization\OAuth2Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Client implements RequestFactoryInterface
{
    protected const API_VERSION = 'v1';
    protected const API_ROUTE = 'https://%s.%s/api';
    protected const MDC_ROUTE = 'https://%s/rendition/%s/%s';

    protected ClientOptions $options;

    protected LoggerInterface $logger;

    protected ClientInterface $httpClient;

    protected RequestFactoryInterface $requestFactory;

    protected ?string $accessToken = null;

    public function __construct(ClientOptions $options)
    {
        $this->options = $options;
        $this->httpClient = $this->options->getHttpClient() ?? $this->buildHttpClient();
        $this->logger = $this->options->getLogger() ?? new NullLogger();
        $this->requestFactory = $this->options->getHttpRequestFactory() ?? $this->buildRequestFactory();
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    protected function buildHttpClient(): ClientInterface
    {
        if (class_exists('\GuzzleHttp\Client')) {
            return new \GuzzleHttp\Client([
                'allow_redirects' => true,
                'connect_timeout' => (int)$this->options->getHttpClientOptions()['timeout'],
                'debug' => (bool)$this->options->getHttpClientOptions()['debug'],
                'headers' => [
                    'userAgent' => $this->options->getHttpClientOptions()['userAgent'],
                ],
            ]);
        }

        throw HttpClientException::noDefaultHttpClient();
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    protected function buildRequestFactory(): RequestFactoryInterface
    {
        if (class_exists('\GuzzleHttp\Psr7\Request')) {
            return new class () implements RequestFactoryInterface {
                public function createRequest(string $method, $uri): RequestInterface
                {
                    return new \GuzzleHttp\Psr7\Request($method, $uri);
                }
            };
        }

        throw HttpClientException::noDefaultHttpRequestFactory();
    }

    /**
     * @throws Endpoint\Authorization\AuthorizationFailedException|Endpoint\Authorization\NotAuthorizedException
     */
    public function authorizeWithClientCredentials(string $userId = '', string $scope = OAuth2Request::SCOPE_ADMIN): OAuth2Response
    {
        $request = new OAuth2Request();
        $request->setAppId($this->options->getAppId())
            ->setAppSecret($this->options->getAppSecret())
            ->setRedirectUri($this->options->getRedirectUri())
            ->setScope($scope);
        if ($userId !== '') {
            $request->setUserId($userId);
        }

        $OAuth2 = new OAuth2($this);
        $response = $OAuth2->obtainAccessToken($request);
        $this->setAccessToken($response->getAccessToken());

        return $response;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): void
    {
        if ($accessToken !== '') {
            $this->accessToken = $accessToken;
        }
    }

    /**
     * @param string $method
     * @param \Psr\Http\Message\UriInterface|string $uri
     * @return RequestInterface
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return $this->requestFactory->createRequest($method, $uri);
    }

    public function asset(): Asset
    {
        return new Asset($this);
    }

    public function libraryTree(): LibraryTree
    {
        return new LibraryTree($this);
    }

    public function upload(): Upload
    {
        return new Upload($this);
    }

    public function mdc(): MdcUrlHelper
    {
        return new MdcUrlHelper($this);
    }

    public function getApiUrl(string $path = null): string
    {
        $url = sprintf(
            self::API_ROUTE,
            $this->getOptions()->getCantoName(),
            $this->getOptions()->getCantoDomain(),
        );
        return sprintf(
            '%s/%s/%s',
            $url,
            self::API_VERSION,
            $path ?? ''
        );
    }

    public function getOptions(): ClientOptions
    {
        return $this->options;
    }

    public function getMdcUrl(string $assetId = '', string $scheme = 'image'): string
    {
        $path = '';
        if ($assetId) {
            $path = sprintf('%s_%s/', $scheme, $assetId);
        }
        return sprintf(
            self::MDC_ROUTE,
            $this->options->getMdcDomainName(),
            $this->options->getMdcAwsAccountId(),
            $path,
        );
    }
}
