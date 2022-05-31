<?php

namespace Cabloo\IpAddress;

use PHPUnit\Framework\TestCase;

class IpAddressV6Test extends TestCase {
  /** @dataProvider dataInAndOut */
  public function testCreateOutputMatchesInput(string $input, string $output) {
    try {
      $addr = new IpAddressV6($input);
      $this->assertEquals($output, (string) $addr);
    } catch (InvalidIpAddress $exception) {
      $this->assertEquals($output, $exception->getMessage());
    }
  }

  /** @dataProvider dataConversions */
  public function testConversions(
    string $input,
    string $long,
    string $hex,
    string $binary
  ): void {
    $this->assertEquals($long, (new IpAddressV6($input))->long());
    $this->assertEquals($hex, (new IpAddressV6($input))->hex());
    $this->assertEquals($binary, (new IpAddressV6($input))->binary());
  }

  /** @test */
  public function testRangeInterface(): void {
    $ip = new IpAddressV6('::a');
    $this->assertInstanceOf(IIpAddressRange::class, $ip);
    $this->assertInstanceOf(IIpAddress::class, $ip);
    $this->assertEquals($ip, $ip->start());
    $this->assertEquals($ip, $ip->end());
  }

  /** @test */
  public function testRange(): void {
    $range = IpAddressV6::range('::a', '::b');
    $this->assertEquals('::a', (string) $range->start());
    $this->assertEquals('::b', (string) $range->end());
  }

  /** @test */
  public function testPartial(): void {
    try {
      new IpAddressV6('a:b');
      $this->assertTrue(false); // should not be reached
    } catch (PartialIpAddress $exc) {
      $partial = $exc->partial();
      $this->assertEquals('a:b', (string) $partial);
      $this->assertEquals(['a', 'b'], $partial->parts());
    }
  }

  public function dataConversions(): array {
    return [
      [
        'input' => 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff',
        'long' => '340282366920938586008062602462446642046',
        'hex' => 'ffffffffffffffffffffffffffffffff',
        'binary' => hex2bin('ffffffffffffffffffffffffffffffff'),
      ],
      [
        'input' => '::',
        'long' => '0',
        'hex' => '00000000000000000000000000000000',
        'binary' => hex2bin('00000000000000000000000000000000'),
      ],
      [
        'input' => '0000:0000:0000:0000:0000:0000:0000:0000',
        'long' => '0',
        'hex' => '00000000000000000000000000000000',
        'binary' => hex2bin('00000000000000000000000000000000'),
      ],
      [
        'input' => 'a:b::c',
        'long' => '51923840095135946068200442422660066',
        'hex' => '000a000b00000000000000000000000c',
        'binary' => hex2bin('000a000b00000000000000000000000c'),
      ],
    ];
  }

  public static function dataInAndOut() {
    return [
      'valid IP' => ['input' => '::1', 'output' => '::1'],
      'empty string' => [
        'input' => '',
        'output' => 'Invalid IP Address:  (empty string)',
      ],
      'a-f letters work (lower and uppercase), uppercase gets converted to lower' => [
        'input' => '::afAF',
        'output' => '::afaf',
      ],
      'G not a valid letter' => [
        'input' => '::G',
        'output' => 'Invalid IP Address: ::G (contains illegal characters)',
      ],
      'z not a valid letter' => [
        'input' => '::z',
        'output' => 'Invalid IP Address: ::z (contains illegal characters)',
      ],
      '7 part IP not valid' => [
        'input' => 'aa:aa:aa:aa:aa:aa:aa',
        'output' => 'Invalid IP Address: aa:aa:aa:aa:aa:aa:aa (partial)',
      ],
      'two part IP not valid' => [
        'input' => 'aa:aa',
        'output' => 'Invalid IP Address: aa:aa (partial)',
      ],
      'short IP ending with a colon does not get expanded' => [
        'input' => 'a:',
        'output' => 'Invalid IP Address: a: (partial)',
      ],
      'ending with an extra colon is valid' => [
        'input' => 'a:a:a:a:a:a:a:a:',
        'output' => 'a:a:a:a:a:a:a:a',
      ],

      '> 4 chars does not work' => [
        'input' => '2001:4860:4860::10000',
        'output' =>
          'Invalid IP Address: 2001:4860:4860::10000 (65536 exceeds maximum value of 65535)',
      ],

      'double colon 7 part' => [
        'input' => '::2:3:4:5:6:7:8',
        'output' => '0:2:3:4:5:6:7:8',
      ],
      'double colon 6 part' => [
        'input' => '::3:4:5:6:7:8',
        'output' => '::3:4:5:6:7:8',
      ],
      'double colon 5 part' => [
        'input' => '::4:5:6:7:8',
        'output' => '::4:5:6:7:8',
      ],
      // TODO: this next line should actually be ['1::3:4:5:6:7:8', '1:0:3:4:5:6:7:8'],
      // We are not transforming to the preferred format here.
      'double colon 8 part' => [
        'input' => '1::3:4:5:6:7:8',
        'output' => '1::3:4:5:6:7:8',
      ],
      '8 part' => ['input' => '1:2:3:4:5:6:7:8', 'output' => '1:2:3:4:5:6:7:8'],
      '8 part with zero' => [
        'input' => '1:0:3:4:5:6:7:8',
        'output' => '1:0:3:4:5:6:7:8',
      ],

      '4-char parts with double' => [
        'input' => '2001:4860:4860::8888',
        'output' => '2001:4860:4860::8888',
      ],

      ':0: does nott get converted to a double delim when there is already a double delim not at the end' => [
        'input' => '2620:0:2d0:200::7',
        'output' => '2620:0:2d0:200::7',
      ],
      ':0: does nott get converted to a double delim when there is already a double delim at the end' => [
        'input' => '2620:0:2d0:200::',
        'output' => '2620:0:2d0:200::',
      ],
      'many 0s' => [
        'input' => '0000000000000::0000000010:0000000',
        'output' => '0::10:0',
      ],
      'too many parts' => [
        'input' => '1:2:3:4:5:6:7:8:9',
        'output' =>
          'Invalid IP Address: 1:2:3:4:5:6:7:8:9 (1:2:3:4:5:6:7:8:9 has too many parts (expected 8, found 9))',
      ],
    ];
  }

  /**
   * @param string $start
   * @param string $long
   *
   * @dataProvider dataLongName
   */
  public function testLongName($start, $long) {
    $addr = new IpAddressV6($start);
    $this->assertEquals($long, $addr->longName());
  }

  public static function dataLongName() {
    return [
      [
        'input' => '2620:0:2d0:200::7',
        'output' => '2620:0000:02d0:0200:0000:0000:0000:0007',
      ],
      [
        'input' => '2620:0:2d0:200::',
        'output' => '2620:0000:02d0:0200:0000:0000:0000:0000',
      ],
      [
        'input' => '2620:0:2d0::7',
        'output' => '2620:0000:02d0:0000:0000:0000:0000:0007',
      ],
      [
        'input' => '2620:0:2d0::',
        'output' => '2620:0000:02d0:0000:0000:0000:0000:0000',
      ],
      [
        'input' => '2620:0000:02d0:0000:0000:0000:0007::',
        'output' => '2620:0000:02d0:0000:0000:0000:0007:0000',
      ],
      [
        'input' => '2620:0000:02d0::0000:0000:0007:0000',
        'output' => '2620:0000:02d0:0000:0000:0000:0007:0000',
      ],
      [
        'input' => '2620:0000:02d0:0000:0000:0000::0007',
        'output' => '2620:0000:02d0:0000:0000:0000:0000:0007',
      ],
      [
        'input' => '::2620:0000:02d0:0000:0000:0000:0007',
        'output' => '0000:2620:0000:02d0:0000:0000:0000:0007',
      ],
      ['input' => '::1', 'output' => '0000:0000:0000:0000:0000:0000:0000:0001'],
      ['input' => '1::', 'output' => '0001:0000:0000:0000:0000:0000:0000:0000'],
      [
        'input' => '1::1',
        'output' => '0001:0000:0000:0000:0000:0000:0000:0001',
      ],
      [
        'input' => '1:1::',
        'output' => '0001:0001:0000:0000:0000:0000:0000:0000',
      ],
      [
        'input' => '::1:1',
        'output' => '0000:0000:0000:0000:0000:0000:0001:0001',
      ],
      [
        'input' => '1:1::1',
        'output' => '0001:0001:0000:0000:0000:0000:0000:0001',
      ],
      [
        'input' => '1::1:1',
        'output' => '0001:0000:0000:0000:0000:0000:0001:0001',
      ],
    ];
  }
}
