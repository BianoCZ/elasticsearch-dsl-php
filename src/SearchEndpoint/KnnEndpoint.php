<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\SearchEndpoint;

use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\Knn\Knn;
use LogicException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function array_map;
use function array_values;
use function count;

class KnnEndpoint extends AbstractSearchEndpoint
{

    public const string NAME = 'knn';

    protected function getName(): string
    {
        return self::NAME;
    }

    public function add(BuilderInterface $builder, ?string $key = null): string
    {
        if (!$builder instanceof Knn) {
            throw new LogicException('Add Knn builder instead!');
        }

        return parent::add($builder, $key);
    }

    /**
     * @inheritDoc
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = []): array|string|int|float|bool
    {
        $knns = array_values($this->getAll());

        if (count($knns) === 1) {
            return $knns[0]->toArray();
        }

        return array_map(static fn (BuilderInterface $knn): array => $knn->toArray(), $knns);
    }

}
