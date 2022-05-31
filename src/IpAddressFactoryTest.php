<?php

namespace Cabloo\IpAddress;

use PHPUnit\Framework\TestCase;

class IpAddressFactoryTest extends TestCase {
  protected function setUp(): void {
    parent::setUp();

    $this->subject = new IpAddressFactory();
  }

  /** @test */
  public function testRange(): void {
    $this->assertEquals(
      '1.0.0.1 - 1.0.0.2',
      (string) $this->subject->range(
        new IpAddressV4('1.0.0.1'),
        new IpAddressV4('1.0.0.2')
      )
    );
    $this->assertEquals(
      '::a - ::b',
      (string) $this->subject->range(
        new IpAddressV6('::a'),
        new IpAddressV6('::b')
      )
    );
  }

  /** @test */
  public function testPartialRange(): void {
    $this->assertEquals(
      '1.2.0.0 - 1.2.3.0',
      (string) $this->subject->partialRange('1.2', '1.2.3')
    );
  }

  /** @test */
  public function testFind(): void {
    $this->assertEquals(
      ['1.2.0.0/24', '1.3.0.0/28'],
      array_map(function (IIpAddressRange $range): string {
        return (string) $range;
      }, $this->subject->find('test 1.2.0.0/24 test', '1.3.0.0/28'))
    );
  }

  /** @dataProvider dataMake */
  public function testMake(string $input, ?string $output): void {
    if ($output === null) {
      $this->assertNull($this->subject->make($input));
      $this->expectException(InvalidIpAddress::class);
      $this->subject->makeEnforce($input);
      return;
    }
    $this->assertEquals($output, (string) $this->subject->make($input));
    $this->assertEquals($output, (string) $this->subject->makeEnforce($input));
  }

  /** @dataProvider dataMakeFromPartialIPv4 */
  public function testMakeFromPartialIPv4(
    string $input,
    ?string $output
  ): void {
    if ($output === null) {
      $this->assertNull($this->subject->makeFromPartialIPv4($input));
      return;
    }
    $this->assertEquals(
      $output,
      (string) $this->subject->makeFromPartialIPv4($input)
    );
  }

  public function dataMakeFromPartialIPv4(): array {
    return [
      [
        'input' => '1.2',
        'output' => '1.2.0.0',
      ],
      [
        'input' => '1.5.4',
        'output' => '1.5.4.0',
      ],
      [
        'input' => '',
        'output' => null,
      ],
      [
        'input' => '1.5.4.5',
        'output' => '1.5.4.5',
      ],
    ];
  }

  public function dataMake(): array {
    return [
      'ipv4' => [
        'input' => '1.2.3.4',
        'output' => '1.2.3.4',
      ],
      'ipv4 partial' => [
        'input' => '1.2',
        'output' => null,
      ],
      'ipv4 invalid' => [
        'input' => '1.256.0.0',
        'output' => null,
      ],
      'ipv6' => [
        'input' => '::a',
        'output' => '::a',
      ],
      'ipv6 partial' => [
        'input' => 'a:b',
        'output' => null,
      ],
      'ipv6 invalid' => [
        'input' => 'a:z:b',
        'output' => null,
      ],
      'neither' => [
        'input' => 'zane',
        'output' => null,
      ],
      'empty' => [
        'input' => '',
        'output' => null,
      ],
    ];
  }

  /**
   * Test that the IpAddress::all() function works as expected.
   */
  public function testAll() {
    $range = function ($start, $end) {
      return new IpAddressRange(new IpAddressV4($start), new IpAddressV4($end));
    };

    $addrs = [
      '1.0.0.1' => [
        new IpAddressV4('1.0.0.1'),
        $range('1.0.0.10', '1.0.0.19'),
        $range('1.0.0.100', '1.0.0.199'),
      ],
      '1.0.0.2' => [
        new IpAddressV4('1.0.0.2'),
        $range('1.0.0.20', '1.0.0.29'),
        $range('1.0.0.200', '1.0.0.255'),
      ],
      '1.0.0.3' => [new IpAddressV4('1.0.0.3'), $range('1.0.0.30', '1.0.0.39')],
      '1.0.0.10' => [
        new IpAddressV4('1.0.0.10'),
        $range('1.0.0.100', '1.0.0.109'),
      ],
    ];

    foreach ($addrs as $addr => $ranges) {
      $all = $this->subject->all($addr);
      $toString = function ($addr) {
        return (string) $addr;
      };
      $this->assertEquals(
        array_map($toString, $all),
        array_map($toString, $ranges)
      );
    }
  }
}
