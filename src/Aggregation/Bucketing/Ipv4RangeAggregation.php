<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Aggregation\Bucketing;

use LogicException;
use function array_filter;
use function count;
use function is_array;

/**
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-iprange-aggregation.html
 */
class Ipv4RangeAggregation extends AbstractBucketingAggregation
{

    /** @var list<array<string,string>> */
    private array $ranges = [];

    /**
     * @param list<array<string,string|null>|string> $ranges
     */
    public function __construct(string $name, ?string $field = null, array $ranges = [])
    {
        parent::__construct($name);

        if ($field !== null) {
            $this->setField($field);
        }

        foreach ($ranges as $range) {
            if (is_array($range)) {
                $this->addRange($range['from'] ?? null, $range['to'] ?? null);
            } else {
                $this->addMask($range);
            }
        }
    }

    /**
     * @return list<array<string,string>>
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }

    /**
     * Add range to aggregation.
     */
    public function addRange(?string $from = null, ?string $to = null): self
    {
        $range = array_filter(
            [
                'from' => $from,
                'to' => $to,
            ],
            static fn ($v): bool => $v !== null,
        );

        $this->ranges[] = $range;

        return $this;
    }

    /**
     * Add ip mask to aggregation.
     */
    public function addMask(string $mask): self
    {
        $this->ranges[] = ['mask' => $mask];

        return $this;
    }

    public function getType(): string
    {
        return 'ip_range';
    }

    /**
     * @inheritDoc
     */
    public function getArray(): array
    {
        if ($this->getField() === null || count($this->getRanges()) === 0) {
            throw new LogicException('Ip range aggregation must have field set and range added.');
        }

        return [
            'field' => $this->getField(),
            'ranges' => $this->ranges,
        ];
    }

}
