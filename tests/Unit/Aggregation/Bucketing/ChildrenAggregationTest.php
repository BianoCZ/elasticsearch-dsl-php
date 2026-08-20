<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Aggregation\Bucketing;

use Biano\ElasticsearchDSL\Aggregation\AbstractAggregation;
use Biano\ElasticsearchDSL\Aggregation\Bucketing\ChildrenAggregation;
use LogicException;
use PHPUnit\Framework\TestCase;

class ChildrenAggregationTest extends TestCase
{

    public function testGetArrayException(): void
    {
        $this->expectException(LogicException::class);

        $aggregation = new ChildrenAggregation('foo');
        $aggregation->getArray();
    }

    public function testChildrenAggregationGetType(): void
    {
        $aggregation = new ChildrenAggregation('foo');

        $result = $aggregation->getType();

        self::assertEquals('children', $result);
    }

    public function testChildrenAggregationGetArray(): void
    {
        $mock = $this->createStub(AbstractAggregation::class);
        $mock->setName('name');

        $aggregation = new ChildrenAggregation('foo');
        $aggregation->addAggregation($mock);
        $aggregation->setChildren('question');

        $result = $aggregation->getArray();
        $expected = ['type' => 'question'];

        self::assertEquals($expected, $result);
    }

}
