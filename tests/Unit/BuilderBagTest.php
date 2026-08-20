<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit;

use Biano\ElasticsearchDSL\BuilderBag;
use Biano\ElasticsearchDSL\BuilderInterface;
use Biano\ElasticsearchDSL\NamedBuilderInterface;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class BuilderBagTest extends TestCase
{

    public function testHas(): void
    {
        $bag = new BuilderBag();
        $fooBuilder = $this->getBuilder('foo');
        $builderName = $bag->add($fooBuilder);

        self::assertTrue($bag->has($builderName));
    }

    public function testRemove(): void
    {
        $bag = new BuilderBag();
        $fooBuilder = $this->getBuilder('foo');
        $acmeBuilder = $this->getBuilder('acme');
        $fooBuilderName = $bag->add($fooBuilder);
        $acmeBuilderName = $bag->add($acmeBuilder);

        $bag->remove($fooBuilderName);

        self::assertFalse($bag->has($fooBuilderName), 'Foo builder should not exist anymore.');
        self::assertTrue($bag->has($acmeBuilderName), 'Acme builder should exist.');
        self::assertCount(1, $bag->all());
    }

    public function testClear(): void
    {
        $bag = new BuilderBag(
            [
                $this->getBuilder('foo'),
                $this->getBuilder('baz'),
            ],
        );

        $bag->clear();

        self::assertEmpty($bag->all());
    }

    public function testGet(): void
    {
        $bag = new BuilderBag();
        $bazBuilder = $this->getBuilder('baz');
        $builderName = $bag->add($bazBuilder);

        self::assertNotEmpty($bag->get($builderName));
    }

    private function getBuilder(string $name): BuilderInterface&Stub
    {
        $builder = $this->createStub(NamedBuilderInterface::class);

        $builder->method('getName')->willReturn($name);
        $builder->method('toArray')->willReturn([]);

        return $builder;
    }

}
