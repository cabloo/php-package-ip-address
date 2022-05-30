<?php

namespace Cabloo\IpAddress;

class IpAddressRangeComparator {
  /**
   * @var IIpAddressRange
   */
  private $range;

  /**
   * IpAddressComparator constructor.
   *
   * @param IIpAddressRange $range
   */
  public function __construct(IIpAddressRange $range) {
    $this->range = $range;
  }

  public static function getRangeThatCoversAllRanges(
    IIpAddressRange ...$ranges
  ): IIpAddressRange {
    $firstStart = IpAddressComparator::minOf(
      ...array_map(function (IIpAddressRange $range) {
        return $range->start();
      }, $ranges)
    );
    $lastEnd = IpAddressComparator::maxOf(
      ...array_map(function (IIpAddressRange $range) {
        return $range->end();
      }, $ranges)
    );
    return new IpAddressRange($firstStart, $lastEnd);
  }

  public function fullyContainsRange(IIpAddressRange $range): bool {
    list($start, $end) = $this->startAndEndComparator();
    return $start->lessThanOrEqualTo($range->start()) &&
      $end->greaterThanOrEqualTo($range->end());
  }

  public function overlapsRange(IIpAddressRange $range): bool {
    list($start, $end) = $this->startAndEndComparator();

    return $start->lessThanOrEqualTo($range->end()) &&
      $end->greaterThanOrEqualTo($range->start());
  }

  /**
   * @return IpAddressComparator[]
   */
  private function startAndEndComparator(): array {
    return [
      new IpAddressComparator($this->range->start()),
      new IpAddressComparator($this->range->end()),
    ];
  }

  public function equals(IIpAddressRange $range) {
    list($start, $end) = $this->startAndEndComparator();

    return $start->equalTo($range->start()) && $end->equalTo($range->end());
  }
}
