<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\SearchEndpoint;

use Biano\ElasticsearchDSL\SearchEndpoint\SuggestEndpoint;
use Biano\ElasticsearchDSL\Suggest\Suggest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SuggestEndpointTest extends TestCase
{

    public function testItCanBeInstantiated(): void
    {
        self::assertInstanceOf(SuggestEndpoint::class, new SuggestEndpoint());
    }

    public function testEndpointGetter(): void
    {
        $suggestName = 'acme_suggest';
        $text = 'foo';
        $suggest = new Suggest($suggestName, 'text', $text, 'acme');
        $endpoint = new SuggestEndpoint();
        $endpoint->add($suggest, $suggestName);
        $builders = $endpoint->getAll();

        self::assertCount(1, $builders);
        self::assertSame($suggest, $builders[$suggestName]);
    }

    public function testNormalize(): void
    {
        $instance = new SuggestEndpoint();

        $normalizerInterface = $this->createStub(NormalizerInterface::class);

        $suggest = new Suggest('foo', 'bar', 'acme', 'foo');
        $instance->add($suggest);

        self::assertEquals(
            $suggest->toArray(),
            $instance->normalize($normalizerInterface),
        );
    }

}
