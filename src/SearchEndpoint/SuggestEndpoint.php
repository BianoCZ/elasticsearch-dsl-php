<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\SearchEndpoint;

use Biano\ElasticsearchDSL\Suggest\Suggest;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function array_merge;
use function assert;

class SuggestEndpoint extends AbstractSearchEndpoint
{

    public const string NAME = 'suggest';

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

        foreach ($this->getAll() as $suggest) {
            assert($suggest instanceof Suggest);
            $result = array_merge($result, $suggest->toArray());
        }

        return $result;
    }

}
