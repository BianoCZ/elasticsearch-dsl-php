<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Query\TermLevel;

use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\ParametersTrait;
use InvalidArgumentException;

/**
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-set-query.html
 */
class TermsSetQuery implements BuilderInterface
{

    use ParametersTrait;

    public const MINIMUM_SHOULD_MATCH_TYPE = 'minimum_should_match';
    public const MINIMUM_SHOULD_MATCH_TYPE_FIELD = 'minimum_should_match_field';
    public const MINIMUM_SHOULD_MATCH_TYPE_SCRIPT = 'minimum_should_match_script';

    private string $field;

    /** @var list<string> */
    private array $terms;

    /**
     * @param list<string> $terms
     * @param array<string,mixed> $parameters
     */
    public function __construct(string $field, array $terms, array $parameters)
    {
        $this->field = $field;
        $this->terms = $terms;
        $this->validateParameters($parameters);
        $this->setParameters($parameters);
    }

    public function getType(): string
    {
        return 'terms_set';
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $query = [
            'terms' => $this->terms,
        ];

        return [
            $this->getType() => [
                $this->field => $this->processArray($query),
            ],
        ];
    }

    /**
     * @param array<string,mixed> $parameters
     */
    private function validateParameters(array $parameters): void
    {
        if (
            !isset($parameters[self::MINIMUM_SHOULD_MATCH_TYPE]) &&
            !isset($parameters[self::MINIMUM_SHOULD_MATCH_TYPE_FIELD]) &&
            !isset($parameters[self::MINIMUM_SHOULD_MATCH_TYPE_SCRIPT])
        ) {
            $message = 'Either minimum_should_match, minimum_should_match_field or minimum_should_match_script must be set.';

            throw new InvalidArgumentException($message);
        }
    }

}
