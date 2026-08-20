<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\SearchEndpoint;

use Biano\ElasticsearchDSL\Aggregation\AbstractAggregation;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function assert;

class AggregationsEndpoint extends AbstractSearchEndpoint
{

    public const string NAME = 'aggregations';

    protected function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = []): array|string|int|float|bool
    {
        $result = [];

        foreach ($this->getAll() as $aggregation) {
            assert($aggregation instanceof AbstractAggregation);
            $result[$aggregation->getName()] = $aggregation->toArray();
        }

        return $result;
    }

}
