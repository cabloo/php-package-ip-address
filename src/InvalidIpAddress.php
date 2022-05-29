<?php

namespace Cabloo\IpAddress;

class InvalidIpAddress extends \Exception {
  public function __construct(string $input, string $reason = '') {
    parent::__construct(
      sprintf("Invalid IP Address: %s %s", $input, $reason ? "($reason)" : '')
    );
  }
}
