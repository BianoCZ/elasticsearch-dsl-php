<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Serializer;

use ArrayObject;
use Biano\ElasticsearchDSL\Serializer\Normalizer\OrderedNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use function array_diff_key;
use function array_filter;
use function array_merge;
use function is_array;
use function uasort;

/**
 * Custom serializer which orders data before normalization.
 */
class OrderedSerializer implements NormalizerInterface, DenormalizerInterface
{

    private Serializer $serializer;

    /**
     * @param list<\Symfony\Component\Serializer\Normalizer\NormalizerInterface|\Symfony\Component\Serializer\Normalizer\DenormalizerInterface> $normalizers
     */
    public function __construct(array $normalizers = [])
    {
        $this->serializer = new Serializer($normalizers);
    }

    /**
     * @inheritDoc
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        return $this->serializer->normalize(
            is_array($data) ? $this->order($data) : $data,
            $format,
            $context,
        );
    }

    /**
     * @inheritDoc
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return $this->serializer->denormalize(
            is_array($data) ? $this->order($data) : $data,
            $type,
            $format,
            $context,
        );
    }

    /**
     * Orders objects if it can be done.
     *
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private function order(array $data): array
    {
        $filteredData = $this->filterOrderable($data);

        if (!empty($filteredData)) {
            uasort(
                $filteredData,
                static function (OrderedNormalizerInterface $a, OrderedNormalizerInterface $b) {
                    return $a->getOrder() > $b->getOrder() ? 1 : -1;
                },
            );

            return array_merge($filteredData, array_diff_key($data, $filteredData));
        }

        return $data;
    }

    /**
     * Filters out data which can be ordered.
     *
     * @param array<mixed> $array
     *
     * @return array<mixed>
     */
    private function filterOrderable(array $array): array
    {
        return array_filter(
            $array,
            static fn ($value): bool => $value instanceof OrderedNormalizerInterface,
        );
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $this->serializer->supportsDenormalization($data, $type, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->serializer->supportsNormalization($data, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->serializer->getSupportedTypes($format);
    }

}
