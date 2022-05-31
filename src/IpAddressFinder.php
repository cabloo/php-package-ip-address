<?php

namespace Cabloo\IpAddress;

class IpAddressFinder {
  /**
   * Map regexes for finding IP addresses to the representative classes.
   * They are searched in the order they appear in the array below.
   *
   * @return \Closure[]
   */
  private static function ipFinders(): array {
    return [
      # IPv4 + CIDR Mask
      '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/\d{1,2}/' => function (
        string $address
      ): IIpAddressRange {
        return new IpAddressRangeCidrV4($address);
      },
      # Regular IPv4 (No CIDR Mask)
      '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/' => function (
        string $address
      ): IIpAddressRange {
        return new IpAddressV4($address);
      },
      # IPv6 (No CIDR Mask)
      # We intentionally choose not to support '::' as it is too likely to be falsely reported as an IP in regular text.
      # The IP must start with either a part or :: and then a part. Starting with a single : is invalid.
      # Then, there are up to 7 parts following the first.
      '/' .
      '(' .
      IpAddressV6::REGEX_PART .
      IpAddressV6::REGEX_DELIM .
      '|::' .
      IpAddressV6::REGEX_PART .
      '(' .
      IpAddressV6::REGEX_DELIM .
      ')?)' .
      '(' .
      IpAddressV6::REGEX_PART .
      IpAddressV6::REGEX_DELIM .
      '){0,6}' .
      '(' .
      IpAddressV6::REGEX_PART .
      ')?' .
      '/' => function (string $address): IIpAddressRange {
        return new IpAddressV6($address);
      },
    ];
  }

  /**
   * Get a list of IP Address objects from a set of strings
   *
   * @return IIpAddressRange[]
   */
  public function find(string ...$haystacks): array {
    $result = [];
    foreach ($this->ipFinders() as $regex => $callback) {
      foreach ($haystacks as $haystack) {
        $matches = [];
        preg_match_all($regex, $haystack, $matches);

        foreach ($matches[0] as $match) {
          try {
            $addr = $callback($match);
            foreach ($this->nonOverlappingPart($addr, ...$result) as $part) {
              $result[] = $part;
            }
          } catch (InvalidIpAddress $exc) {
            // probably just a false alarm, e.g. 999.999.999.999
          }
        }
      }
    }

    return $result;
  }

  /** @return (?IIpAddressRange)[] */
  private function nonOverlappingPart(
    IIpAddressRange $addr1,
    IIpAddressRange ...$addresses
  ): array {
    $comparator = new IpAddressRangeComparator($addr1);
    // TODO: This is not the actual intended behavior, since we really want subtract.
    foreach ($addresses as $addr2) {
      if ($comparator->overlapsRange($addr2)) {
        return [];
      }
    }
    return [$addr1];
  }
}
