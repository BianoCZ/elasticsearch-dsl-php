<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL;

use function array_filter;
use function array_merge;
use function array_values;
use function bin2hex;
use function random_bytes;

/**
 * Container for named builders.
 *
 * @template T of \Biano\ElasticsearchDSL\BuilderInterface
 */
class BuilderBag
{

    /** @var array<string,T> */
    private array $bag = [];

    /**
     * @param list<T> $builders
     */
    public function __construct(array $builders = [])
    {
        foreach ($builders as $builder) {
            $this->add($builder);
        }
    }

    /**
     * Adds a builder.
     *
     * @param T $builder
     */
    public function add(BuilderInterface $builder): string
    {
        if ($builder instanceof NamedBuilderInterface) {
            $name = $builder->getName();
        } else {
            $name = bin2hex(random_bytes(30));
        }

        $this->bag[$name] = $builder;

        return $name;
    }

    /**
     * Checks if builder exists by a specific name.
     */
    public function has(string $name): bool
    {
        return isset($this->bag[$name]);
    }

    /**
     * Removes a builder by name.
     */
    public function remove(string $name): void
    {
        unset($this->bag[$name]);
    }

    /**
     * Clears contained builders.
     */
    public function clear(): void
    {
        $this->bag = [];
    }

    /**
     * Returns a builder by name.
     *
     * @return T
     */
    public function get(string $name): BuilderInterface
    {
        return $this->bag[$name];
    }

    /**
     * Returns all builders contained.
     *
     * @return list<T>
     */
    public function all(?string $type = null): array
    {
        return array_values(array_filter(
            $this->bag,
            static fn (BuilderInterface $builder): bool => $type === null || $builder->getType() === $type,
        ));
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this->all() as $builder) {
            $data = array_merge($data, $builder->toArray());
        }

        return $data;
    }

}
