<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL;

use Biano\ElasticsearchDSL\Aggregation\AbstractAggregation;
use Biano\ElasticsearchDSL\Highlight\Highlight;
use Biano\ElasticsearchDSL\InnerHit\NestedInnerHit;
use Biano\ElasticsearchDSL\Query\Compound\BoolQuery;
use Biano\ElasticsearchDSL\SearchEndpoint\AbstractSearchEndpoint;
use Biano\ElasticsearchDSL\SearchEndpoint\AggregationsEndpoint;
use Biano\ElasticsearchDSL\SearchEndpoint\HighlightEndpoint;
use Biano\ElasticsearchDSL\SearchEndpoint\InnerHitsEndpoint;
use Biano\ElasticsearchDSL\SearchEndpoint\PostFilterEndpoint;
use Biano\ElasticsearchDSL\SearchEndpoint\QueryEndpoint;
use Biano\ElasticsearchDSL\SearchEndpoint\SearchEndpointFactory;
use Biano\ElasticsearchDSL\SearchEndpoint\SearchEndpointInterface;
use Biano\ElasticsearchDSL\SearchEndpoint\SortEndpoint;
use Biano\ElasticsearchDSL\SearchEndpoint\SuggestEndpoint;
use Biano\ElasticsearchDSL\Serializer\Normalizer\CustomReferencedNormalizer;
use Biano\ElasticsearchDSL\Serializer\OrderedSerializer;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use function array_filter;
use function array_key_exists;
use function assert;
use function in_array;
use function json_encode;
use function sprintf;

/**
 * Search object that can be executed by a manager.
 */
class Search
{

    /**
     * The total number of hits. Defaults to true (10000 hits).
     * You can improve query times by not tracking at all and setting this option to false
     * or defining a number of hits you want to track.
     */
    private bool|int|null $trackTotalHits = null;

    /**
     * To retrieve hits from a certain offset. Defaults to 0.
     */
    private ?int $from = null;

    /**
     * The number of hits to return. Defaults to 10. If you do not care about getting some
     * hits back but only about the number of matches and/or aggregations, setting the value
     * to 0 will help performance.
     */
    private ?int $size = null;

    /**
     * Allows to control how the _source field is returned with every hit. By default
     * operations return the contents of the _source field unless you have used the
     * stored_fields parameter or if the _source field is disabled.
     *
     * @var array<mixed>|bool|string|null
     */
    private array|bool|string|null $source = null;

    /**
     * Allows to selectively load specific stored fields for each document represented by a search hit.
     *
     * @var list<string>|null
     */
    private ?array $storedFields = null;

    /**
     * Allows to return a script evaluation (based on different fields) for each hit.
     * Script fields can work on fields that are not stored, and allow to return custom
     * values to be returned (the evaluated value of the script). Script fields can
     * also access the actual _source document indexed and extract specific elements
     * to be returned from it (can be an "object" type).
     *
     * @var array<mixed>|null
     */
    private ?array $scriptFields = null;

    /**
     * Allows to return a script evaluation (based on different fields) at query time.
     * Compared to script fields, runtime fields can also be used for aggregations.
     * Elasticsearch sees runtime fields no differently like any other field.
     * The script has access to the entire context of a document, including the
     * original _source and any mapped fields plus their values. The script runs only
     * against the top hits just like script fields do.
     *
     * @var array<mixed>|null
     */
    private ?array $runtimeFields = null;

    /**
     * Allows to return the doc value representation of a field for each hit. Doc value
     * fields can work on fields that are not stored. Note that if the fields parameter
     * specifies fields without docvalues it will try to load the value from the fielddata
     * cache causing the terms for that field to be loaded to memory (cached), which will
     * result in more memory consumption.
     *
     * @var array<mixed>|null
     */
    private ?array $docValueFields = null;

    /**
     * Enables explanation for each hit on how its score was computed.
     */
    private ?bool $explain = null;

    /**
     * Returns a version for each search hit.
     */
    private ?bool $version = null;

    /**
     * Allows to configure different boost level per index when searching across more
     * than one indices. This is very handy when hits coming from one index matter more
     * than hits coming from another index (think social graph where each user has an index).
     *
     * @var array<mixed>|null
     */
    private ?array $indicesBoost = null;

    /**
     * Exclude documents which have a _score less than the minimum specified in min_score.
     */
    private ?float $minScore = null;

    /**
     * Pagination of results can be done by using the from and size but the cost becomes
     * prohibitive when the deep pagination is reached. The index.max_result_window which
     * defaults to 10,000 is a safeguard, search requests take heap memory and time
     * proportional to from + size. The Scroll api is recommended for efficient deep
     * scrolling but scroll contexts are costly and it is not recommended to use it for
     * real time user requests. The search_after parameter circumvents this problem by
     * providing a live cursor. The idea is to use the results from the previous page to
     * help the retrieval of the next page.
     *
     * @var array<mixed>|null
     */
    private ?array $searchAfter = null;

    /**
     * URI parameters alongside Request body search.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-uri-request.html
     *
     * @var array<string,mixed>
     */
    private array $uriParams = [];

    /**
     * While a search request returns a single “page” of results, the scroll API can be used to retrieve
     * large numbers of results (or even all results) from a single search request, in much the same way
     * as you would use a cursor on a traditional database. Scrolling is not intended for real time user
     * requests, but rather for processing large amounts of data, e.g. in order to reindex the contents
     * of one index into a new index with a different configuration.
     */
    private ?string $scroll = null;

    private static ?OrderedSerializer $serializer = null;

    /** @var array<string,\Biano\ElasticsearchDSL\SearchEndpoint\SearchEndpointInterface> */
    private array $endpoints = [];

    public function __construct()
    {
        $this->initializeSerializer();
    }

    /**
     * Wakeup method to initialize static properties
     */
    public function __wakeup(): void
    {
        $this->initializeSerializer();
    }

    /**
     * Initializes the serializer
     */
    private function initializeSerializer(): void
    {
        if (self::$serializer !== null) {
            return;
        }

        self::$serializer = new OrderedSerializer(
            [
                new CustomReferencedNormalizer(new CustomNormalizer()),
                new CustomNormalizer(),
            ],
        );
    }

    /**
     * Destroys search endpoint.
     */
    public function destroyEndpoint(string $type): void
    {
        unset($this->endpoints[$type]);
    }

    /**
     * Adds query to the search.
     */
    public function addQuery(BuilderInterface $query, string $boolType = BoolQuery::MUST, ?string $key = null): self
    {
        $endpoint = $this->getEndpoint(QueryEndpoint::NAME);
        $endpoint->addToBool($query, $boolType, $key);

        return $this;
    }

    /**
     * Returns endpoint instance.
     */
    private function getEndpoint(string $type): SearchEndpointInterface
    {
        if (!array_key_exists($type, $this->endpoints)) {
            $this->endpoints[$type] = SearchEndpointFactory::get($type);
        }

        return $this->endpoints[$type];
    }

    /**
     * Returns queries inside BoolQuery instance.
     */
    public function getQueries(): ?BoolQuery
    {
        $endpoint = $this->getEndpoint(QueryEndpoint::NAME);

        return $endpoint->getBool();
    }

    /**
     * Sets query endpoint parameters.
     *
     * @param array<string,mixed> $parameters
     */
    public function setQueryParameters(array $parameters): self
    {
        $this->setEndpointParameters(QueryEndpoint::NAME, $parameters);

        return $this;
    }

    /**
     * Sets parameters to the endpoint.
     *
     * @param array<string,mixed> $parameters
     */
    public function setEndpointParameters(string $endpointName, array $parameters): self
    {
        $endpoint = $this->getEndpoint($endpointName);
        assert($endpoint instanceof AbstractSearchEndpoint);
        $endpoint->setParameters($parameters);

        return $this;
    }

    /**
     * Adds a post filter to search.
     */
    public function addPostFilter(BuilderInterface $filter, string $boolType = BoolQuery::MUST, ?string $key = null): self
    {
        $endpoint = $this->getEndpoint(PostFilterEndpoint::NAME);
        $endpoint->addToBool($filter, $boolType, $key);

        return $this;
    }

    /**
     * Returns queries inside BoolFilter instance.
     */
    public function getPostFilters(): ?BoolQuery
    {
        $endpoint = $this->getEndpoint(PostFilterEndpoint::NAME);

        return $endpoint->getBool();
    }

    /**
     * Sets post filter endpoint parameters.
     *
     * @param array<string,mixed> $parameters
     */
    public function setPostFilterParameters(array $parameters): self
    {
        $this->setEndpointParameters(PostFilterEndpoint::NAME, $parameters);

        return $this;
    }

    /**
     * Adds aggregation into search.
     */
    public function addAggregation(AbstractAggregation $aggregation): self
    {
        $this->getEndpoint(AggregationsEndpoint::NAME)->add($aggregation, $aggregation->getName());

        return $this;
    }

    /**
     * Returns all aggregations.
     *
     * @return array<string,\Biano\ElasticsearchDSL\BuilderInterface>
     */
    public function getAggregations(): array
    {
        return $this->getEndpoint(AggregationsEndpoint::NAME)->getAll();
    }

    /**
     * Adds inner hit into search.
     */
    public function addInnerHit(NestedInnerHit $innerHit): self
    {
        $this->getEndpoint(InnerHitsEndpoint::NAME)->add($innerHit, $innerHit->getName());

        return $this;
    }

    /**
     * Returns all inner hits.
     *
     * @return array<string,\Biano\ElasticsearchDSL\BuilderInterface>
     */
    public function getInnerHits(): array
    {
        return $this->getEndpoint(InnerHitsEndpoint::NAME)->getAll();
    }

    /**
     * Adds sort to search.
     */
    public function addSort(BuilderInterface $sort): self
    {
        $this->getEndpoint(SortEndpoint::NAME)->add($sort);

        return $this;
    }

    /**
     * Returns all set sorts.
     *
     * @return array<string,\Biano\ElasticsearchDSL\BuilderInterface>
     */
    public function getSorts(): array
    {
        return $this->getEndpoint(SortEndpoint::NAME)->getAll();
    }

    /**
     * Allows to highlight search results on one or more fields.
     */
    public function addHighlight(Highlight $highlight): self
    {
        $this->getEndpoint(HighlightEndpoint::NAME)->add($highlight);

        return $this;
    }

    /**
     * Returns highlight builder.
     */
    public function getHighlights(): ?BuilderInterface
    {
        $highlightEndpoint = $this->getEndpoint(HighlightEndpoint::NAME);
        assert($highlightEndpoint instanceof HighlightEndpoint);

        return $highlightEndpoint->getHighlight();
    }

    /**
     * Adds suggest into search.
     */
    public function addSuggest(NamedBuilderInterface $suggest): self
    {
        $this->getEndpoint(SuggestEndpoint::NAME)->add($suggest, $suggest->getName());

        return $this;
    }

    /**
     * Returns all suggests.
     *
     * @return array<string,\Biano\ElasticsearchDSL\BuilderInterface>
     */
    public function getSuggests(): array
    {
        return $this->getEndpoint(SuggestEndpoint::NAME)->getAll();
    }

    public function getFrom(): ?int
    {
        return $this->from;
    }

    public function setFrom(?int $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function isTrackTotalHits(): bool|int|null
    {
        return $this->trackTotalHits;
    }

    public function setTrackTotalHits(bool|int|null $trackTotalHits): self
    {
        $this->trackTotalHits = $trackTotalHits;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return array<mixed>|bool|string|null
     */
    public function getSource(): array|bool|string|null
    {
        return $this->source;
    }

    /**
     * @param  array<mixed>|bool|string $source
     */
    public function setSource(array|bool|string $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return list<string>|null
     */
    public function getStoredFields(): ?array
    {
        return $this->storedFields;
    }

    /**
     * @param list<string> $storedFields
     */
    public function setStoredFields(array $storedFields): self
    {
        $this->storedFields = $storedFields;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getScriptFields(): ?array
    {
        return $this->scriptFields;
    }

    /**
     * @param array<mixed> $scriptFields
     */
    public function setScriptFields(array $scriptFields): self
    {
        $this->scriptFields = $scriptFields;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getRuntimeFields(): ?array
    {
        return $this->runtimeFields;
    }

    /**
     * @param array<mixed> $runtimeFields
     */
    public function setRuntimeFields(array $runtimeFields): self
    {
        $this->runtimeFields = $runtimeFields;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getDocValueFields(): ?array
    {
        return $this->docValueFields;
    }

    /**
     * @param array<mixed> $docValueFields
     */
    public function setDocValueFields(array $docValueFields): self
    {
        $this->docValueFields = $docValueFields;

        return $this;
    }

    public function getExplain(): ?bool
    {
        return $this->explain;
    }

    public function setExplain(bool $explain): self
    {
        $this->explain = $explain;

        return $this;
    }

    public function getVersion(): ?bool
    {
        return $this->version;
    }

    public function setVersion(bool $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return  array<mixed>|null
     */
    public function getIndicesBoost(): ?array
    {
        return $this->indicesBoost;
    }

    /**
     * @param array<mixed> $indicesBoost
     */
    public function setIndicesBoost(array $indicesBoost): self
    {
        $this->indicesBoost = $indicesBoost;

        return $this;
    }

    public function getMinScore(): ?float
    {
        return $this->minScore;
    }

    public function setMinScore(float $minScore): self
    {
        $this->minScore = $minScore;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getSearchAfter(): ?array
    {
        return $this->searchAfter;
    }

    /**
     * @param array<mixed>|null $searchAfter
     */
    public function setSearchAfter(?array $searchAfter): self
    {
        $this->searchAfter = $searchAfter;

        return $this;
    }

    public function getScroll(): ?string
    {
        return $this->scroll;
    }

    public function setScroll(string $scroll = '5m'): self
    {
        $this->scroll = $scroll;

        $this->addUriParam('scroll', $this->scroll);

        return $this;
    }

    /**
     * @param string|array<mixed>|bool $value
     */
    public function addUriParam(string $name, string|array|bool $value): self
    {
        if (
            !in_array($name, [
                'q',
                'df',
                'analyzer',
                'analyze_wildcard',
                'default_operator',
                'lenient',
                'explain',
                '_source',
                '_source_exclude',
                '_source_include',
                'stored_fields',
                'sort',
                'track_scores',
                'timeout',
                'terminate_after',
                'from',
                'size',
                'search_type',
                'scroll',
                'allow_no_indices',
                'ignore_unavailable',
                'typed_keys',
                'pre_filter_shard_size',
                'ignore_unavailable',
                'rest_total_hits_as_int',
            ], true)
        ) {
            throw new InvalidArgumentException(sprintf('Parameter %s is not supported.', json_encode($value)));
        }

        $this->uriParams[$name] = $value;

        return $this;
    }

    /**
     * Returns query url parameters.
     *
     * @return array<string,mixed>
     */
    public function getUriParams(): array
    {
        return $this->uriParams;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $result = array_filter(self::$serializer->normalize($this->endpoints));

        $params = [
            'from' => 'from',
            'size' => 'size',
            'source' => '_source',
            'storedFields' => 'stored_fields',
            'scriptFields' => 'script_fields',
            'runtimeFields' => 'runtime_mappings',
            'docValueFields' => 'docvalue_fields',
            'explain' => 'explain',
            'version' => 'version',
            'indicesBoost' => 'indices_boost',
            'minScore' => 'min_score',
            'searchAfter' => 'search_after',
            'trackTotalHits' => 'track_total_hits',
        ];

        foreach ($params as $field => $param) {
            if ($this->$field === null) {
                continue;
            }

            $result[$param] = $this->$field;
        }

        return $result;
    }

}
