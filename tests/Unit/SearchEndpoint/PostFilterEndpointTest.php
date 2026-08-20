<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\SearchEndpoint;

use Biano\ElasticsearchDSL\Query\MatchAllQuery;
use Biano\ElasticsearchDSL\SearchEndpoint\PostFilterEndpoint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function json_encode;

class PostFilterEndpointTest extends TestCase
{

    public function testItCanBeInstantiated(): void
    {
        self::assertInstanceOf(PostFilterEndpoint::class, new PostFilterEndpoint());
    }

    public function testGetOrder(): void
    {
        $instance = new PostFilterEndpoint();

        self::assertEquals(1, $instance->getOrder());
    }

    public function testNormalization(): void
    {
        $instance = new PostFilterEndpoint();
        $normalizerInterface = $this->createStub(NormalizerInterface::class);

        self::assertFalse($instance->normalize($normalizerInterface));

        $matchAll = new MatchAllQuery();
        $instance->add($matchAll);

        self::assertEquals(
            json_encode($matchAll->toArray()),
            json_encode($instance->normalize($normalizerInterface)),
        );
    }

    public function testEndpointGetter(): void
    {
        $filterName = 'acme_post_filter';
        $filter = new MatchAllQuery();

        $endpoint = new PostFilterEndpoint();
        $endpoint->add($filter, $filterName);
        $builders = $endpoint->getAll();

        self::assertCount(1, $builders);
        self::assertSame($filter, $builders[$filterName]);
    }

}
