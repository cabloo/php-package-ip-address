<?php

namespace Cabloo\IpAddress;

class IpAddressFactory {
  /**
   * Generate an IP Address range from the given start and end addresses.
   */
  public function range(IIpAddress $start, IIpAddress $end): IpAddressRange {
    return new IpAddressRange($start, $end);
  }

  /**
   * Generate an IPv4 Address range from the given start and end partial addresses.
   */
  public function partialRange(string $start, string $end): IIpAddressRange {
    return $this->range(
      $this->makeFromPartialIPv4($start),
      $this->makeFromPartialIPv4($end)
    );
  }

  /**
   * Get a list of IP Address objects from a set of strings
   *
   * @return IIpAddressRange[]
   */
  public function find(string ...$haystacks): array {
    return (new IpAddressFinder())->find(...$haystacks);
  }

  /**
   * Convert the given string to an IpAddress.
   */
  public function make(string $addr): ?IIpAddress {
    try {
      return new IpAddressV4($addr);
    } catch (InvalidIpAddress $exc) {
    } catch (PartialIpAddress $exc) {
    }

    try {
      return new IpAddressV6($addr);
    } catch (InvalidIpAddress $exc) {
    } catch (PartialIpAddress $exc) {
    }

    return null;
  }

  /**
   * @throws InvalidIpAddress
   */
  public function makeEnforce(string $addr): IIpAddress {
    $result = $this->make($addr);

    if ($result !== null) {
      return $result;
    }

    throw new InvalidIpAddress($addr, 'Not a valid IPv4 or IPv6 address');
  }

  /**
   * Make the given string addr, even if it's incomplete. E.g. 127.0.0 -> 127.0.0.0/24
   */
  public function makeFromPartialIPv4(string $ipv4): ?IpAddressV4 {
    try {
      return new IpAddressV4($ipv4);
    } catch (PartialIpAddress $except) {
      // Partial IP Addresses are fine.
      return $except->partial();
    } catch (InvalidIpAddress $except) {
      // Invalid IP Addresses are not.
      return null;
    }
  }

  /**
   * All the IP Address ranges that could match the given string input.
   *
   * @param string $string the possibly incomplete IP Address
   *
   * @return IIpAddressRange[]
   */
  public function all(string $string): array {
    // Attempt to match valid IPs.
    $list = $this->find($string);

    // Attempt to match partial IPs.
    if (!($addr = $this->makeFromPartialIPv4($string))) {
      return $list;
    }

    $parts = $addr->parts();
    if (!count($parts)) {
      return $list;
    }

    // Take the last part of the IP Address and expand other ranges.
    $delim = $addr->delim();
    $partMax = 255;
    $partMin = 0;
    $lastPart = array_pop($parts);
    $firstParts = implode($delim, $parts) . $delim;

    $lastPartLength = max(2 - count($parts), 0);
    $lastPartsStart = str_repeat($delim . $partMin, $lastPartLength);
    $lastPartsEnd = str_repeat($delim . $partMax, $lastPartLength);

    // TODO: handle case with starting zero
    if ($lastPart === null) {
      $list[] = $this->partialRange(
        $firstParts . $partMin . $lastPartsStart,
        $firstParts . $partMax . $lastPartsEnd
      );
    }

    if (count($parts) < 3) {
      $list[] = $this->partialRange(
        $firstParts . $lastPart . $lastPartsStart,
        $firstParts . $lastPart . $lastPartsEnd
      );
    }

    $mult = 1;
    while ($lastPart && ($lastPart *= 10) <= $partMax) {
      $endRangeLastPart = $lastPart + 9 * $mult;
      $endRangeLastPart = min($endRangeLastPart, $partMax);
      $mult += 10;
      $list[] = $this->partialRange(
        $firstParts . $lastPart . $lastPartsStart,
        $firstParts . $endRangeLastPart . $lastPartsEnd
      );
    }

    return $list;
  }
}
