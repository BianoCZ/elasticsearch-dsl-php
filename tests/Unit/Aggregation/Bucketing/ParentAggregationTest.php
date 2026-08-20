<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Aggregation\Bucketing;

use Biano\ElasticsearchDSL\Aggregation\AbstractAggregation;
use Biano\ElasticsearchDSL\Aggregation\Bucketing\ParentAggregation;
use LogicException;
use PHPUnit\Framework\TestCase;

class ParentAggregationTest extends TestCase
{

    public function testGetArrayException(): void
    {
        $this->expectException(LogicException::class);

        $aggregation = new ParentAggregation('foo');
        $aggregation->getArray();
    }

    public function testParentAggregationGetType(): void
    {
        $aggregation = new ParentAggregation('foo');

        $result = $aggregation->getType();

        self::assertEquals('parent', $result);
    }

    public function testParentAggregationGetArray(): void
    {
        $mock = $this->createMock(AbstractAggregation::class);
        $mock->setName('name');

        $aggregation = new ParentAggregation('foo');
        $aggregation->addAggregation($mock);
        $aggregation->setParent('answer');

        $result = $aggregation->getArray();
        $expected = ['type' => 'answer'];

        self::assertEquals($expected, $result);
    }

}
