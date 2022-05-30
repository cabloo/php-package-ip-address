<?php

namespace Cabloo\IpAddress;

use PHPUnit\Framework\TestCase;

class IpAddressRangeSubtractorTest extends TestCase {
  /**
   * @param string[] $expectedOutput
   *
   * @dataProvider dataSubtract
   */

  public function testSubtract(
    IIpAddressRange $remove,
    IIpAddressRange $from,
    array $expectedOutput
  ) {
    $subtractor = new IpAddressRangeSubtractor();
    $ranges = $subtractor->subtract($remove, $from);

    $this->assertEquals(
      $expectedOutput,
      array_map(function (IIpAddressRange $range) {
        return (string) $range;
      }, $ranges)
    );
  }

  public function dataSubtract() {
    return [
      // One result
      [
        'remove' => new IpAddressRange(
          new IpAddressV4("1.0.0.1"),
          new IpAddressV4("1.0.0.2")
        ),
        'from' => new IpAddressRange(
          new IpAddressV4("1.0.0.1"),
          new IpAddressV4("1.0.0.3")
        ),
        'expectedOutput' => ["1.0.0.3"],
      ],
      [
        'remove' => new IpAddressRange(
          new IpAddressV4("1.0.0.1"),
          new IpAddressV4("1.0.0.2")
        ),
        'from' => new IpAddressRange(
          new IpAddressV4("1.0.0.2"),
          new IpAddressV4("1.0.0.3")
        ),
        'expectedOutput' => ["1.0.0.3"],
      ],
      [
        'remove' => new IpAddressRange(
          new IpAddressV4("1.0.0.1"),
          new IpAddressV4("1.0.0.2")
        ),
        'from' => new IpAddressRange(
          new IpAddressV4("1.0.0.3"),
          new IpAddressV4("1.0.0.3")
        ),
        'expectedOutput' => ["1.0.0.3"],
      ],
      // Two results
      [
        'remove' => new IpAddressRange(
          new IpAddressV4("1.0.0.1"),
          new IpAddressV4("1.0.0.2")
        ),
        'from' => new IpAddressRange(
          new IpAddressV4("1.0.0.0"),
          new IpAddressV4("1.0.0.4")
        ),
        'expectedOutput' => ["1.0.0.0", "1.0.0.3 - 1.0.0.4"],
      ],
      [
        'remove' => new IpAddressRange(
          new IpAddressV4("1.0.0.1"),
          new IpAddressV4("1.0.0.3")
        ),
        'from' => new IpAddressRange(
          new IpAddressV4("1.0.0.1"),
          new IpAddressV4("1.0.0.5")
        ),
        'expectedOutput' => ["1.0.0.4 - 1.0.0.5"],
      ],
      // Empty
      [
        'remove' => new IpAddressRange(
          new IpAddressV4("1.0.0.1"),
          new IpAddressV4("1.0.0.5")
        ),
        'from' => new IpAddressRange(
          new IpAddressV4("1.0.0.2"),
          new IpAddressV4("1.0.0.3")
        ),
        'expectedOutput' => [],
      ],
      [
        'remove' => new IpAddressRange(
          new IpAddressV4("1.0.0.1"),
          new IpAddressV4("1.0.0.2")
        ),
        'from' => new IpAddressRange(
          new IpAddressV4("1.0.0.1"),
          new IpAddressV4("1.0.0.2")
        ),
        'expectedOutput' => [],
      ],
      [
        'remove' => new IpAddressRange(
          new IpAddressV4("1.0.0.1"),
          new IpAddressV4("1.0.0.4")
        ),
        'from' => new IpAddressRange(
          new IpAddressV4("1.0.0.2"),
          new IpAddressV4("1.0.0.3")
        ),
        'expectedOutput' => [],
      ],
      [
        'remove' => new IpAddressRange(
          new IpAddressV6("::a"),
          new IpAddressV6("::b")
        ),
        'from' => new IpAddressRange(
          new IpAddressV6("::8"),
          new IpAddressV6("::b000")
        ),
        'expectedOutput' => ["::8 - ::9", "::c - ::b000"],
      ],
      // TODO: tests for cases involving 1.0.0.0 which don't seem to work well (but shouldn't appear in production).
    ];
  }
}
