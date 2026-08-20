<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\SearchEndpoint;

use Biano\ElasticsearchDSL\InnerHit\NestedInnerHit;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function assert;

class InnerHitsEndpoint extends AbstractSearchEndpoint
{

    public const string NAME = 'inner_hits';

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

        foreach ($this->getAll() as $innerHit) {
            assert($innerHit instanceof NestedInnerHit);
            $result[$innerHit->getName()] = $innerHit->toArray();
        }

        return $result;
    }

}
