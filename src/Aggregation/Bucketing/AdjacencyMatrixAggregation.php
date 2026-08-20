<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Aggregation\Bucketing;

use Biano\ElasticsearchDSL\BuilderInterface;

/**
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-adjacency-matrix-aggregation.html
 */
class AdjacencyMatrixAggregation extends AbstractBucketingAggregation
{

    private const string FILTERS = 'filters';

    /** @var array<string,array<string,array<mixed>>> */
    private array $filters = [
        self::FILTERS => [],
    ];

    /**
     * @param array<string,\Biano\ElasticsearchDSL\BuilderInterface> $filters
     */
    public function __construct(string $name, array $filters = [])
    {
        parent::__construct($name);

        foreach ($filters as $name => $filter) {
            $this->addFilter($name, $filter);
        }
    }

    /**
     * @return array<string,array<string,array<mixed>>>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function addFilter(string $name, BuilderInterface $filter): self
    {
        $this->filters[self::FILTERS][$name] = $filter->toArray();

        return $this;
    }

    public function getType(): string
    {
        return 'adjacency_matrix';
    }

    /**
     * @inheritDoc
     */
    public function getArray(): array
    {
        return $this->getFilters();
    }

}
