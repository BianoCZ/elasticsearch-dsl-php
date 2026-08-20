<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Query\Compound;

use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\Query\Compound\DisMaxQuery;
use PHPUnit\Framework\TestCase;

class DisMaxQueryTest extends TestCase
{

    public function testToArray(): void
    {
        $mock = $this->createStub(BuilderInterface::class);
        $mock->method('toArray')->willReturn(['term' => ['foo' => 'bar']]);

        $query = new DisMaxQuery(['boost' => 1.2]);
        $query->addQuery($mock);
        $query->addQuery($mock);
        $expected = [
            'dis_max' => [
                'queries' => [
                    ['term' => ['foo' => 'bar']],
                    ['term' => ['foo' => 'bar']],
                ],
                'boost' => 1.2,
            ],
        ];

        self::assertEquals($expected, $query->toArray());
    }

}
