<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Sort;

use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\ParametersTrait;
use stdClass;
use function count;

/**
 * Holds all the values required for basic sorting.
 */
class FieldSort implements BuilderInterface
{

    use ParametersTrait;

    public const string ASC = 'asc';
    public const string DESC = 'desc';

    private string $field;

    /** @var string|array<string,mixed>|null */
    private string|array|null $order = null;

    private ?BuilderInterface $nestedFilter = null;

    /**
     * @param string|array<string,mixed>|null $order
     * @param array<string,mixed> $parameters
     */
    public function __construct(string $field, string|array|null $order = null, array $parameters = [])
    {
        $this->setField($field);

        if ($order !== null) {
            $this->setOrder($order);
        }

        $this->setParameters($parameters);
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return string|array<string,mixed>|null
     */
    public function getOrder(): string|array|null
    {
        return $this->order;
    }

    /**
     * @param string|array<string,mixed> $order
     */
    public function setOrder(string|array $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getNestedFilter(): ?BuilderInterface
    {
        return $this->nestedFilter;
    }

    public function setNestedFilter(BuilderInterface $nestedFilter): self
    {
        $this->nestedFilter = $nestedFilter;

        return $this;
    }

    public function getType(): string
    {
        return 'sort';
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if ($this->getOrder() !== null) {
            $this->addParameter('order', $this->getOrder());
        }

        if ($this->getNestedFilter() !== null) {
            $this->addParameter('nested', $this->getNestedFilter()->toArray());
        }

        return [
            $this->field => count($this->getParameters()) > 0 ? $this->getParameters() : new stdClass(),
        ];
    }

}
