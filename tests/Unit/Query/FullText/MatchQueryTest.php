<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Query\FullText;

use Biano\ElasticsearchDSL\Query\FullText\MatchQuery;
use PHPUnit\Framework\TestCase;

class MatchQueryTest extends TestCase
{

    public function testToArray(): void
    {
        $query = new MatchQuery('message', 'this is a test');
        $expected = [
            'match' => [
                'message' => ['query' => 'this is a test'],
            ],
        ];

        self::assertEquals($expected, $query->toArray());
    }

}
