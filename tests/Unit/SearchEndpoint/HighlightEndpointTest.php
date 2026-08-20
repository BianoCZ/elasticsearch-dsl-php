<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\SearchEndpoint;

use Biano\ElasticsearchDSL\Highlight\Highlight;
use Biano\ElasticsearchDSL\SearchEndpoint\HighlightEndpoint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function json_encode;

class HighlightEndpointTest extends TestCase
{

    public function testItCanBeInstantiated(): void
    {
        self::assertInstanceOf(HighlightEndpoint::class, new HighlightEndpoint());
    }

    public function testNormalization(): void
    {
        $instance = new HighlightEndpoint();
        $normalizerInterface = $this->createStub(NormalizerInterface::class);

        self::assertFalse($instance->normalize($normalizerInterface));

        $highlight = new Highlight();
        $highlight->addField('acme');
        $instance->add($highlight);

        self::assertEquals(
            json_encode($highlight->toArray()),
            json_encode($instance->normalize($normalizerInterface)),
        );
    }

    public function testEndpointGetter(): void
    {
        $highlightName = 'acme_highlight';
        $highlight = new Highlight();
        $highlight->addField('acme');

        $endpoint = new HighlightEndpoint();
        $endpoint->add($highlight, $highlightName);
        $builders = $endpoint->getAll();

        self::assertCount(1, $builders);
        self::assertSame($highlight, $builders[$highlightName]);
    }

}
