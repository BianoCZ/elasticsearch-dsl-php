<?php

declare(strict_types = 1);

namespace Biano\ElasticsearchDSL\Tests\Unit\Aggregation\Bucketing;

use Biano\ElasticsearchDSL\Aggregation\Bucketing\DateRangeAggregation;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function count;

class DateRangeAggregationTest extends TestCase
{

    public function testIfExceptionIsThrownWhenNoParametersAreSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Date range aggregation must have field, format set and range added.');

        $aggregation = new DateRangeAggregation('test_agg');
        $aggregation->getArray();
    }

    public function testIfExceptionIsThrownWhenBothRangesAreNull(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Either from or to must be set. Both cannot be null.');

        $aggregation = new DateRangeAggregation('test_agg');
        $aggregation->addRange(null, null);
    }

    public function testDateRangeAggregationGetArray(): void
    {
        $aggregation = new DateRangeAggregation('foo', 'baz');
        $aggregation->addRange(10, 20);
        $aggregation->setFormat('bar');
        $aggregation->setKeyed(true);

        $result = $aggregation->getArray();
        $expected = [
            'format' => 'bar',
            'field' => 'baz',
            'ranges' => [['from' => 10, 'to' => 20]],
            'keyed' => true,
        ];

        self::assertEquals($expected, $result);
    }

    public function testDateRangeAggregationGetType(): void
    {
        $aggregation = new DateRangeAggregation('foo');

        $result = $aggregation->getType();

        self::assertEquals('date_range', $result);
    }

    /**
     * @param list<array<string,mixed>>|null $ranges
     */
    #[DataProvider('provideDateRangeAggregationConstructor')]
    public function testDateRangeAggregationConstructor(?string $field = null, ?string $format = null, ?array $ranges = null): void
    {
        $aggregation = $this->createMock(DateRangeAggregation::class);
        $aggregation->expects($field !== null ? self::once() : self::never())->method('setField')->with($field);
        $aggregation->expects($format !== null ? self::once() : self::never())->method('setFormat')->with($format);
        $aggregation->expects(self::exactly(count($ranges ?? [])))->method('addRange');

        if ($field !== null) {
            if ($format !== null) {
                if ($ranges !== null) {
                    $aggregation->__construct('mock', $field, $format, $ranges);
                } else {
                    $aggregation->__construct('mock', $field, $format);
                }
            } else {
                $aggregation->__construct('mock', $field);
            }
        } else {
            $aggregation->__construct('mock');
        }
    }

    /**
     * @return iterable<array<string,mixed>>
     */
    public static function provideDateRangeAggregationConstructor(): iterable
    {
        // Case #0. Minimum arguments.
        yield [];

        // Case #1. Provide field.
        yield ['field' => 'fieldName'];

        // Case #2. Provide format.
        yield ['field' => 'fieldName', 'format' => 'formatString'];

        // Case #3. Provide empty ranges.
        yield ['field' => 'fieldName', 'format' => 'formatString', 'ranges' => []];

        // Case #4. Provide 1 range.
        yield [
            'field' => 'fieldName',
            'format' => 'formatString',
            'ranges' => [['from' => 'value']],
        ];

        // Case #4. Provide 2 ranges.
        yield [
            'field' => 'fieldName',
            'format' => 'formatString',
            'ranges' => [['from' => 'value'], ['to' => 'value']],
        ];

        // Case #5. Provide 3 ranges.
        yield [
            'field' => 'fieldName',
            'format' => 'formatString',
            'ranges' => [['from' => 'value'], ['to' => 'value'], ['from' => 'value', 'to' => 'value2']],
        ];
    }

}
