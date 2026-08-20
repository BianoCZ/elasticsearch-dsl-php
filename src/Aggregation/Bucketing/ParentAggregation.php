<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Aggregation\Bucketing;

use LogicException;
use function array_filter;
use function count;
use function sprintf;

/**
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-parent-aggregation.html
 */
class ParentAggregation extends AbstractBucketingAggregation
{

    private ?string $parent = null;

    public function __construct(string $name, ?string $parent = null)
    {
        parent::__construct($name);

        if ($parent !== null) {
            $this->setParent($parent);
        }
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function setParent(string $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getType(): string
    {
        return 'parent';
    }

    /**
     * @inheritDoc
     */
    public function getArray(): array
    {
        if (count($this->getAggregations()) === 0) {
            throw new LogicException(sprintf('Parent aggregation `%s` has no aggregations added', $this->getName()));
        }

        return array_filter([
            'type' => $this->getParent(),
        ]);
    }

}
