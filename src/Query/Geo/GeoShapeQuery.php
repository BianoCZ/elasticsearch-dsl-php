<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Query\Geo;

use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\ParametersTrait;
use function array_merge;

/**
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html
 */
class GeoShapeQuery implements BuilderInterface
{

    use ParametersTrait;

    public const string INTERSECTS = 'intersects';
    public const string DISJOINT = 'disjoint';
    public const string WITHIN = 'within';
    public const string CONTAINS = 'contains';

    /** @var array<string,array<string,mixed>> */
    private array $fields = [];

    /**
     * @param array<string,mixed> $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->setParameters($parameters);
    }

    public function getType(): string
    {
        return 'geo_shape';
    }

    /**
     * Add geo-shape provided filter.
     *
     * @param array<mixed> $coordinates
     * @param array<string,mixed> $parameters
     */
    public function addShape(string $field, string $type, array $coordinates, string $relation = self::INTERSECTS, array $parameters = []): void
    {
        $filter = array_merge(
            $parameters,
            [
                'type' => $type,
                'coordinates' => $coordinates,
            ],
        );

        $this->fields[$field] = [
            'shape' => $filter,
            'relation' => $relation,
        ];
    }

    /**
     * Add geo-shape pre-indexed filter.
     *
     * @param array<string,mixed> $parameters
     */
    public function addPreIndexedShape(string $field, string $id, string $type, string $index, string $path, string $relation = self::INTERSECTS, array $parameters = []): void
    {
        $filter = array_merge(
            $parameters,
            [
                'id' => $id,
                'type' => $type,
                'index' => $index,
                'path' => $path,
            ],
        );

        $this->fields[$field] = [
            'indexed_shape' => $filter,
            'relation' => $relation,
        ];
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $output = $this->processArray($this->fields);

        return [$this->getType() => $output];
    }

}
