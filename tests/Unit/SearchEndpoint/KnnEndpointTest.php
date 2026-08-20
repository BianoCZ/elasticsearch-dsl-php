<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\SearchEndpoint;

use Biano\ElasticsearchDSL\Knn\Knn;
use Biano\ElasticsearchDSL\Query\MatchAllQuery;
use Biano\ElasticsearchDSL\SearchEndpoint\KnnEndpoint;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class KnnEndpointTest extends TestCase
{

    public function testItThrowsAnExceptionWhenBuilderIsNotKnn(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Add Knn builder instead!');

        (new KnnEndpoint())->add(new MatchAllQuery());
    }

    public function testNormalizeEmpty(): void
    {
        $endpoint = new KnnEndpoint();

        self::assertEquals([], $endpoint->normalize($this->createMock(NormalizerInterface::class)));
    }

    public function testNormalizeSingleKnnIsNotWrappedInAList(): void
    {
        $endpoint = new KnnEndpoint();
        $endpoint->add(new Knn('vector', [1.0, 2.0], 5, 50));

        $expected = [
            'field' => 'vector',
            'query_vector' => [1.0, 2.0],
            'k' => 5,
            'num_candidates' => 50,
        ];

        self::assertEquals($expected, $endpoint->normalize($this->createMock(NormalizerInterface::class)));
    }

    public function testNormalizeMultipleKnns(): void
    {
        $endpoint = new KnnEndpoint();
        $endpoint->add(new Knn('vector', [1.0, 2.0], 5, 50), 'first');
        $endpoint->add(new Knn('other_vector', [3.0, 4.0], 6, 60), 'second');

        $expected = [
            [
                'field' => 'vector',
                'query_vector' => [1.0, 2.0],
                'k' => 5,
                'num_candidates' => 50,
            ],
            [
                'field' => 'other_vector',
                'query_vector' => [3.0, 4.0],
                'k' => 6,
                'num_candidates' => 60,
            ],
        ];

        self::assertEquals($expected, $endpoint->normalize($this->createMock(NormalizerInterface::class)));
    }

}
