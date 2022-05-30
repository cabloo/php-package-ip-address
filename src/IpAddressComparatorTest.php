<?php

namespace Cabloo\IpAddress;

use PHPUnit\Framework\TestCase;

class IpAddressComparatorTest extends TestCase {
  /** @test */
  public function testMinOf(): void {
    $this->assertEquals(
      '1.1.1.1',
      (string) IpAddressComparator::minOf(
        new IpAddressV4('123.255.255.255'),
        new IpAddressV4('1.1.1.1'),
        new IpAddressV4('200.0.0.0'),
        new IpAddressV4('123.0.0.0')
      )
    );
  }

  /** @test */
  public function testMaxOf(): void {
    $this->assertEquals(
      '200.0.0.0',
      (string) IpAddressComparator::maxOf(
        new IpAddressV4('123.255.255.255'),
        new IpAddressV4('1.1.1.1'),
        new IpAddressV4('200.0.0.0'),
        new IpAddressV4('123.0.0.0')
      )
    );
  }

  /** @dataProvider dataComparisons */
  public function testComparisons(
    IIpAddress $addr1,
    string $comparison,
    IIpAddress $addr2
  ): void {
    $compare = new IpAddressComparator($addr1);
    $this->assertEquals(
      $this->isLessThan($comparison),
      $compare->lessThan($addr2)
    );
    $this->assertEquals(
      $this->isLessThan($comparison),
      IpAddressComparator::lt($addr1, $addr2)
    );
    $this->assertEquals(
      $this->isGreaterThan($comparison),
      $compare->greaterThan($addr2)
    );
    $this->assertEquals(
      $this->isGreaterThan($comparison),
      IpAddressComparator::gt($addr1, $addr2)
    );
    $this->assertEquals(
      $this->isEqualTo($comparison),
      $compare->equalTo($addr2)
    );
    $this->assertEquals(
      $this->isEqualTo($comparison),
      IpAddressComparator::eq($addr1, $addr2)
    );
    $this->assertEquals(
      $this->isLessThanOrEqualTo($comparison),
      $compare->lessThanOrEqualTo($addr2)
    );
    $this->assertEquals(
      $this->isGreaterThanOrEqualTo($comparison),
      $compare->greaterThanOrEqualTo($addr2)
    );
  }

  public function dataComparisons(): array {
    return array_reduce(
      [
        [
          'addr1' => new IpAddressV4('1.1.1.1'),
          'comparison' => '=',
          'addr2' => new IpAddressV4('1.1.1.1'),
        ],
        [
          'addr1' => new IpAddressV4('1.1.1.1'),
          'comparison' => '<',
          'addr2' => new IpAddressV4('1.1.1.10'),
        ],
        [
          'addr1' => new IpAddressV4('1.1.1.10'),
          'comparison' => '>',
          'addr2' => new IpAddressV4('1.1.1.1'),
        ],
        [
          'addr1' => new IpAddressV4('1.1.0.1'),
          'comparison' => '<',
          'addr2' => new IpAddressV4('1.1.1.1'),
        ],
        [
          'addr1' => new IpAddressV4('19.1.1.1'),
          'comparison' => '>',
          'addr2' => new IpAddressV4('2.1.0.1'),
        ],
        [
          'addr1' => new IpAddressV4('2.1.1.1'),
          'comparison' => '<',
          'addr2' => new IpAddressV4('10.1.0.1'),
        ],
        [
          'addr1' => new IpAddressV6('::0'),
          'comparison' => '=',
          'addr2' => new IpAddressV6('::0'),
        ],
        [
          'addr1' => new IpAddressV6('::0'),
          'comparison' => '<',
          'addr2' => new IpAddressV6('::1'),
        ],
        [
          'addr1' => new IpAddressV6('aa::0'),
          'comparison' => '>',
          'addr2' => new IpAddressV6('b::1'),
        ],
      ],
      // This isn't used by the test, it just provides helpful debug info
      function (array $aggregate, array $value): array {
        $aggregate[
          sprintf(
            "%s %s %s",
            $value['addr2'],
            $value['comparison'],
            $value['addr1']
          )
        ] = $value;
        return $aggregate;
      },
      []
    );
  }

  private function isGreaterThanOrEqualTo(string $comparison): bool {
    return in_array($comparison, ['>', '=']);
  }

  private function isLessThanOrEqualTo(string $comparison): bool {
    return in_array($comparison, ['<', '=']);
  }

  private function isEqualTo(string $comparison): bool {
    return in_array($comparison, ['=']);
  }

  private function isGreaterThan(string $comparison): bool {
    return in_array($comparison, ['>']);
  }

  private function isLessThan(string $comparison): bool {
    return in_array($comparison, ['<']);
  }

  /** @dataProvider dataMinMax */
  public function testMinMax(
    string $a,
    string $b,
    string $min,
    string $max
  ): void {
    $addressFactory = new IpAddressFactory();
    $aAddress = $addressFactory->makeEnforce($a);
    $bAddress = $addressFactory->makeEnforce($b);
    $this->assertEquals(
      $min,
      (string) (new IpAddressComparator($aAddress))->min($bAddress)
    );
    $this->assertEquals(
      $max,
      (string) (new IpAddressComparator($aAddress))->max($bAddress)
    );
  }

  public function dataMinMax(): array {
    return [
      [
        'a' => '1.0.0.1',
        'b' => '2.0.0.1',
        'min' => '1.0.0.1',
        'max' => '2.0.0.1',
      ],
      [
        'a' => '2.0.0.1',
        'b' => '2.0.0.1',
        'min' => '2.0.0.1',
        'max' => '2.0.0.1',
      ],
      [
        'a' => '1.0.1.1',
        'b' => '1.0.0.1',
        'min' => '1.0.0.1',
        'max' => '1.0.1.1',
      ],
      [
        'a' => '155.0.0.1',
        'b' => '20.255.255.255',
        'min' => '20.255.255.255',
        'max' => '155.0.0.1',
      ],
      [
        'a' => '::a',
        'b' => '::ffff',
        'min' => '::a',
        'max' => '::ffff',
      ],
      [
        'a' => 'abc:def:ddd::',
        'b' => 'abc:def:abc::ffff',
        'min' => 'abc:def:abc::ffff',
        'max' => 'abc:def:ddd::',
      ],
    ];
  }
}
