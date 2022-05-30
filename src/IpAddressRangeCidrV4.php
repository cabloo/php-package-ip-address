<?php

namespace Cabloo\IpAddress;

class IpAddressRangeCidrV4 implements IIpAddressRange {
  /**
   * The delimiting character between the IP Address and the mask.
   */
  const MASK_DELIM = '/';

  /**
   * The allowed CIDR mask range.
   */
  const CIDR_MIN = 1;

  const CIDR_MAX = 32;

  /**
   * The CIDR Mask.
   *
   * @var int
   */
  private $mask;

  /**
   * The IpAddress at the start of the range.
   *
   * @var IIpAddress
   */
  private $start;

  /**
   * The IpAddress at the end of the range.
   *
   * @var IIpAddress
   */
  private $end;

  /**
   * Create the IpAddress instance.
   *
   * @param string $addr
   *
   * @throws InvalidIpAddress
   */
  public function __construct(string $addr) {
    if (strpos($addr, '/') === false) {
      throw new InvalidIpAddress(
        $addr,
        'CIDR masks must come in the form 1.0.0.0/30'
      );
    }
    list($addrNoMask, $mask) = explode(static::MASK_DELIM, $addr);

    $this->mask = (int) $mask;
    if ($this->mask < static::CIDR_MIN || $this->mask > static::CIDR_MAX) {
      throw new InvalidIpAddress($addr, 'Invalid mask');
    }

    $inputLong = (new IpAddressV4($addrNoMask))->asLongInt();
    $this->start = new IpAddressV4(long2ip($inputLong & $this->netmask()));
    $this->end = new IpAddressV4(
      long2ip($this->start->asLongInt() + $this->count() - 1)
    );
  }

  /**
   * Get the netmask as a long int.
   */
  public function netmask(): int {
    return -1 << static::CIDR_MAX - $this->mask;
  }

  /**
   * Number of addresses in the range.
   */
  public function count(): int {
    return pow(2, static::CIDR_MAX - $this->mask);
  }

  public function cidrMask(): int {
    return $this->mask;
  }

  /**
   * Get the start IP Address in the range.
   */
  public function start(): IIpAddress {
    return $this->start;
  }

  /**
   * Get the start IP Address in the range.
   */
  public function end(): IIpAddress {
    return $this->end;
  }

  public static function fromAddressAndMask(
    IIpAddress $address,
    IIpAddress $subnetMask
  ): self {
    $long = $subnetMask->long();
    $base = ip2long('255.255.255.255');
    $mask = 32 - (int) log(($long ^ $base) + 1, 2);

    return new static(sprintf('%s/%d', $address, $mask));
  }

  /**
   * Convert the IP Address to its string representation.
   */
  public function __toString(): string {
    return implode('', [$this->start(), static::MASK_DELIM, $this->mask]);
  }
}
