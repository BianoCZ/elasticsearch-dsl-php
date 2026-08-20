<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\SearchEndpoint;

use Biano\ElasticsearchDSL\Query\MatchAllQuery;
use Biano\ElasticsearchDSL\SearchEndpoint\QueryEndpoint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class QueryEndpointTest extends TestCase
{

    public function testItCanBeInstantiated(): void
    {
        self::assertInstanceOf(QueryEndpoint::class, new QueryEndpoint());
    }

    public function testGetOrder(): void
    {
        $instance = new QueryEndpoint();

        self::assertEquals(2, $instance->getOrder());
    }

    public function testEndpoint(): void
    {
        $instance = new QueryEndpoint();
        $normalizerInterface = $this->createStub(NormalizerInterface::class);

        self::assertFalse($instance->normalize($normalizerInterface));

        $matchAll = new MatchAllQuery();
        $instance->add($matchAll);

        self::assertEquals(
            $matchAll->toArray(),
            $instance->normalize($normalizerInterface),
        );
    }

    public function testEndpointGetter(): void
    {
        $queryName = 'acme_query';
        $query = new MatchAllQuery();
        $endpoint = new QueryEndpoint();
        $endpoint->add($query, $queryName);
        $builders = $endpoint->getAll();

        self::assertCount(1, $builders);
        self::assertSame($query, $builders[$queryName]);
    }

}
