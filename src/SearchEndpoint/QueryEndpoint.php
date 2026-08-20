<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\SearchEndpoint;

use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\Query\Compound\BoolQuery;
use Biano\ElasticsearchDSL\Serializer\Normalizer\OrderedNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function assert;

/**
 * Search query dsl endpoint.
 */
class QueryEndpoint extends AbstractSearchEndpoint implements OrderedNormalizerInterface
{

    public const string NAME = 'query';

    private ?BoolQuery $bool = null;

    private bool $filtersSet = false;

    protected function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = []): array|string|int|float|bool
    {
        if (!$this->filtersSet && $this->hasReference('filter_query')) {
            $filter = $this->getReference('filter_query');
            assert($filter instanceof BuilderInterface);

            $this->addToBool($filter, BoolQuery::FILTER);
            $this->filtersSet = true;
        }

        if ($this->bool === null) {
            return false;
        }

        return $this->bool->toArray();
    }

    public function add(BuilderInterface $builder, ?string $key = null): string
    {
        return $this->addToBool($builder, BoolQuery::MUST, $key);
    }

    public function addToBool(BuilderInterface $builder, ?string $boolType = null, ?string $key = null): string
    {
        if ($this->bool === null) {
            $this->bool = new BoolQuery();
        }

        return $this->bool->add($builder, $boolType ?? BoolQuery::MUST, $key);
    }

    public function getOrder(): int
    {
        return 2;
    }

    public function getBool(): ?BoolQuery
    {
        return $this->bool;
    }

    /**
     * @inheritDoc
     */
    public function getAll(?string $boolType = null): array
    {
        return $this->bool?->getQueries($boolType) ?? [];
    }

}
