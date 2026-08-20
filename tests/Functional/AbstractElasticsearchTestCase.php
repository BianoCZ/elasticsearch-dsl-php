<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Functional;

use Biano\ElasticsearchDSL\Search;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Http\Promise\Promise;
use PHPUnit\Framework\TestCase;
use Throwable;
use function array_filter;
use function assert;

abstract class AbstractElasticsearchTestCase extends TestCase
{

    /**
     * Test index name in the elasticsearch.
     */
    public const string INDEX_NAME = 'elasticsearch-dsl-test';

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = ClientBuilder::create()->build();
        $this->deleteIndex();

        $this->client->indices()->create(
            array_filter(
                [
                    'index' => self::INDEX_NAME,
                    'mapping' => $this->getMapping(),
                ],
            ),
        );

        $bulkBody = [];

        foreach ($this->getDataArray() as $type => $documents) {
            foreach ($documents as $id => $document) {
                $bulkBody[] = [
                    'index' => [
                        '_index' => self::INDEX_NAME,
                        '_id' => $id,
                    ],
                ];
                $bulkBody[] = $document;
            }
        }

        $this->client->bulk(
            ['body' => $bulkBody],
        );
        $this->client->indices()->refresh();
    }

    /**
     * Defines index mapping for test index.
     * Override this function in your test case and return array with mapping body.
     * More info check here: https://goo.gl/zWBree
     *
     * @return array<string,mixed>
     */
    protected function getMapping(): array
    {
        return [];
    }

    /**
     * Can be overwritten in child class to populate elasticsearch index with the data.
     *
     * Example:
     *      [
     *          'type_name' => [
     *              'custom_id' => [
     *                  'title' => 'foo',
     *              ],
     *              3 => [
     *                  '_id' => 2,
     *                  'title' => 'bar',
     *              ]
     *          ]
     *      ]
     * Document _id can be set as it's id.
     *
     * @return array<string,mixed>
     */
    protected function getDataArray(): array
    {
        return [];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->deleteIndex();
    }

    /**
     * Execute search to the elasticsearch and handle results.
     *
     * @return array<string,mixed>
     *
     * @throws \Elastic\Elasticsearch\Exception\ClientResponseException
     * @throws \Elastic\Elasticsearch\Exception\MissingParameterException
     * @throws \Elastic\Elasticsearch\Exception\ServerResponseException
     */
    protected function executeSearch(Search $search, bool $returnRaw = false): array
    {
        $response = $this->client->search(
            array_filter([
                'index' => self::INDEX_NAME,
                'body' => $search->toArray(),
            ]),
        );
        assert(!$response instanceof Promise);

        if ($returnRaw) {
            return $response->asArray();
        }

        $documents = [];

        try {
            foreach ($response['hits']['hits'] as $document) {
                $documents[$document['_id']] = $document['_source'];
            }
        } catch (Throwable) {
            return $documents;
        }

        return $documents;
    }

    /**
     * Deletes index from elasticsearch.
     */
    private function deleteIndex(): void
    {
        try {
            $this->client->indices()->delete(['index' => self::INDEX_NAME]);
        } catch (Throwable) {
            // Do nothing.
        }
    }

}
