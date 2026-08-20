<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\SearchEndpoint;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PostFilterEndpoint extends QueryEndpoint
{

    public const string NAME = 'post_filter';

    protected function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = []): array|string|int|float|bool
    {
        if ($this->getBool() === null) {
            return false;
        }

        return $this->getBool()->toArray();
    }

    public function getOrder(): int
    {
        return 1;
    }

}
