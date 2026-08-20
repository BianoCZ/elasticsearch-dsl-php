<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\SearchEndpoint;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SortEndpoint extends AbstractSearchEndpoint
{

    public const string NAME = 'sort';

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

        foreach ($this->getAll() as $sort) {
            $result[] = $sort->toArray();
        }

        return $result;
    }

}
