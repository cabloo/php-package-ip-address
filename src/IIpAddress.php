<?php

namespace Cabloo\IpAddress;

interface IIpAddress extends IIpAddressRange {
  /**
   * Get the Binary representation of the Ip Address.
   */
  public function binary(): string;

  /**
   * Get the Hexadecimal representation of the Ip Address.
   */
  public function hex(): string;

  /**
   * Get the split up list of parts as strings.
   * 
   * @return string[]
   */
  public function parts(): array;

  /**
   * Get the integer representation of the IP Address as a bigint string.
   */
  public function long(): string;
}
