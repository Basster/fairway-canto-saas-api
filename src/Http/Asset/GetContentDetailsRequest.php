<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Ecentral\CantoSaasApiClient\Http\Asset;

use Ecentral\CantoSaasApiClient\Http\RequestInterface;

class GetContentDetailsRequest implements RequestInterface
{
    protected string $scheme;

    protected string $contentId;

    public function __construct(string $contentId, string $scheme)
    {
        $this->contentId = $contentId;
        $this->scheme = $scheme;
    }

    public function getQueryParams(): ?array
    {
        return null;
    }

    public function getPathVariables(): ?array
    {
        return [
            $this->scheme,
            $this->contentId,
        ];
    }
}
