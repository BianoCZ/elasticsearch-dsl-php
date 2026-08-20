<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Aggregation;

use Biano\ElasticsearchDSL\BuilderBag;
use Biano\ElasticsearchDSL\NameAwareTrait;
use Biano\ElasticsearchDSL\NamedBuilderInterface;
use Biano\ElasticsearchDSL\ParametersTrait;
use stdClass;
use function count;
use function is_array;

abstract class AbstractAggregation implements NamedBuilderInterface
{

    use ParametersTrait;
    use NameAwareTrait;

    private ?string $field = null;

    /**
     * @var \Biano\ElasticsearchDSL\BuilderBag<\Biano\ElasticsearchDSL\Aggregation\AbstractAggregation>|null
     */
    private ?BuilderBag $aggregations = null;

    abstract protected function supportsNesting(): bool;

    /**
     * @return array<mixed>|\stdClass
     */
    abstract protected function getArray(): array|stdClass;

    public function __construct(string $name)
    {
        $this->setName($name);
    }

    public function setField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * Adds a sub-aggregation.
     */
    public function addAggregation(AbstractAggregation $abstractAggregation): self
    {
        if ($this->aggregations === null) {
            $this->aggregations = $this->createBuilderBag();
        }

        $this->aggregations->add($abstractAggregation);

        return $this;
    }

    /**
     * Returns all sub aggregations.
     *
     * @return list<\Biano\ElasticsearchDSL\Aggregation\AbstractAggregation>
     */
    public function getAggregations(): array
    {
        return $this->aggregations?->all() ?? [];
    }

    /**
     * Returns sub aggregation.
     */
    public function getAggregation(string $name): ?AbstractAggregation
    {
        if ($this->aggregations?->has($name) ?? false) {
            return $this->aggregations->get($name);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = $this->getArray();

        $result = [
            $this->getType() => is_array($data) ? $this->processArray($data) : $data,
        ];

        if ($this->supportsNesting()) {
            $nestedResult = $this->collectNestedAggregations();

            if (count($nestedResult) > 0) {
                $result['aggregations'] = $nestedResult;
            }
        }

        return $result;
    }

    /**
     * Process all nested aggregations.
     *
     * @return array<string,array<mixed>>
     */
    protected function collectNestedAggregations(): array
    {
        $result = [];

        foreach ($this->getAggregations() as $aggregation) {
            $result[$aggregation->getName()] = $aggregation->toArray();
        }

        return $result;
    }

    /**
     * @return \Biano\ElasticsearchDSL\BuilderBag<\Biano\ElasticsearchDSL\Aggregation\AbstractAggregation>
     */
    private function createBuilderBag(): BuilderBag
    {
        return new BuilderBag();
    }

}
