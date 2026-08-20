<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Aggregation\Bucketing;

use LogicException;
use function array_filter;
use function array_flip;
use function array_intersect_key;
use function count;
use function is_numeric;

/**
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-histogram-aggregation.html
 */
class HistogramAggregation extends AbstractBucketingAggregation
{

    public const string DIRECTION_ASC = 'asc';
    public const string DIRECTION_DESC = 'desc';

    private ?int $interval = null;

    private ?int $minDocCount = null;

    /** @var array<mixed>|null */
    private ?array $extendedBounds = null;

    private ?string $orderMode = null;

    private ?string $orderDirection = null;

    private ?bool $keyed = null;

    public function __construct(string $name, ?string $field = null, ?int $interval = null, ?int $minDocCount = null, ?string $orderMode = null, string $orderDirection = self::DIRECTION_ASC, ?int $extendedBoundsMin = null, ?int $extendedBoundsMax = null, ?bool $keyed = null)
    {
        parent::__construct($name);

        if ($field !== null) {
            $this->setField($field);
        }

        if ($interval !== null) {
            $this->setInterval($interval);
        }

        if ($minDocCount !== null) {
            $this->setMinDocCount($minDocCount);
        }

        if ($orderMode !== null) {
            $this->setOrder($orderMode, $orderDirection);
        }

        $this->setExtendedBounds($extendedBoundsMin, $extendedBoundsMax);
        if ($keyed !== null) {
            $this->setKeyed($keyed);
        }
    }

    public function isKeyed(): ?bool
    {
        return $this->keyed;
    }

    /**
     * Get response as a hash instead keyed by the buckets keys.
     */
    public function setKeyed(bool $keyed): self
    {
        $this->keyed = $keyed;

        return $this;
    }

    /**
     * Sets buckets ordering.
     */
    public function setOrder(string $mode, string $direction = self::DIRECTION_ASC): self
    {
        $this->orderMode = $mode;
        $this->orderDirection = $direction;

        return $this;
    }

    /**
     * @return array<string,string>|null
     */
    public function getOrder(): ?array
    {
        if ($this->orderMode && $this->orderDirection) {
            return [$this->orderMode => $this->orderDirection];
        }

        return null;
    }

    public function getInterval(): ?int
    {
        return $this->interval;
    }

    public function setInterval(int $interval): self
    {
        $this->interval = $interval;

        return $this;
    }

    public function getMinDocCount(): ?int
    {
        return $this->minDocCount;
    }

    /**
     * Set limit for document count buckets should have.
     */
    public function setMinDocCount(int $minDocCount): self
    {
        $this->minDocCount = $minDocCount;

        return $this;
    }

    /**
     * @return  array<mixed>|null
     */
    public function getExtendedBounds(): ?array
    {
        return $this->extendedBounds;
    }

    public function setExtendedBounds(?int $min = null, ?int $max = null): self
    {
        $bounds = array_filter(
            [
                'min' => $min,
                'max' => $max,
            ],
            static fn ($v): bool => $v !== null,
        );

        $this->extendedBounds = $bounds;

        return $this;
    }

    public function getType(): string
    {
        return 'histogram';
    }

    /**
     * @inheritDoc
     */
    public function getArray(): array
    {
        $out = array_filter(
            [
                'field' => $this->getField(),
                'interval' => $this->getInterval(),
                'min_doc_count' => $this->getMinDocCount(),
                'extended_bounds' => $this->getExtendedBounds(),
                'keyed' => $this->isKeyed(),
                'order' => $this->getOrder(),
            ],
            static function ($val) {
                return $val || is_numeric($val);
            },
        );
        $this->checkRequiredParameters($out, ['field', 'interval']);

        return $out;
    }

    /**
     * Checks if all required parameters are set.
     *
     * @param array<mixed> $data
     * @param list<string> $required
     *
     * @throws \LogicException
     */
    protected function checkRequiredParameters(array $data, array $required): void
    {
        if (count(array_intersect_key(array_flip($required), $data)) !== count($required)) {
            throw new LogicException('Histogram aggregation must have field and interval set.');
        }
    }

}
