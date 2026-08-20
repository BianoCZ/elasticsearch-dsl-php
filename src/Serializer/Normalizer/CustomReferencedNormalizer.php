<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Serializer\Normalizer;

use ArrayObject;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use function array_merge;
use function assert;

/**
 * Normalizer used with referenced normalized objects.
 */
class CustomReferencedNormalizer implements NormalizerInterface, SerializerAwareInterface
{

    /** @var array<string,mixed> */
    private array $references = [];

    public function __construct(
        private CustomNormalizer $customNormalizer,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function normalize(mixed $object, $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        assert($object instanceof AbstractNormalizable);
        $object->setReferences($this->references);
        $data = $this->customNormalizer->normalize($object, $format, $context);
        $this->references = array_merge($this->references, $object->getReferences());

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->customNormalizer->supportsNormalization($data, $format);
    }

    /**
     * @inheritDoc
     */
    public function getSupportedTypes(?string $format): array
    {
        return $this->customNormalizer->getSupportedTypes($format);
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->customNormalizer->setSerializer($serializer);
    }

}
