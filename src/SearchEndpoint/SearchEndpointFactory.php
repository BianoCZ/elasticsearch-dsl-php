<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\SearchEndpoint;

use RuntimeException;
use function array_key_exists;

class SearchEndpointFactory
{

    /**
     * Holds namespaces for endpoints.
     *
     * @var array<string,class-string<\Biano\ElasticsearchDSL\SearchEndpoint\SearchEndpointInterface>>
     */
    private static array $endpoints = [
        'query' => QueryEndpoint::class,
        'knn' => KnnEndpoint::class,
        'post_filter' => PostFilterEndpoint::class,
        'sort' => SortEndpoint::class,
        'highlight' => HighlightEndpoint::class,
        'aggregations' => AggregationsEndpoint::class,
        'suggest' => SuggestEndpoint::class,
        'inner_hits' => InnerHitsEndpoint::class,
    ];

    /**
     * Returns a search endpoint instance.
     *
     * @throws \RuntimeException Endpoint does not exist.
     */
    public static function get(string $type): SearchEndpointInterface
    {
        if (!array_key_exists($type, self::$endpoints)) {
            throw new RuntimeException('Endpoint does not exist.');
        }

        return new self::$endpoints[$type]();
    }

}
