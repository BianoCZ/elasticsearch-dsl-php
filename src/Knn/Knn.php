<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Knn;

use Biano\ElasticsearchDSL\BuilderInterface;
use function array_filter;

/**
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/knn-search.html
 */
class Knn implements BuilderInterface
{

    private string $field;

    /** @var list<float> */
    private array $queryVector;

    private int $k;

    private int $numCandidates;

    private ?float $similarity = null;

    private ?float $boost = null;

    private ?BuilderInterface $filter = null;

    /**
     * @param list<float> $queryVector
     */
    public function __construct(string $field, array $queryVector, int $k, int $numCandidates)
    {
        $this->field = $field;
        $this->queryVector = $queryVector;
        $this->k = $k;
        $this->numCandidates = $numCandidates;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return list<float>
     */
    public function getQueryVector(): array
    {
        return $this->queryVector;
    }

    /**
     * @param list<float> $queryVector
     */
    public function setQueryVector(array $queryVector): self
    {
        $this->queryVector = $queryVector;

        return $this;
    }

    public function getK(): int
    {
        return $this->k;
    }

    public function setK(int $k): self
    {
        $this->k = $k;

        return $this;
    }

    public function getNumCandidates(): int
    {
        return $this->numCandidates;
    }

    public function setNumCandidates(int $numCandidates): self
    {
        $this->numCandidates = $numCandidates;

        return $this;
    }

    public function getSimilarity(): ?float
    {
        return $this->similarity;
    }

    public function setSimilarity(?float $similarity): self
    {
        $this->similarity = $similarity;

        return $this;
    }

    public function getBoost(): ?float
    {
        return $this->boost;
    }

    public function setBoost(?float $boost): self
    {
        $this->boost = $boost;

        return $this;
    }

    public function getFilter(): ?BuilderInterface
    {
        return $this->filter;
    }

    public function setFilter(?BuilderInterface $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function getType(): string
    {
        return 'knn';
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_filter([
            'field' => $this->getField(),
            'query_vector' => $this->getQueryVector(),
            'k' => $this->getK(),
            'num_candidates' => $this->getNumCandidates(),
            'similarity' => $this->getSimilarity(),
            'boost' => $this->getBoost(),
            'filter' => $this->getFilter()?->toArray(),
        ], static fn ($value): bool => $value !== null);
    }

}
