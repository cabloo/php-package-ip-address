<?php

namespace Cabloo\IpAddress;

use PHPUnit\Framework\TestCase;

class IpAddressIncrementerTest extends TestCase {
  /**
   * @dataProvider dataRange
   */
  public function testRange(
    IIpAddressRange $range,
    int $limit,
    array $expectedOutput
  ): void {
    $incrementer = new IpAddressIncrementer();
    $results = $incrementer->range($range, $limit);

    $this->assertEquals(
      $expectedOutput,
      array_map(function (IIpAddress $address) {
        return (string) $address;
      }, $results)
    );
  }

  /**
   * @dataProvider dataIncrement
   */
  public function testIncrement(IIpAddress $input, IIpAddress $output): void {
    $incrementer = new IpAddressIncrementer();
    $this->assertEquals(
      (string) $output,
      (string) $incrementer->increment($input)
    );
  }

  /** @test */
  public function testIncrementPastMax(): void {
    $this->assertEquals(
      '0.0.0.0',
      (string) (new IpAddressIncrementer())->increment(
        new IpAddressV4('255.255.255.255'),
        1
      )
    );
  }

  public function dataIncrement(): array {
    return [
      [
        'input' => new IpAddressV4('1.1.1.1'),
        'output' => new IpAddressV4('1.1.1.2'),
      ],
      [
        'input' => new IpAddressV4('1.1.1.255'),
        'output' => new IpAddressV4('1.1.2.0'),
      ],
      [
        'input' => new IpAddressV4('1.255.255.255'),
        'output' => new IpAddressV4('2.0.0.0'),
      ],
      [
        'input' => new IpAddressV4('1.1.1.9'),
        'output' => new IpAddressV4('1.1.1.10'),
      ],
      [
        'input' => new IpAddressV6('::0'),
        'output' => new IpAddressV6('::1'),
      ],
      [
        'input' => new IpAddressV6('::a:0:ffff'),
        'output' => new IpAddressV6('::a:1:0'),
      ],
      [
        'input' => new IpAddressV6('::ffff:ffff:ffff'),
        'output' => new IpAddressV6('::1:0:0:0'),
      ],
      [
        'input' => new IpAddressV6('::9'),
        'output' => new IpAddressV6('::a'),
      ],
      [
        'input' => new IpAddressV6('::f'),
        'output' => new IpAddressV6('::10'),
      ],
    ];
  }

  public function dataRange(): array {
    return [
      'IPv4 Range' => [
        'range' => new IpAddressRange(
          new IpAddressV4('1.1.1.1'),
          new IpAddressV4('1.1.1.255')
        ),
        'limit' => 5,
        'expectedOutput' => [
          '1.1.1.1',
          '1.1.1.2',
          '1.1.1.3',
          '1.1.1.4',
          '1.1.1.5',
        ],
      ],
      'IPv4 Single IP' => [
        'range' => new IpAddressRange(
          new IpAddressV4('1.1.1.1'),
          new IpAddressV4('1.1.1.1')
        ),
        'limit' => 10,
        'expectedOutput' => ['1.1.1.1'],
      ],
      'IPv6 Single IP' => [
        'range' => new IpAddressRange(
          new IpAddressV6('::a'),
          new IpAddressV6('::a')
        ),
        'limit' => 10,
        'expectedOutput' => ['::a'],
      ],
      'IPv6 Range' => [
        'range' => new IpAddressRange(
          new IpAddressV6('::a'),
          new IpAddressV6('::ffff')
        ),
        'limit' => 10,
        'expectedOutput' => [
          '::a',
          '::b',
          '::c',
          '::d',
          '::e',
          '::f',
          '::10',
          '::11',
          '::12',
          '::13',
        ],
      ],
    ];
  }
}
