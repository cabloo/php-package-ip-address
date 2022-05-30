<?php

namespace Cabloo\IpAddress;

use PHPUnit\Framework\TestCase;

class IpAddressRangeComparatorTest extends TestCase {
  /** @dataProvider dataGetRangeThatCoversAllRanges */
  public function testGetRangeThatCoversAllRanges(
    string $expectedStart,
    string $expectedEnd,
    array $ranges
  ): void {
    $range = IpAddressRangeComparator::getRangeThatCoversAllRanges(
      ...array_map(function (array $range): IIpAddressRange {
        $factory = new IpAddressFactory();
        return new IpAddressRange(
          $factory->makeEnforce($range[0]),
          $factory->makeEnforce($range[1])
        );
      }, $ranges)
    );
    $this->assertEquals($expectedStart, $range->start());
    $this->assertEquals($expectedEnd, $range->end());
  }

  public function dataGetRangeThatCoversAllRanges(): array {
    return [
      [
        'expectedStart' => '1.0.0.0',
        'expectedEnd' => '1.0.0.255',
        'ranges' => [
          ['1.0.0.8', '1.0.0.9'],
          ['1.0.0.255', '1.0.0.255'],
          ['1.0.0.0', '1.0.0.10'],
        ],
      ],
      [
        'expectedStart' => '1.0.0.0',
        'expectedEnd' => '1.0.255.255',
        'ranges' => [
          ['1.0.0.8', '1.0.0.9'],
          ['1.0.0.255', '1.0.0.255'],
          ['1.0.0.0', '1.0.0.10'],
          ['1.0.0.0', '1.0.255.255'],
        ],
      ],
      [
        'expectedStart' => '0.0.0.0',
        'expectedEnd' => '255.255.255.255',
        'ranges' => [
          ['0.0.0.0', '1.0.0.9'],
          ['1.0.0.255', '1.0.0.255'],
          ['1.0.0.0', '1.0.0.10'],
          ['1.0.0.0', '255.255.255.255'],
        ],
      ],
      [
        'expectedStart' => '::a',
        'expectedEnd' => '::ffff',
        'ranges' => [
          ['::a', '::b'],
          ['::d', '::ffff'],
          ['::c', '::e'],
          ['::b', '::c'],
        ],
      ],
    ];
  }

  /** @dataProvider dataFullyContainsRange */
  public function testFullyContainsRange(
    string $r1start,
    string $r1end,
    string $r2start,
    string $r2end,
    bool $expected
  ): void {
    $factory = new IpAddressFactory();
    $r1 = new IpAddressRange(
      $factory->makeEnforce($r1start),
      $factory->makeEnforce($r1end)
    );
    $r2 = new IpAddressRange(
      $factory->makeEnforce($r2start),
      $factory->makeEnforce($r2end)
    );
    $this->assertEquals(
      $expected,
      (new IpAddressRangeComparator($r1))->fullyContainsRange($r2),
      'Failed to assert that ' .
        $r1 .
        ' fully contains ' .
        $r2 .
        ' is ' .
        ($expected ? 'true' : 'false')
    );
  }

  public function dataFullyContainsRange(): array {
    return [
      [
        'r1start' => '1.0.0.0',
        'r1end' => '1.0.0.0',
        'r2start' => '1.0.0.0',
        'r2end' => '1.0.0.0',
        'expected' => true,
      ],
      [
        'r1start' => '1.0.0.0',
        'r1end' => '1.0.0.0',
        'r2start' => '1.0.0.0',
        'r2end' => '1.0.0.1',
        'expected' => false,
      ],
      [
        'r1start' => '1.0.0.0',
        'r1end' => '1.0.0.255',
        'r2start' => '1.0.0.200',
        'r2end' => '1.0.0.222',
        'expected' => true,
      ],
      [
        'r1start' => '::a',
        'r1end' => '::b000',
        'r2start' => '::aa',
        'r2end' => '::ab',
        'expected' => true,
      ],
      [
        'r1start' => '::a',
        'r1end' => '::b',
        'r2start' => '::aa',
        'r2end' => '::bb',
        'expected' => false,
      ],
    ];
  }

  /** @dataProvider dataOverlapsRange */
  public function testOverlapsRange(
    string $r1start,
    string $r1end,
    string $r2start,
    string $r2end,
    bool $expected
  ): void {
    $factory = new IpAddressFactory();
    $r1 = new IpAddressRange(
      $factory->makeEnforce($r1start),
      $factory->makeEnforce($r1end)
    );
    $r2 = new IpAddressRange(
      $factory->makeEnforce($r2start),
      $factory->makeEnforce($r2end)
    );
    $this->assertEquals(
      $expected,
      (new IpAddressRangeComparator($r1))->overlapsRange($r2),
      'Failed to assert that ' .
        $r1 .
        ' overlaps ' .
        $r2 .
        ' is ' .
        ($expected ? 'true' : 'false')
    );
  }

  public function dataOverlapsRange(): array {
    return [
      [
        'r1start' => '1.0.0.0',
        'r1end' => '1.0.0.0',
        'r2start' => '1.0.0.0',
        'r2end' => '1.0.0.0',
        'expected' => true,
      ],
      [
        'r1start' => '1.0.0.0',
        'r1end' => '1.0.0.0',
        'r2start' => '1.0.0.0',
        'r2end' => '1.0.0.1',
        'expected' => true,
      ],
      [
        'r1start' => '1.0.0.0',
        'r1end' => '1.0.0.255',
        'r2start' => '1.0.0.200',
        'r2end' => '1.0.0.222',
        'expected' => true,
      ],
      [
        'r1start' => '1.0.0.0',
        'r1end' => '1.0.0.255',
        'r2start' => '2.0.0.200',
        'r2end' => '2.0.0.222',
        'expected' => false,
      ],
      [
        'r1start' => '::a',
        'r1end' => '::b000',
        'r2start' => '::aa',
        'r2end' => '::ab',
        'expected' => true,
      ],
      [
        'r1start' => '::a',
        'r1end' => '::b',
        'r2start' => '::aa',
        'r2end' => '::bb',
        'expected' => false,
      ],
    ];
  }

  /** @dataProvider dataEquals */
  public function testEquals(
    string $r1start,
    string $r1end,
    string $r2start,
    string $r2end,
    bool $expected
  ): void {
    $factory = new IpAddressFactory();
    $r1 = new IpAddressRange(
      $factory->makeEnforce($r1start),
      $factory->makeEnforce($r1end)
    );
    $r2 = new IpAddressRange(
      $factory->makeEnforce($r2start),
      $factory->makeEnforce($r2end)
    );
    $this->assertEquals(
      $expected,
      (new IpAddressRangeComparator($r1))->equals($r2),
      'Failed to assert that ' .
        $r1 .
        ' equals ' .
        $r2 .
        ' is ' .
        ($expected ? 'true' : 'false')
    );
  }

  public function dataEquals(): array {
    return [
      [
        'r1start' => '1.0.0.0',
        'r1end' => '1.0.0.0',
        'r2start' => '1.0.0.0',
        'r2end' => '1.0.0.0',
        'expected' => true,
      ],
      [
        'r1start' => '1.0.0.0',
        'r1end' => '1.0.0.0',
        'r2start' => '1.0.0.0',
        'r2end' => '1.0.0.1',
        'expected' => false,
      ],
      [
        'r1start' => '1.0.0.0',
        'r1end' => '1.0.0.255',
        'r2start' => '1.0.0.200',
        'r2end' => '1.0.0.222',
        'expected' => false,
      ],
      [
        'r1start' => '1.0.0.0',
        'r1end' => '1.0.0.255',
        'r2start' => '2.0.0.200',
        'r2end' => '2.0.0.222',
        'expected' => false,
      ],
      [
        'r1start' => '::a',
        'r1end' => '::b000',
        'r2start' => '::aa',
        'r2end' => '::ab',
        'expected' => false,
      ],
      [
        'r1start' => '::a',
        'r1end' => '::b000',
        'r2start' => '::a',
        'r2end' => '::b000',
        'expected' => true,
      ],
      [
        'r1start' => '::a',
        'r1end' => '::b',
        'r2start' => '::aa',
        'r2end' => '::bb',
        'expected' => false,
      ],
    ];
  }
}
