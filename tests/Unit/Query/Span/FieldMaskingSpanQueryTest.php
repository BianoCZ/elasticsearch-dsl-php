<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Query\Span;

use Biano\ElasticsearchDSL\Query\Span\FieldMaskingSpanQuery;
use Biano\ElasticsearchDSL\Query\Span\SpanNearQuery;
use Biano\ElasticsearchDSL\Query\Span\SpanTermQuery;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for FieldMaskingSpanQuery.
 */
class FieldMaskingSpanQueryTest extends TestCase
{

    /**
     * Tests for toArray().
     */
    public function testToArray(): void
    {
        $spanTermQuery = new SpanTermQuery('text', 'quick brown');

        $spanTermQueryForMask = new SpanTermQuery('text.stems', 'fox');
        $fieldMaskingSpanQuery = new FieldMaskingSpanQuery('text', $spanTermQueryForMask);

        $spanNearQuery = new SpanNearQuery();
        $spanNearQuery->addQuery($spanTermQuery);
        $spanNearQuery->addQuery($fieldMaskingSpanQuery);
        $spanNearQuery->setSlop(5);
        $spanNearQuery->addParameter('in_order', false);

        $result = [
            'span_near' => [
                'clauses' => [
                    [
                        'span_term' => [ 'text' => 'quick brown'],
                    ],
                    [
                        'field_masking_span' => [
                            'query' => [ 'span_term' => [ 'text.stems' => 'fox' ] ],
                            'field' => 'text',
                        ],
                    ],
                ],
                'slop' => 5,
                'in_order' => false,
            ],
        ];

        $this->assertEquals($result, $spanNearQuery->toArray());
    }

}