<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\InnerHit;

use Biano\ElasticsearchDSL\NameAwareTrait;
use Biano\ElasticsearchDSL\NamedBuilderInterface;
use Biano\ElasticsearchDSL\ParametersTrait;
use Biano\ElasticsearchDSL\Search;
use stdClass;

/**
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-inner-hits.html
 */
class NestedInnerHit implements NamedBuilderInterface
{

    use ParametersTrait;
    use NameAwareTrait;

    private string $path;

    private ?Search $search = null;

    public function __construct(string $name, string $path, ?Search $search = null)
    {
        $this->setName($name);
        $this->setPath($path);

        if ($search !== null) {
            $this->setSearch($search);
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Returns 'path' for nested and 'type' for parent inner hits
     */
    private function getPathType(): string
    {
        return match ($this->getType()) {
            'parent' => 'type',
            default => 'path',
        };
    }

    public function getSearch(): ?Search
    {
        return $this->search;
    }

    public function setSearch(Search $search): self
    {
        $this->search = $search;

        return $this;
    }

    public function getType(): string
    {
        return 'nested';
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $out = $this->getSearch() ? $this->getSearch()->toArray() : new stdClass();

        $out = [
            $this->getPathType() => [$this->getPath() => $out ],
        ];

        return $out;
    }

}
