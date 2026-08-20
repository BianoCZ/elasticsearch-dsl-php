<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Query\Compound;

use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\Query\Compound\BoostingQuery;
use PHPUnit\Framework\TestCase;

class BoostingQueryTest extends TestCase
{

    public function testToArray(): void
    {
        $mock = $this->createStub(BuilderInterface::class);
        $mock->method('toArray')->willReturn(['term' => ['foo' => 'bar']]);

        $query = new BoostingQuery($mock, $mock, 0.2);
        $expected = [
            'boosting' => [
                'positive' => ['term' => ['foo' => 'bar']],
                'negative' => ['term' => ['foo' => 'bar']],
                'negative_boost' => 0.2,
            ],
        ];

        self::assertEquals($expected, $query->toArray());
    }

}
