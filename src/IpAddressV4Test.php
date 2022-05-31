<?php

namespace Cabloo\IpAddress;

use PHPUnit\Framework\TestCase;

class IpAddressV4Test extends TestCase {
  /** @dataProvider dataInAndOut */
  public function testCreateOutputMatchesInput(string $input, string $output) {
    try {
      $addr = new IpAddressV4($input);
      $this->assertEquals($output, (string) $addr);
    } catch (InvalidIpAddress $exception) {
      $this->assertEquals($output, $exception->getMessage());
    }
  }

  /** @dataProvider dataConversions */
  public function testConversions(
    string $input,
    int $long,
    string $hex,
    string $binary
  ): void {
    $longInt = (new IpAddressV4($input))->asLongInt();
    $this->assertEquals($long, $longInt);
    $this->assertEquals((string) $long, (new IpAddressV4($input))->long());
    $this->assertEquals($hex, (new IpAddressV4($input))->hex());
    $this->assertEquals($binary, (new IpAddressV4($input))->binary());
    $this->assertEquals($input, (string) IpAddressV4::fromLong($longInt));
  }

  /** @test */
  public function testRangeInterface(): void {
    $ip = new IpAddressV4('127.0.0.1');
    $this->assertInstanceOf(IIpAddressRange::class, $ip);
    $this->assertInstanceOf(IIpAddress::class, $ip);
    $this->assertEquals($ip, $ip->start());
    $this->assertEquals($ip, $ip->end());
  }

  /** @test */
  public function testRange(): void {
    $range = IpAddressV4::range('1.0.0.1', '2.0.0.1');
    $this->assertEquals('1.0.0.1', (string) $range->start());
    $this->assertEquals('2.0.0.1', (string) $range->end());
  }

  /** @test */
  public function testPartial(): void {
    try {
      new IpAddressV4('127.0');
      $this->assertTrue(false); // should not be reached
    } catch (PartialIpAddress $exc) {
      $partial = $exc->partial();
      $this->assertEquals('127.0.0.0', (string) $partial);
      $this->assertEquals(['127', '0'], $partial->parts());
    }
  }

  public function dataConversions(): array {
    return [
      [
        'input' => '127.0.0.1',
        'long' => 2130706433,
        'hex' => '7f000001',
        'binary' => hex2bin('7f000001'),
      ],
      [
        'input' => '1.0.0.1',
        'long' => 16777217,
        'hex' => '01000001',
        'binary' => hex2bin('01000001'),
      ],
      [
        'input' => '99.99.99.99',
        'long' => 1667457891,
        'hex' => '63636363',
        'binary' => hex2bin('63636363'),
      ],
      [
        'input' => '0.0.0.0',
        'long' => 0,
        'hex' => '00000000',
        'binary' => hex2bin('00000000'),
      ],
      [
        'input' => '255.255.255.255',
        'long' => 4294967295,
        'hex' => 'ffffffff',
        'binary' => hex2bin('ffffffff'),
      ],
    ];
  }

  public static function dataInAndOut() {
    return [
      'correct IP' => ['input' => '127.0.0.1', 'output' => '127.0.0.1'],
      'correct IP max' => [
        'input' => '255.255.255.255',
        'output' => '255.255.255.255',
      ],
      'correct IP min' => ['input' => '0.0.0.0', 'output' => '0.0.0.0'],
      'many 0s' => [
        'input' => '001.000000.00220.202',
        'output' => '1.0.220.202',
      ],
      'empty string' => [
        'input' => '',
        'output' => 'Invalid IP Address:  (partial)',
      ],
      'over max' => [
        'input' => '1.256.0.0',
        'output' =>
          'Invalid IP Address: 1.256.0.0 (256 exceeds maximum value of 255)',
      ],
      'too many parts' => [
        'input' => '127.0.0.1.1',
        'output' =>
          'Invalid IP Address: 127.0.0.1.1 (127.0.0.1.1 has too many parts (expected 4, found 5))',
      ],
      'contains illegal characters' => [
        'input' => '127.0a.0.1',
        'output' =>
          'Invalid IP Address: 127.0a.0.1 (contains illegal characters)',
      ],
      'partial' => [
        'input' => '127.0',
        'output' => 'Invalid IP Address: 127.0 (partial)',
      ],
    ];
  }
}
