<?php

namespace Cabloo\IpAddress;

class IpAddressRange implements IIpAddressRange {
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
   * Initialize an IP Address.
   *
   * @param IIpAddress $start
   * @param IIpAddress $end
   */
  public function __construct(
    IIpAddress $start,
    IIpAddress $end
  ) {
    $this->end = $end;
    $this->start = $start;

    if (IpAddressComparator::lt($this->end, $this->start)) {
      throw new InvalidIpAddress(
        'Invalid range: end < start: ' . (string) $this
      );
    }
  }

  /**
   * Get the IpAddress at the start of the range.
   */
  public function start(): IIpAddress {
    return $this->start;
  }

  /**
   * Get the IpAddress at the end of the range.
   */
  public function end(): IIpAddress {
    return $this->end;
  }

  /**
   * Convert the Ip Adrress Range to a string that describes the range.
   *
   * @return string
   */
  public function __toString() {
    if ((string) $this->start === (string) $this->end) {
      return (string) $this->start;
    }

    // TODO: remove similar parts.
    return $this->start . " - " . $this->end;
  }
}
