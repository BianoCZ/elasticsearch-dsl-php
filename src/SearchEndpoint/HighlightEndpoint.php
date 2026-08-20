<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\SearchEndpoint;

use Biano\ElasticsearchDSL\BuilderInterface;
use OverflowException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Search highlight dsl endpoint.
 */
class HighlightEndpoint extends AbstractSearchEndpoint
{

    public const string NAME = 'highlight';

    private ?BuilderInterface $highlight = null;

    /**
     * Key for highlight storing.
     */
    private string $key = '';

    protected function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = []): array|string|int|float|bool
    {
        if ($this->highlight !== null) {
            return $this->highlight->toArray();
        }

        return false;
    }

    public function add(BuilderInterface $builder, ?string $key = null): string
    {
        if ($this->highlight !== null) {
            throw new OverflowException('Only one highlight can be set');
        }

        $this->key = $key ?? '';
        $this->highlight = $builder;

        return $this->key;
    }

    public function getHighlight(): ?BuilderInterface
    {
        return $this->highlight;
    }

    /**
     * @inheritDoc
     */
    public function getAll(?string $boolType = null): array
    {
        if ($this->highlight === null) {
            return [];
        }

        return [$this->key => $this->highlight];
    }

}
