<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Query\Joining;

use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\Query\Joining\HasChildQuery;
use PHPUnit\Framework\TestCase;

class HasChildQueryTest extends TestCase
{

    public function testConstructor(): void
    {
        $parentQuery = $this->createStub(BuilderInterface::class);
        $query = new HasChildQuery('test_type', $parentQuery, ['test' => 'test_parameter1']);

        self::assertEquals(['test' => 'test_parameter1'], $query->getParameters());
    }

}
