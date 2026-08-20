<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Query\Compound;

use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\Query\Compound\FunctionScoreQuery;
use Biano\ElasticsearchDSL\Query\MatchAllQuery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class FunctionScoreQueryTest extends TestCase
{

    /**
     * @param array<string,mixed> $expected
     */
    #[DataProvider('providerAddRandomFunction')]
    public function testAddRandomFunction(mixed $seed, array $expected): void
    {
        $matchAllQuery = $this->createStub(MatchAllQuery::class);

        $functionScoreQuery = new FunctionScoreQuery($matchAllQuery);
        $functionScoreQuery->addRandomFunction($seed);

        self::assertEquals(['function_score' => $expected], $functionScoreQuery->toArray());
    }

    /**
     * @return iterable<array<string,mixed>>
     */
    public static function providerAddRandomFunction(): iterable
    {
        // Case #0. No seed.
        yield [
            'seed' => null,
            'expected' => [
                'query' => [],
                'functions' => [
                    [
                        'random_score' => new stdClass(),
                    ],
                ],
            ],
        ];

        // Case #1. With seed.
        yield [
            'seed' => 'someSeed',
            'expected' => [
                'query' => [],
                'functions' => [
                    [
                        'random_score' => ['seed' => 'someSeed'],
                    ],
                ],
            ],
        ];
    }

    public function testAddFieldValueFactorFunction(): void
    {
        $mock = $this->createStub(BuilderInterface::class);
        $functionScoreQuery = new FunctionScoreQuery($mock);
        $functionScoreQuery->addFieldValueFactorFunction('field1', 2);
        $functionScoreQuery->addFieldValueFactorFunction('field2', 1.5, 'ln');

        self::assertEquals(
            [
                'query' => [],
                'functions' => [
                    [
                        'field_value_factor' => [
                            'field' => 'field1',
                            'factor' => 2,
                            'modifier' => 'none',
                        ],
                    ],
                    [
                        'field_value_factor' => [
                            'field' => 'field2',
                            'factor' => 1.5,
                            'modifier' => 'ln',
                        ],
                    ],
                ],
            ],
            $functionScoreQuery->toArray()['function_score'],
        );
    }

}
