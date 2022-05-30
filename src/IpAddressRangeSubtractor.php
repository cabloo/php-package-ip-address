<?php

namespace Cabloo\IpAddress;

class IpAddressRangeSubtractor {
  /**
   * @param IIpAddressRange $remove
   * @param IIpAddressRange $from
   *
   * @return IIpAddressRange[] Returs empty array if $remove covers all of $from
   */
  public function subtract(
    IIpAddressRange $remove,
    IIpAddressRange $from
  ): array {
    $results = [];
    $incrementer = new IpAddressIncrementer();
    $beforeRemoveStart = $incrementer->decrement($remove->start());
    $afterRemoveEnd = $incrementer->increment($remove->end());
    $fromCompare = new IpAddressRangeComparator($from);

    // If the ranges do not overlap, return the original.
    if (
      !$fromCompare->overlapsRange(
        new IpAddressRange($beforeRemoveStart, $afterRemoveEnd)
      )
    ) {
      return [$from];
    }

    if ($fromCompare->overlapsRange($beforeRemoveStart)) {
      $results[] = new IpAddressRange($from->start(), $beforeRemoveStart);
    }

    if ($fromCompare->overlapsRange($afterRemoveEnd)) {
      $results[] = new IpAddressRange($afterRemoveEnd, $from->end());
    }

    return $results;
  }
}
