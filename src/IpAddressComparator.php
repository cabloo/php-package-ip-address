<?php

namespace Cabloo\IpAddress;

class IpAddressComparator {
  /**
   * @var IIpAddress
   */
  private $address;

  public function __construct(IIpAddress $address) {
    $this->address = $address;
  }

  public static function maxOf(IIpAddress ...$addresses): ?IIpAddress {
    return array_reduce($addresses, function (
      ?IIpAddress $max,
      IIpAddress $address
    ): IIpAddress {
      if (!$max) {
        return $address;
      }

      $comparator = new IpAddressComparator($address);

      return $comparator->max($max);
    });
  }

  public function max(IIpAddress $address): IIpAddress {
    return $this->greaterThan($address) ? $this->address : $address;
  }

  public function greaterThan(IIpAddress $address): bool {
    return $this->address->binary() > $address->binary();
  }

  public static function minOf(IIpAddress ...$addresses): ?IIpAddress {
    return array_reduce($addresses, function (
      ?IIpAddress $max,
      IIpAddress $address
    ): ?IIpAddress {
      if (!$max) {
        return $address;
      }

      $comparator = new IpAddressComparator($address);

      return $comparator->min($max);
    });
  }

  public function min(IIpAddress $address): IIpAddress {
    return $this->lessThan($address) ? $this->address : $address;
  }

  public function lessThan(IIpAddress $address): bool {
    return $this->address->binary() < $address->binary();
  }

  public static function gt(IIpAddress $addr1, IIpAddress $addr2): bool {
    $inst = new static($addr1);

    return $inst->greaterThan($addr2);
  }

  public static function lt(IIpAddress $addr1, IIpAddress $addr2): bool {
    $inst = new static($addr1);

    return $inst->lessThan($addr2);
  }

  public static function eq(IIpAddress $addr1, IIpAddress $addr2): bool {
    $inst = new static($addr1);

    return $inst->equalTo($addr2);
  }

  public function greaterThanOrEqualTo(IIpAddress $address): bool {
    return $this->address->binary() >= $address->binary();
  }

  public function lessThanOrEqualTo(IIpAddress $address): bool {
    return $this->address->binary() <= $address->binary();
  }

  public function equalTo(IIpAddress $address): bool {
    return $this->address->binary() === $address->binary();
  }
}
