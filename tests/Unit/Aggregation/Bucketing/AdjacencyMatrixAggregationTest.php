<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Aggregation\Bucketing;

use Biano\ElasticsearchDSL\Aggregation\Bucketing\AdjacencyMatrixAggregation;
use Biano\ElasticsearchDSL\BuilderInterface;
use PHPUnit\Framework\TestCase;

class AdjacencyMatrixAggregationTest extends TestCase
{

    public function testFiltersAggregationGetArray(): void
    {
        $mock = $this->createStub(BuilderInterface::class);
        $aggregation = new AdjacencyMatrixAggregation('test_agg');
        $aggregation->addFilter('name', $mock);

        $result = $aggregation->getArray();

        self::assertArrayHasKey('filters', $result);
    }

    public function testFiltersAggregationGetType(): void
    {
        $aggregation = new AdjacencyMatrixAggregation('foo');

        $result = $aggregation->getType();

        self::assertEquals('adjacency_matrix', $result);
    }

    public function testToArray(): void
    {
        $aggregation = new AdjacencyMatrixAggregation('test_agg');
        $filter = $this->createStub(BuilderInterface::class);
        $filter->method('toArray')
            ->willReturn(['test_field' => ['test_value' => 'test']]);

        $aggregation->addFilter('first', $filter);
        $aggregation->addFilter('second', $filter);

        $result = $aggregation->toArray();
        $expected = [
            'adjacency_matrix' => [
                'filters' => [
                    'first' => [
                        'test_field' => ['test_value' => 'test'],
                    ],
                    'second' => [
                        'test_field' => ['test_value' => 'test'],
                    ],
                ],
            ],
        ];

        self::assertEquals($expected, $result);
    }

    public function testFilterConstructor(): void
    {
        $builderInterface1 = $this->createStub(BuilderInterface::class);
        $builderInterface2 = $this->createStub(BuilderInterface::class);

        $aggregation = new AdjacencyMatrixAggregation(
            'test',
            [
                'filter1' => $builderInterface1,
                'filter2' => $builderInterface2,
            ],
        );

        self::assertSame(
            [
                'adjacency_matrix' => [
                    'filters' => [
                        'filter1' => [],
                        'filter2' => [],
                    ],
                ],
            ],
            $aggregation->toArray(),
        );

        $aggregation = new AdjacencyMatrixAggregation('test');

        self::assertSame(
            [
                'adjacency_matrix' => [
                    'filters' => [],
                ],
            ],
            $aggregation->toArray(),
        );
    }

}
