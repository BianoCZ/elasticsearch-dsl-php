<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Query\Compound;

use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\Query\Compound\ConstantScoreQuery;
use PHPUnit\Framework\TestCase;

class ConstantScoreQueryTest extends TestCase
{

    public function testToArray(): void
    {
        $mock = $this->createStub(BuilderInterface::class);
        $mock->method('toArray')->willReturn(['term' => ['foo' => 'bar']]);

        $query = new ConstantScoreQuery($mock, ['boost' => 1.2]);
        $expected = [
            'constant_score' => [
                'filter' => [
                    'term' => ['foo' => 'bar'],
                ],
                'boost' => 1.2,
            ],
        ];

        self::assertEquals($expected, $query->toArray());
    }

}
