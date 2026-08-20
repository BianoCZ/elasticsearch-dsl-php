<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Query\TermLevel;

use Biano\ElasticsearchDSL\Query\TermLevel\TermsSetQuery;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TermsSetQueryTest extends TestCase
{

    public function testToArray(): void
    {
        $terms = ['php', 'c++', 'java'];
        $parameters = ['minimum_should_match_field' => 'required_matches'];
        $query = new TermsSetQuery('programming_languages', $terms, $parameters);
        $expected = [
            'terms_set' => [
                'programming_languages' => [
                    'terms' => ['php', 'c++', 'java'],
                    'minimum_should_match_field' => 'required_matches',
                ],
            ],
        ];

        self::assertEquals($expected, $query->toArray());
    }

    public function testToArrayWithMinimumShouldMatch(): void
    {
        $terms = ['php', 'c++', 'java'];
        $parameters = ['minimum_should_match' => 2];
        $query = new TermsSetQuery('programming_languages', $terms, $parameters);
        $expected = [
            'terms_set' => [
                'programming_languages' => [
                    'terms' => ['php', 'c++', 'java'],
                    'minimum_should_match' => 2,
                ],
            ],
        ];

        self::assertEquals($expected, $query->toArray());
    }

    public function testItThrowsAnExceptionWhenNoMinimumShouldMatchParameterIsGiven(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Either minimum_should_match, minimum_should_match_field or minimum_should_match_script must be set.');

        $terms = ['php', 'c++', 'java'];
        new TermsSetQuery('programming_languages', $terms, []);
    }

}
