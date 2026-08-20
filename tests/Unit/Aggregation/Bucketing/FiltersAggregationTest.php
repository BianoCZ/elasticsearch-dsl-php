<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Aggregation\Bucketing;

use Biano\ElasticsearchDSL\Aggregation\Bucketing\FiltersAggregation;
use Biano\ElasticsearchDSL\BuilderInterface;
use LogicException;
use PHPUnit\Framework\TestCase;

class FiltersAggregationTest extends TestCase
{

    public function testIfExceptionIsThrown(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('In not anonymous filters filter name must be set.');

        $mock = $this->createStub(BuilderInterface::class);
        $aggregation = new FiltersAggregation('test_agg');
        $aggregation->addFilter($mock);
    }

    public function testFiltersAggregationGetArray(): void
    {
        $mock = $this->createStub(BuilderInterface::class);
        $aggregation = new FiltersAggregation('test_agg');
        $aggregation->setAnonymous(true);
        $aggregation->addFilter($mock, 'name');

        $result = $aggregation->getArray();

        self::assertArrayHasKey('filters', $result);
    }

    public function testFiltersAggregationGetType(): void
    {
        $aggregation = new FiltersAggregation('foo');

        $result = $aggregation->getType();

        self::assertEquals('filters', $result);
    }

    public function testToArray(): void
    {
        $aggregation = new FiltersAggregation('test_agg');
        $filter = $this->createStub(BuilderInterface::class);
        $filter->method('toArray')->willReturn(['test_field' => ['test_value' => 'test']]);

        $aggregation->addFilter($filter, 'first');
        $aggregation->addFilter($filter, 'second');

        $result = $aggregation->toArray();
        $expected = [
            'filters' => [
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

    public function testConstructorFilter(): void
    {
        $builderInterface1 = $this->createStub(BuilderInterface::class);
        $builderInterface2 = $this->createStub(BuilderInterface::class);

        $aggregation = new FiltersAggregation(
            'test',
            [
                'filter1' => $builderInterface1,
                'filter2' => $builderInterface2,
            ],
        );

        self::assertSame(
            [
                'filters' => [
                    'filters' => [
                        'filter1' => [],
                        'filter2' => [],
                    ],
                ],
            ],
            $aggregation->toArray(),
        );

        $aggregation = new FiltersAggregation(
            'test',
            [
                'filter1' => $builderInterface1,
                'filter2' => $builderInterface2,
            ],
            true,
        );

        self::assertSame(
            [
                'filters' => [
                    'filters' => [
                        [],
                        [],
                    ],
                ],
            ],
            $aggregation->toArray(),
        );
    }

}
