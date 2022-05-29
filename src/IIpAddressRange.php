<?php

namespace Cabloo\IpAddress;

interface IIpAddressRange {
  /**
   * Get the start IP Address in the range.
   */
  public function start(): IIpAddress;

  /**
   * Get the start IP Address in the range.
   */
  public function end(): IIpAddress;

  /**
   * Convert the Ip Adrress Range to a string that describes the range.
   *
   * @return string
   */
  public function __toString(): string;
}
