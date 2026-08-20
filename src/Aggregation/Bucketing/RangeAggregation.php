<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Aggregation\Bucketing;

use function array_diff_assoc;
use function array_filter;
use function array_key_exists;
use function array_values;

/**
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-range-aggregation.html
 */
class RangeAggregation extends AbstractBucketingAggregation
{

    /** @var list<array<string,int|float|string>> */
    private array $ranges = [];

    private bool $keyed = false;

    /**
     * @param list<array<string,int|float|string|null>> $ranges
     */
    public function __construct(string $name, ?string $field = null, array $ranges = [], bool $keyed = false)
    {
        parent::__construct($name);

        if ($field !== null) {
            $this->setField($field);
        }

        $this->setKeyed($keyed);

        foreach ($ranges as $range) {
            $this->addRange($range['from'] ?? null, $range['to'] ?? null, ($range['key'] ?? null) !== null ? (string) $range['key'] : null);
        }
    }

    public function isKeyed(): bool
    {
        return $this->keyed;
    }

    /**
     * Sets if result buckets should be keyed.
     */
    public function setKeyed(bool $keyed): self
    {
        $this->keyed = $keyed;

        return $this;
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }

    /**
     * Add range to aggregation.
     */
    public function addRange(int|float|string|null $from = null, int|float|string|null $to = null, ?string $key = null): self
    {
        $range = array_filter(
            [
                'from' => $from,
                'to' => $to,
                'key' => $key,
            ],
            static fn ($v): bool => $v !== null,
        );

        $this->ranges[] = $range;

        return $this;
    }

    /**
     * Remove range from aggregation. Returns true on success.
     */
    public function removeRange(int|float|null $from, int|float|null $to): bool
    {
        foreach ($this->ranges as $key => $range) {
            if (array_diff_assoc(array_filter(['from' => $from, 'to' => $to]), $range) === []) {
                $ranges = $this->ranges;
                unset($ranges[$key]);
                $this->ranges = array_values($ranges);

                return true;
            }
        }

        return false;
    }

    /**
     * Removes range by key.
     */
    public function removeRangeByKey(string $key): bool
    {
        if ($this->keyed) {
            foreach ($this->ranges as $rangeKey => $range) {
                if (array_key_exists('key', $range) && $range['key'] === $key) {
                    $ranges = $this->ranges;
                    unset($ranges[$rangeKey]);
                    $this->ranges = array_values($ranges);

                    return true;
                }
            }
        }

        return false;
    }

    public function getType(): string
    {
        return 'range';
    }

    /**
     * @inheritDoc
     */
    public function getArray(): array
    {
        return array_filter(
            [
                'keyed' => $this->isKeyed(),
                'ranges' => $this->getRanges(),
                'field' =>  $this->getField(),
            ],
            static fn ($v): bool => $v !== null,
        );
    }

}
