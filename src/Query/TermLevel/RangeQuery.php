<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Query\TermLevel;

use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\ParametersTrait;

/**
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html
 */
class RangeQuery implements BuilderInterface
{

    use ParametersTrait;

    /**
     * Range control names.
     */
    public const string LT = 'lt';
    public const string GT = 'gt';
    public const string LTE = 'lte';
    public const string GTE = 'gte';

    private string $field;

    /**
     * @param array<string,mixed> $parameters
     */
    public function __construct(string $field, array $parameters = [])
    {
        $this->setParameters($parameters);

        if ($this->hasParameter(self::GTE) && $this->hasParameter(self::GT)) {
            unset($this->parameters[self::GT]);
        }

        if ($this->hasParameter(self::LTE) && $this->hasParameter(self::LT)) {
            unset($this->parameters[self::LT]);
        }

        $this->field = $field;
    }

    public function getType(): string
    {
        return 'range';
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $output = [
            $this->field => $this->getParameters(),
        ];

        return [$this->getType() => $output];
    }

}
