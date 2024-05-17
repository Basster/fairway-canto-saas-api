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
use Fairway\CantoSaasApi\Endpoint\LibraryTree;
use Fairway\CantoSaasApi\Http\LibraryTree\GetTreeRequest;
use Fairway\CantoSaasApi\Http\LibraryTree\SearchFolderRequest;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LibraryTreeTest extends TestCase
{
    /**
     * @test
     */
    public function searchFolderContentSuccessfulObtainResponse(): void
    {
        $responseBody = '{' .
            '"facets":[{"key":"facet-value"}],' .
            '"results":[{"result-id":1234}],' .
            '"limit":100,' .
            '"found":500,' .
            '"sortBy":"name",' .
            '"sortDirection":"ascending",' .
            '"matchExpr":"test"' .
            '}';
        $mockHandler = new MockHandler([new Response(200, [], $responseBody)]);
        $clientMock = $this->buildClientMock($mockHandler);
        assert($clientMock instanceof Client);

        $libraryTreeEndpoint = new LibraryTree($clientMock);
        $searchFolderRequest = $this->buildListFolderContentRequestMock();
        assert($searchFolderRequest instanceof SearchFolderRequest);
        $response = $libraryTreeEndpoint->searchFolderContent($searchFolderRequest);

        self::assertSame([['key' => 'facet-value']], $response->getFacets());
        self::assertSame([['result-id' => 1234]], $response->getResults());
        self::assertSame(100, $response->getLimit());
        self::assertSame(500, $response->getFound());
        self::assertSame('name', $response->getSortBy());
        self::assertSame('ascending', $response->getSortDirection());
        self::assertSame('test', $response->getMatchExpr());
    }

    /**
     * @test
     */
    public function searchFolderContentExpectNotAuthorizedException(): void
    {
        $this->expectExceptionCode(1626717511);

        $mockHandler = new MockHandler([
            new RequestException(
                'Error Communicating with Server',
                new Request('GET', 'test'),
                new Response(401, [], '[]')
            )
        ]);
        $clientMock = $this->buildClientMock($mockHandler);
        assert($clientMock instanceof Client);

        $libraryTreeEndpoint = new LibraryTree($clientMock);
        $searchFolderRequest = $this->buildListFolderContentRequestMock();
        assert($searchFolderRequest instanceof SearchFolderRequest);
        $libraryTreeEndpoint->searchFolderContent($searchFolderRequest);
    }

    /**
     * @test
     */
    public function searchFolderContentExpectUnexpectedHttpStatusException(): void
    {
        $this->expectExceptionCode(1627649307);

        $mockHandler = new MockHandler([
            new RequestException(
                'Error Communicating with Server',
                new Request('GET', 'test'),
                new Response(400, [], '[]')
            )
        ]);
        $clientMock = $this->buildClientMock($mockHandler);
        assert($clientMock instanceof Client);

        $libraryTreeEndpoint = new LibraryTree($clientMock);
        $searchFolderRequest = $this->buildListFolderContentRequestMock();
        assert($searchFolderRequest instanceof SearchFolderRequest);
        $libraryTreeEndpoint->searchFolderContent($searchFolderRequest);
    }

    /**
     * @test
     */
    public function getTreeSuccessfulObtainResponse(): void
    {
        $responseBody = '{"results":[{"id":"test"}],"sortBy":"time","sortDirection":"descending"}';
        $mockHandler = new MockHandler([new Response(200, [], $responseBody)]);
        $clientMock = $this->buildClientMock($mockHandler);

        assert($clientMock instanceof Client);
        $libraryTreeEndpoint = new LibraryTree($clientMock);
        $requestMock = $this->buildGetTreeRequestMock();
        assert($requestMock instanceof GetTreeRequest);
        $response = $libraryTreeEndpoint->getTree($requestMock);

        self::assertSame([['id' => 'test']], $response->getResults());
        self::assertSame('time', $response->getSortBy());
        self::assertSame('descending', $response->getSortDirection());
    }

    /**
     * @test
     */
    public function getTreeExpectNotAuthorizedException(): void
    {
        $this->expectExceptionCode(1626717511);

        $mockHandler = new MockHandler([new RequestException('', new Request('GET', 'test'), new Response(401, [], '[]'))]);
        $clientMock = $this->buildClientMock($mockHandler);
        assert($clientMock instanceof Client);

        $libraryTreeEndpoint = new LibraryTree($clientMock);
        $treeRequest = $this->buildGetTreeRequestMock();
        assert($treeRequest instanceof GetTreeRequest);
        $libraryTreeEndpoint->getTree($treeRequest);
    }

    /**
     * @test
     */
    public function getTreeExpectUnexpectedHttpStatusException(): void
    {
        $this->expectExceptionCode(1627649307);

        $mockHandler = new MockHandler([new RequestException('', new Request('GET', 'test'), new Response(400, [], '[]'))]);
        $clientMock = $this->buildClientMock($mockHandler);
        assert($clientMock instanceof Client);

        $libraryTreeEndpoint = new LibraryTree($clientMock);
        $treeRequest = $this->buildGetTreeRequestMock();
        assert($treeRequest instanceof GetTreeRequest);
        $libraryTreeEndpoint->getTree($treeRequest);
    }

    protected function buildClientMock(MockHandler $mockHandler): Client
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

    protected function buildListFolderContentRequestMock(): MockObject
    {
        $requestMock = $this->getMockBuilder(SearchFolderRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQueryParams', 'getPathVariables'])
            ->getMock();
        $requestMock->method('getQueryParams')->willReturn(null);
        $requestMock->method('getPathVariables')->willReturn(null);
        return $requestMock;
    }

    protected function buildGetTreeRequestMock(): MockObject
    {
        $requestMock = $this->getMockBuilder(GetTreeRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQueryParams', 'getPathVariables'])
            ->getMock();
        $requestMock->method('getQueryParams')->willReturn(null);
        $requestMock->method('getPathVariables')->willReturn(null);
        return $requestMock;
    }
}
