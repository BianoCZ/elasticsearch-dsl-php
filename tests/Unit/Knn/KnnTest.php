<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Knn;

use Biano\ElasticsearchDSL\Knn\Knn;
use Biano\ElasticsearchDSL\Query\MatchAllQuery;
use PHPUnit\Framework\TestCase;
use stdClass;

class KnnTest extends TestCase
{

    public function testGetType(): void
    {
        $knn = new Knn('vector', [1.0, 2.0, 3.0], 10, 100);

        self::assertEquals('knn', $knn->getType());
    }

    public function testToArray(): void
    {
        $knn = new Knn('vector', [1.0, 2.0, 3.0], 10, 100);
        $expected = [
            'field' => 'vector',
            'query_vector' => [1.0, 2.0, 3.0],
            'k' => 10,
            'num_candidates' => 100,
        ];

        self::assertEquals($expected, $knn->toArray());
    }

    public function testToArrayWithSimilarityAndBoost(): void
    {
        $knn = new Knn('vector', [1.0, 2.0, 3.0], 10, 100);
        $knn->setSimilarity(0.5);
        $knn->setBoost(2.0);

        $expected = [
            'field' => 'vector',
            'query_vector' => [1.0, 2.0, 3.0],
            'k' => 10,
            'num_candidates' => 100,
            'similarity' => 0.5,
            'boost' => 2.0,
        ];

        self::assertEquals($expected, $knn->toArray());
    }

    public function testToArrayWithFilter(): void
    {
        $knn = new Knn('vector', [1.0, 2.0, 3.0], 10, 100);
        $knn->setFilter(new MatchAllQuery());

        $expected = [
            'field' => 'vector',
            'query_vector' => [1.0, 2.0, 3.0],
            'k' => 10,
            'num_candidates' => 100,
            'filter' => ['match_all' => new stdClass()],
        ];

        self::assertEquals($expected, $knn->toArray());
    }

}
