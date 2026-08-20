<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Query\Compound;

use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\ParametersTrait;
use UnexpectedValueException;
use stdClass;
use function array_merge;
use function array_walk;
use function bin2hex;
use function count;
use function in_array;
use function is_array;
use function random_bytes;
use function reset;
use function sprintf;

/**
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
 */
class BoolQuery implements BuilderInterface
{

    use ParametersTrait;

    public const MUST = 'must';
    public const MUST_NOT = 'must_not';
    public const SHOULD = 'should';
    public const FILTER = 'filter';

    /** @var array<string,array<string,\Biano\ElasticsearchDSL\BuilderInterface>> */
    private array $container = [];

    /**
     * @param array<string,list<\Biano\ElasticsearchDSL\BuilderInterface>|\Biano\ElasticsearchDSL\BuilderInterface> $container
     */
    public function __construct(array $container = [])
    {
        foreach ($container as $type => $queries) {
            $queries = is_array($queries) ? $queries : [$queries];

            array_walk($queries, function ($query) use ($type): void {
                $this->add($query, $type);
            });
        }
    }

    /**
     * Returns the query instances (by bool type).
     *
     * @return array<string,\Biano\ElasticsearchDSL\BuilderInterface>
     */
    public function getQueries(?string $boolType = null): array
    {
        if ($boolType === null) {
            $queries = [];

            foreach ($this->container as $item) {
                $queries = array_merge($queries, $item);
            }

            return $queries;
        }

        if (isset($this->container[$boolType])) {
            return $this->container[$boolType];
        }

        return [];
    }

    public function add(BuilderInterface $query, string $type = self::MUST, ?string $key = null): string
    {
        if (!in_array($type, [self::MUST, self::MUST_NOT, self::SHOULD, self::FILTER])) {
            throw new UnexpectedValueException(sprintf('The bool operator %s is not supported', $type));
        }

        if (!$key) {
            $key = bin2hex(random_bytes(30));
        }

        $this->container[$type][$key] = $query;

        return $key;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (count($this->container) === 1 && isset($this->container[self::MUST]) && count($this->container[self::MUST]) === 1) {
            $query = reset($this->container[self::MUST]);

            return $query->toArray();
        }

        $output = [];

        foreach ($this->container as $boolType => $builders) {
            foreach ($builders as $builder) {
                $output[$boolType][] = $builder->toArray();
            }
        }

        $output = $this->processArray($output);

        if (empty($output)) {
            $output = new stdClass();
        }

        return [$this->getType() => $output];
    }

    public function getType(): string
    {
        return 'bool';
    }

}
