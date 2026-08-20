<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Query\Span;

use Biano\ElasticsearchDSL\Query\Span\SpanOrQuery;
use Biano\ElasticsearchDSL\Query\Span\SpanQueryInterface;
use PHPUnit\Framework\TestCase;

class SpanOrQueryTest extends TestCase
{

    public function testToArray(): void
    {
        $mock = $this->createMock(SpanQueryInterface::class);
        $mock
            ->expects(self::once())
            ->method('toArray')
            ->willReturn(['span_term' => ['key' => 'value']]);

        $query = new SpanOrQuery();
        $query->addQuery($mock);

        $result = $query->toArray();
        $expected = [
            'span_or' => [
                'clauses' => [
                    0 => [
                        'span_term' => ['key' => 'value'],
                    ],
                ],
            ],
        ];

        self::assertEquals($expected, $result);

        $result = $query->getQueries();

        self::assertCount(1, $result);
    }

}
