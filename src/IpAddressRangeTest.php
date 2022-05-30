<?php

namespace Cabloo\IpAddress;

use PHPUnit\Framework\TestCase;

class IpAddressRangeTest extends TestCase {
  /** @test */
  public function testIPv4Range(): void {
    $range = new IpAddressRange(
      ($start = new IpAddressV4('1.0.0.1')),
      ($end = new IpAddressV4('1.0.0.4'))
    );
    $this->assertEquals($start, $range->start());
    $this->assertEquals($end, $range->end());
    $this->assertEquals('1.0.0.1 - 1.0.0.4', (string) $range);
  }
  /** @test */
  public function testIPv6Range(): void {
    $range = new IpAddressRange(
      ($start = new IpAddressV6('::a')),
      ($end = new IpAddressV6('::b'))
    );
    $this->assertEquals($start, $range->start());
    $this->assertEquals($end, $range->end());
    $this->assertEquals('::a - ::b', (string) $range);
  }

  /** @test */
  public function testIPv4StartGreaterThanEnd(): void {
    $this->expectException(InvalidIpAddress::class);
    new IpAddressRange(new IpAddressV4('1.0.0.4'), new IpAddressV4('1.0.0.1'));
  }

  /** @test */
  public function testIPv6StartGreaterThanEnd(): void {
    $this->expectException(InvalidIpAddress::class);
    new IpAddressRange(new IpAddressV4('::b'), new IpAddressV4('::a'));
  }
}
