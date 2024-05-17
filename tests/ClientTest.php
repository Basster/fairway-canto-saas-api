<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\CantoSaasApi\Tests;

use Fairway\CantoSaasApi\Client;
use Fairway\CantoSaasApi\ClientOptions;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ClientTest extends TestCase
{
    /**
     * @test
     */
    public function createObjectWithDefaultOptions(): void
    {
        $options = new ClientOptions([
            'cantoName' => 'not-empty',
            'appId' => 'not-empty',
            'appSecret' => 'not-empty',
        ]);
        $client = new Client($options);

        self::assertInstanceOf(ClientInterface::class, $client->getHttpClient());
        self::assertInstanceOf(LoggerInterface::class, $client->getLogger());
        self::assertInstanceOf(RequestInterface::class, $client->createRequest('GET', 'https://example.com'));
    }

    /**
     * @test
     */
    public function createObjectWithCustomHttpClient(): void
    {
        $clientOptionsMock = $this->getMockBuilder(ClientOptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHttpClient', 'getLogger', 'getHttpRequestFactory'])
            ->getMock();
        $clientOptionsMock->method('getHttpClient')->willReturn(new \GuzzleHttp\Client());
        $clientOptionsMock->method('getLogger')->willReturn(null);
        $clientOptionsMock->method('getHttpRequestFactory')->willReturn(null);
        $client = new Client($clientOptionsMock);

        self::assertInstanceOf(\GuzzleHttp\Client::class, $client->getHttpClient());
    }

    /**
     * @test
     */
    public function createObjectWithCustomLogger(): void
    {
        $clientOptionsMock = $this->getMockBuilder(ClientOptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHttpClient', 'getHttpClientOptions', 'getLogger', 'getHttpRequestFactory'])
            ->getMock();
        $clientOptionsMock->method('getHttpClient')->willReturn(null);
        $clientOptionsMock->method('getHttpClientOptions')->willReturn([
            'debug' => false,
            'timeout' => 10,
            'userAgent' => 'test',
        ]);
        $clientOptionsMock->method('getLogger')->willReturn(new NullLogger());
        $clientOptionsMock->method('getHttpRequestFactory')->willReturn(null);
        $client = new Client($clientOptionsMock);

        self::assertInstanceOf(NullLogger::class, $client->getLogger());
    }

    /**
     * @test
     */
    public function createRequestFromDefaultRequestFactory(): void
    {
        $request = new Request('GET', 'https://example.com');
        $requestFactory = new class ($request) implements RequestFactoryInterface {
            public Request $request;

            public function __construct(Request $request)
            {
                $this->request = $request;
            }

            public function createRequest(string $method, $uri): RequestInterface
            {
                return $this->request;
            }
        };
        $clientOptionsMock = $this->getMockBuilder(ClientOptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHttpClient', 'getHttpClientOptions', 'getLogger', 'getHttpRequestFactory'])
            ->getMock();
        $clientOptionsMock->method('getHttpClient')->willReturn(null);
        $clientOptionsMock->method('getHttpClientOptions')->willReturn([
            'debug' => false,
            'timeout' => 10,
            'userAgent' => 'test',
        ]);
        $clientOptionsMock->method('getLogger')->willReturn(null);
        $clientOptionsMock->method('getHttpRequestFactory')->willReturn($requestFactory);

        $client = new Client($clientOptionsMock);
        $requestFromFactory = $client->createRequest('GET-IGNORE-ME', 'https://ignore-me.com');

        self::assertSame($requestFromFactory, $request);
    }
}
