<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Query\Joining;

use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\Query\Joining\HasParentQuery;
use PHPUnit\Framework\TestCase;

class HasParentQueryTest extends TestCase
{

    public function testConstructor(): void
    {
        $parentQuery = $this->createStub(BuilderInterface::class);
        $query = new HasParentQuery('test_type', $parentQuery, ['test' => 'test_parameter1']);

        self::assertEquals(['test' => 'test_parameter1'], $query->getParameters());
    }

}
