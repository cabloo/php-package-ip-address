<?php

namespace Cabloo\IpAddress;

class PartialIpAddress extends InvalidIpAddress {
  /** @var IIpAddress */
  private $partial;

  public function __construct(string $input, IIpAddress $partial) {
    parent::__construct($input, "partial");
    $this->partial = $partial;
  }

  public function partial(): IIpAddress {
    return $this->partial;
  }
}
