<?php

namespace Cabloo\IpAddress;

use PHPUnit\Framework\TestCase;

class IpAddressRangeCidrV4Test extends TestCase {
  /** @dataProvider dataConstruct */
  public function testConstruct(
    string $input,
    string $start,
    string $end,
    string $toString
  ): void {
    $address = new IpAddressRangeCidrV4($input);
    $this->assertEquals($start, (string) $address->start());
    $this->assertEquals($end, (string) $address->end());
    $this->assertEquals($toString, (string) $address);
  }

  /** @dataProvider dataFailures */
  public function testFailures(string $input, string $exceptionText): void {
    try {
      new IpAddressRangeCidrV4($input);
    } catch (InvalidIpAddress $exc) {
      $this->assertEquals($exceptionText, $exc->getMessage());
    }
  }

  /** @dataProvider dataConversions */
  public function testConversions(
    string $input,
    int $netmask,
    int $count,
    int $cidrMask
  ): void {
    $range = new IpAddressRangeCidrV4($input);
    $this->assertEquals($netmask, $range->netmask());
    $this->assertEquals($count, $range->count());
    $this->assertEquals($cidrMask, $range->cidrMask());
  }

  public function dataConversions(): array {
    return [
      [
        'input' => '127.0.0.4/24',
        'netmask' => -256,
        'count' => 256,
        'cidrMask' => 24,
      ],
      [
        'input' => '127.0.0.4/29',
        'netmask' => -8,
        'count' => 8,
        'cidrMask' => 29,
      ],
    ];
  }

  /** @dataProvider dataFromAddressAndMask */
  public function testFromAddressAndMask(
    string $address,
    string $mask,
    string $output
  ): void {
    $range = IpAddressRangeCidrV4::fromAddressAndMask(
      new IpAddressV4($address),
      new IpAddressV4($mask)
    );
    $this->assertEquals($output, (string) $range);
  }

  public function dataFromAddressAndMask(): array {
    return [
      [
        'address' => '127.0.0.4',
        'mask' => '255.255.255.255',
        'output' => '127.0.0.4/32',
      ],
      [
        'address' => '1.5.255.255',
        'mask' => '255.255.255.228',
        'output' => '1.5.255.240/28',
      ],
      [
        'address' => '127.0.0.4',
        'mask' => '255.255.255.0',
        'output' => '127.0.0.0/24',
      ],
    ];
  }

  public function dataConstruct(): array {
    return [
      [
        'input' => '127.0.0.4/24',
        'start' => '127.0.0.0',
        'end' => '127.0.0.255',
        'toString' => '127.0.0.0/24',
      ],
      [
        'input' => '2.0.0.4/29',
        'start' => '2.0.0.0',
        'end' => '2.0.0.7',
        'toString' => '2.0.0.0/29',
      ],
      [
        'input' => '1.0.0.8/16',
        'start' => '1.0.0.0',
        'end' => '1.0.255.255',
        'toString' => '1.0.0.0/16',
      ],
      [
        'input' => '3.1.0.4/32',
        'start' => '3.1.0.4',
        'end' => '3.1.0.4',
        'toString' => '3.1.0.4/32',
      ],
      [
        'input' => '4.0.0.28/29',
        'start' => '4.0.0.24',
        'end' => '4.0.0.31',
        'toString' => '4.0.0.24/29',
      ],
    ];
  }
  public function dataFailures(): array {
    return [
      [
        'input' => '127.0.0.4/33',
        'exception' => "Invalid IP Address: 127.0.0.4/33 (Invalid mask)",
      ],
      [
        'input' => '127.0.0.4/0',
        'exception' => "Invalid IP Address: 127.0.0.4/0 (Invalid mask)",
      ],
      [
        'input' => '127.0.0.4',
        'exception' =>
          "Invalid IP Address: 127.0.0.4 (CIDR masks must come in the form 1.0.0.0/30)",
      ],
    ];
  }
}
