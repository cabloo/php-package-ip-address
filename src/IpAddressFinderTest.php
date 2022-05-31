<?php

namespace Cabloo\IpAddress;

use PHPUnit\Framework\TestCase;

class IpAddressFinderTest extends TestCase {
  protected function setUp(): void {
    parent::setUp();

    $this->subject = new IpAddressFinder();
  }

  /**
   * Test that the IpService finds IPs correctly.
   * The purpose of this function is primarily to test the regexes that find() uses.
   *
   * @param string   $haystack
   * @param string[] $output
   *
   * @dataProvider dataFind
   */
  public function testFind(string $haystack, array $output): void {
    $toString = function ($addr) {
      return (string) $addr;
    };

    $this->assertEquals(
      $output,
      array_map($toString, $this->subject->find($haystack))
    );
  }

  /** @test */
  public function testFindMultipleHaystacks(): void {
    $toString = function ($addr) {
      return (string) $addr;
    };
    $this->assertEquals(
      ['1.2.3.0/24', '1.1.1.1'],
      array_map(
        $toString,
        $this->subject->find('test 1.1.1.1', 'te 1.2.3.4/24 st')
      )
    );
  }

  /** @test */
  public function testDedupe(): void {
    $toString = function ($addr) {
      return (string) $addr;
    };
    // any CIDR takes precedence over non-CIDR
    // larger CIDRs do not take precedence over smaller
    $this->assertEquals(
      ['1.1.1.0/28', '1.2.3.0/27'],
      array_map(
        $toString,
        $this->subject->find(
          'test 1.1.1.1 1.1.1.2/28 1.1.1.1/24 1.1.1.9/28 1.2.3.4/27 1.2.3.20/26'
        )
      )
    );
    $this->assertEquals(
      ['1.1.1.0/24', '1.2.3.0/27'],
      array_map(
        $toString,
        $this->subject->find(
          'test 1.1.1.1/24 1.1.1.0 1.1.1.9/28 1.2.3.4/27 1.2.3.20/26'
        )
      )
    );
  }

  /** @test */
  public function testFindWithInvalidIPs(): void {
    $toString = function ($addr) {
      return (string) $addr;
    };
    $this->assertEquals(
      ['1.1.1.1'],
      array_map(
        $toString,
        $this->subject->find('999.999.999.999 ::g 1.1.1.1 abc:')
      )
    );
  }

  public function dataFind(): array {
    return [
      [
        'haystack' => '127.0.0.1',
        'output' => ['127.0.0.1'],
      ],
      [
        'haystack' => '255.255.255.255',
        'output' => ['255.255.255.255'],
      ],
      [
        'haystack' => '127.0a.0.1',
        'output' => [],
      ],
      [
        'haystack' => '127.0',
        'output' => [],
      ],
      [
        'haystack' => '127.*',
        'output' => [],
      ],
      // Short syntax
      [
        'haystack' => '::1',
        'output' => ['::1'],
      ],
      // a-f letters work (lower and uppercase), uppercase gets converted to lower
      [
        'haystack' => '::afAF',
        'output' => ['::afaf'],
      ],
      // letters > f don't work
      [
        'haystack' => '::G',
        'output' => [],
      ],
      [
        'haystack' => '::z',
        'output' => [],
      ],
      // Must be 8 parts if none of the parts have a double colon
      [
        'haystack' => 'aa:aa',
        'output' => [],
      ],
      [
        'haystack' => 'aa:aa:aa:aa:aa:aa:aa',
        'output' => [],
      ],
      // Ending with a colon is not valid
      [
        'haystack' => 'a:',
        'output' => [],
      ],

      // > 4 chars doesn't work
      [
        'haystack' => '2001:4860:4860::8888F',
        'output' => ['2001:4860:4860::8888'],
      ],

      // Double colons work
      [
        'haystack' => '::2:3:4:5:6:7:8',
        'output' => ['0:2:3:4:5:6:7:8'],
      ],
      [
        'haystack' => '::3:4:5:6:7:8',
        'output' => ['::3:4:5:6:7:8'],
      ],
      [
        'haystack' => '::4:5:6:7:8',
        'output' => ['::4:5:6:7:8'],
      ],
      // TODO: this next line should actually be ['haystack' => '1::3:4:5:6:7:8', 'output' => '1:0:3:4:5:6:7:8'],
      // We are not transforming to the preferred format here.
      [
        'haystack' => '1::3:4:5:6:7:8',
        'output' => ['1::3:4:5:6:7:8'],
      ],
      [
        'haystack' => '1:2:3:4:5:6:7:8',
        'output' => ['1:2:3:4:5:6:7:8'],
      ],
      [
        'haystack' => '1:0:3:4:5:6:7:8',
        'output' => ['1:0:3:4:5:6:7:8'],
      ],
      [
        'haystack' => '2001:4860:4860::8888',
        'output' => ['2001:4860:4860::8888'],
      ],

      // :0: doesn't get converted to a double delim when there is already a double delim
      [
        'haystack' => '2620:0:2d0:200::7',
        'output' => ['2620:0:2d0:200::7'],
      ],
      [
        'haystack' => '2620:0:2d0:200::',
        'output' => ['2620:0:2d0:200::'],
      ],
    ];
  }
}
