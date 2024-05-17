<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\CantoSaasApi\Http;

use Fairway\CantoSaasApi\Client;
use JsonException;
use JsonSerializable;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\UriInterface;

abstract class Request implements RequestInterface, JsonSerializable
{
    public function getQueryParams(): ?array
    {
        return null;
    }

    public function getPathVariables(): ?array
    {
        return null;
    }

    public function jsonSerialize(): array
    {
        throw new \Exception('Serializing object not implemented.');
    }

    protected function hasBody(): bool
    {
        return false;
    }

    /**
     * @throws InvalidRequestException
     */
    public function getBody(): string
    {
        try {
            return json_encode($this, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidRequestException(
                'Can not generate json http body.',
                1626885024,
                $e
            );
        }
    }

    protected function buildRequestUrl(Client $client): UriInterface
    {
        $url = $client->getApiUrl($this->getApiPath());

        $pathVariables = $this->getPathVariables();
        $queryParams = $this->getQueryParams();
        if (is_array($pathVariables) === true) {
            $url = rtrim($url, '/');
            $url .= '/' . implode('/', $pathVariables);
        }
        if (is_array($queryParams) && count($queryParams) > 0) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $client->createUri($url);
    }

    /**
     * @throws InvalidRequestException
     */
    public function toHttpRequest(Client $client, array $withHeaders = []): PsrRequestInterface
    {
        $uri = $this->buildRequestUrl($client);
        if ($this->hasBody()) {
            $withHeaders['Content-Type'] = 'application/json';
        }

        $request = $client->createRequest($this->getMethod(), $uri);
        foreach ($withHeaders as $header => $value) {
            $request = $request->withHeader($header, $value);
        }
        if ($this->hasBody()) {
            $request = $request->withBody($client->createStream($this->getBody()));
        }

        return $request;
    }
}
