<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Aggregation\Metric;

use Biano\ElasticsearchDSL\BuilderInterface;
use stdClass;
use function array_filter;

/**
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-top-hits-aggregation.html
 */
class TopHitsAggregation extends AbstractMetricAggregation
{

    /**
     * Number of top matching hits to return per bucket.
     */
    private ?int $size = null;

    /**
     * The offset from the first result you want to fetch.
     */
    private ?int $from = null;

    /**
     * How the top matching hits should be sorted.
     *
     * @var list<\Biano\ElasticsearchDSL\BuilderInterface>
     */
    private array $sorts = [];

    public function __construct(string $name, ?int $size = null, ?int $from = null, ?BuilderInterface $sort = null)
    {
        parent::__construct($name);

        if ($from !== null) {
            $this->setFrom($from);
        }

        if ($size !== null) {
            $this->setSize($size);
        }

        if ($sort !== null) {
            $this->addSort($sort);
        }
    }

    /**
     * Return from.
     */
    public function getFrom(): ?int
    {
        return $this->from;
    }

    public function setFrom(int $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return list<\Biano\ElasticsearchDSL\BuilderInterface>
     */
    public function getSorts(): array
    {
        return $this->sorts;
    }

    /**
     * @param list<\Biano\ElasticsearchDSL\BuilderInterface> $sorts
     */
    public function setSorts(array $sorts): self
    {
        $this->sorts = $sorts;

        return $this;
    }

    /**
     * Add sort.
     */
    public function addSort(BuilderInterface $sort): self
    {
        $this->sorts[] = $sort;

        return $this;
    }

    /**
     * Return size.
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getType(): string
    {
        return 'top_hits';
    }

    public function getArray(): array|stdClass
    {
        $sortsOutput = null;
        foreach ($this->getSorts() as $sort) {
            $sortsOutput[] = $sort->toArray();
        }

        $output = array_filter(
            [
                'sort' => $sortsOutput,
                'size' => $this->getSize(),
                'from' => $this->getFrom(),
            ],
            static fn ($v): bool => $v !== null,
        );

        return empty($output) ? new stdClass() : $output;
    }

}
