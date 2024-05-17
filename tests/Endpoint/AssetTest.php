<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\CantoSaasApi\Tests\Endpoint;

use Fairway\CantoSaasApi\Client;
use Fairway\CantoSaasApi\ClientOptions;
use Fairway\CantoSaasApi\Endpoint\Asset;
use Fairway\CantoSaasApi\Http\Asset\BatchUpdatePropertiesRequest;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AssetTest extends TestCase
{
    /**
     * @test
     */
    public function batchUpdatePropertiesSuccessfulObtainResponse(): void
    {
        $mockHandler = new MockHandler([new Response(200, [], 'success')]);
        $clientMock = $this->buildClient($mockHandler);

        assert($clientMock instanceof Client);
        $assetEndpoint = new Asset($clientMock);
        $request = $this->buildRequestMock();
        assert($request instanceof BatchUpdatePropertiesRequest);
        $response = $assetEndpoint->batchUpdateProperties($request);

        self::assertSame('success', $response->getBody());
    }

    /**
     * @test
     */
    public function batchUpdatePropertiesExpectNotAuthorizedException(): void
    {
        $this->expectExceptionCode(1626717511);

        $mockHandler = new MockHandler([
            new RequestException(
                'Error Communicating with Server',
                new Request('PUT', 'test'),
                new Response(401)
            )
        ]);
        $clientMock = $this->buildClient($mockHandler);
        assert($clientMock instanceof Client);

        $assetEndpoint = new Asset($clientMock);
        $request = $this->buildRequestMock();
        assert($request instanceof BatchUpdatePropertiesRequest);
        $assetEndpoint->batchUpdateProperties($request);
    }

    /**
     * @test
     */
    public function batchUpdatePropertiesExpectUnexpectedHttpStatusException(): void
    {
        $this->expectExceptionCode(1627649307);

        $mockHandler = new MockHandler([
            new RequestException(
                'Error Communicating with Server',
                new Request('PUT', 'test'),
                new Response(400, [], 'success')
            )
        ]);
        $clientMock = $this->buildClient($mockHandler);
        assert($clientMock instanceof Client);

        $assetEndpoint = new Asset($clientMock);
        $request = $this->buildRequestMock();
        assert($request instanceof BatchUpdatePropertiesRequest);
        $assetEndpoint->batchUpdateProperties($request);
    }

    protected function buildClient(MockHandler $mockHandler): Client
    {
        $httpClient = new HttpClient([
            'handler' => HandlerStack::create($mockHandler),
        ]);

        return new Client(new ClientOptions([
            'cantoName' => 'test',
            'cantoDomain' => 'canto.com',
            'appId' => 'test',
            'appSecret' => 'test',
            'httpClient' => $httpClient,
        ]));
    }

    protected function buildRequestMock(): MockObject
    {
        $requestMock = $this->getMockBuilder(BatchUpdatePropertiesRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBody'])
            ->getMock();
        $requestMock->method('getBody')->willReturn('{"contents":[],"properties":[]}');
        return $requestMock;
    }
}
