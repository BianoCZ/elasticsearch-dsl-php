<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit;

use Biano\ElasticsearchDSL\Knn\Knn;
use Biano\ElasticsearchDSL\Search;
use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{

    public function testItCanBeInstantiated(): void
    {
        self::assertInstanceOf(Search::class, new Search());
    }

    public function testScrollUriParameter(): void
    {
        $search = new Search();
        $search->setScroll('5m');

        self::assertArrayHasKey('scroll', $search->getUriParams());
    }

    public function testCollapse(): void
    {
        $search = new Search();
        $search->setCollapse(['field' => 'user']);

        self::assertEquals(['field' => 'user'], $search->getCollapse());
        self::assertEquals(['collapse' => ['field' => 'user']], $search->toArray());
    }

    public function testAddKnn(): void
    {
        $search = new Search();
        $search->addKnn(new Knn('vector', [1.0, 2.0], 5, 50), 'first');

        self::assertCount(1, $search->getKnns());

        $expected = [
            'knn' => [
                'field' => 'vector',
                'query_vector' => [1.0, 2.0],
                'k' => 5,
                'num_candidates' => 50,
            ],
        ];

        self::assertEquals($expected, $search->toArray());
    }

}
