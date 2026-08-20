<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Aggregation\Bucketing;

use LogicException;
use function array_filter;
use function count;

/**
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-daterange-aggregation.html
 */
class DateRangeAggregation extends AbstractBucketingAggregation
{

    private ?string $format = null;

    /** @var list<array<string,mixed>> */
    private array $ranges = [];

    private bool $keyed = false;

    /**
     * @param list<array<string,mixed>>$ranges
     */
    public function __construct(string $name, ?string $field = null, ?string $format = null, array $ranges = [], bool $keyed = false)
    {
        parent::__construct($name);

        if ($field !== null) {
            $this->setField($field);
        }

        if ($format !== null) {
            $this->setFormat($format);
        }

        $this->setKeyed($keyed);

        foreach ($ranges as $range) {
            $this->addRange($range['from'] ?? null, $range['to'] ?? null, $range['key'] ?? null);
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

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }

    public function addRange(int|float|string|null $from = null, int|float|string|null $to = null, ?string $key = null): self
    {
        $range = array_filter(
            [
                'from' => $from,
                'to' => $to,
                'key' => $key,
            ],
            static fn ($v) => $v !== null,
        );

        if (empty($range)) {
            throw new LogicException('Either from or to must be set. Both cannot be null.');
        }

        $this->ranges[] = $range;

        return $this;
    }

    public function getType(): string
    {
        return 'date_range';
    }

    /**
     * @inheritDoc
     */
    public function getArray(): array
    {
        if ($this->getField() === null || $this->getFormat()  === null || count($this->getRanges()) === 0) {
            throw new LogicException('Date range aggregation must have field, format set and range added.');
        }

        return [
            'format' => $this->getFormat(),
            'field' => $this->getField(),
            'ranges' => $this->getRanges(),
            'keyed' => $this->isKeyed(),
        ];
    }

}
