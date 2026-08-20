<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\SearchEndpoint;

use Biano\ElasticsearchDSL\SearchEndpoint\SortEndpoint;
use Biano\ElasticsearchDSL\Sort\FieldSort;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SortEndpointTest extends TestCase
{

    public function testItCanBeInstantiated(): void
    {
        self::assertInstanceOf(SortEndpoint::class, new SortEndpoint());
    }

    public function testNormalize(): void
    {
        $instance = new SortEndpoint();

        $normalizerInterface = $this->createStub(NormalizerInterface::class);

        $sort = new FieldSort('acme', ['order' => FieldSort::ASC]);
        $instance->add($sort);

        self::assertEquals(
            [$sort->toArray()],
            $instance->normalize($normalizerInterface),
        );
    }

    public function testEndpointGetter(): void
    {
        $sortName = 'acme_sort';
        $sort = new FieldSort('acme');
        $endpoint = new SortEndpoint();
        $endpoint->add($sort, $sortName);
        $builders = $endpoint->getAll();

        self::assertCount(1, $builders);
        self::assertSame($sort, $builders[$sortName]);
    }

}
