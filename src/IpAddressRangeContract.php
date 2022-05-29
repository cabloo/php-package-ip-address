<?php

namespace Cabloo\IpAddress;

interface IpAddressRangeContract {
  /**
   * Get the start IP Address in the range.
   */
  public function start(): IpAddressContract;

  /**
   * Get the start IP Address in the range.
   */
  public function end(): IpAddressContract;

  /**
   * Convert the Ip Adrress Range to a string that describes the range.
   *
   * @return string
   */
  public function __toString(): string;
}
