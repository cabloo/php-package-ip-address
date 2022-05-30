<?php

namespace Cabloo\IpAddress;

class IpAddressV4 implements IIpAddress {
  const EMPTY_PART = '0';

  /**
   * A regex that finds any illegal characters in the string.
   */
  const ILLEGAL_CHARACTER_REGEX = '/[^0-9.]/';

  const MAX_PART_VALUE = 0xff;
  const MIN_PART_VALUE = 0x00;
  const MAX_PART_COUNT = 4;
  const MIN_PART_COUNT = 4;

  /**
   * The split up parts of the IP Address.
   *
   * @var int[]
   */
  private $parts = [];

  /**
   * @throws InvalidIpAddress
   * @throws PartialIpAddress
   */
  final public function __construct(string $input) {
    if (!$input) {
      return;
    }

    if (preg_match(static::ILLEGAL_CHARACTER_REGEX, $input)) {
      throw new InvalidIpAddress($input, 'contains illegal characters');
    }

    $address = $this->correctToPreferredFormat($input);
    $parts = $this->parts = $this->getParts($address);

    if (count($parts) > static::MAX_PART_COUNT) {
      throw new InvalidIpAddress(
        $input,
        sprintf(
          '%s has too many parts (expected %d, found %d)',
          $input,
          static::MAX_PART_COUNT,
          count($parts)
        )
      );
    }

    if (!$this->hasMinPartCount()) {
      throw new PartialIpAddress($input, $this);
    }
  }

  private function getParts(string $address): array {
    return array_map(function (string $part) use ($address): int {
      $asInt = (int) $part;

      if ((int) $asInt > static::MAX_PART_VALUE) {
        throw new InvalidIpAddress(
          $address,
          sprintf(
            '%d exceeds maximum value of %d',
            $asInt,
            static::MAX_PART_VALUE
          )
        );
      }

      if ((int) $asInt < static::MIN_PART_VALUE) {
        throw new InvalidIpAddress(
          sprintf(
            '%d below minimum value of %d',
            $asInt,
            static::MIN_PART_VALUE
          )
        );
      }

      return $asInt;
    }, explode($this->delim(), $address));
  }

  private function hasMinPartCount(): bool {
    return count($this->parts()) >= static::MIN_PART_COUNT;
  }

  /** @return string[] */
  public function parts(): array {
    return array_map(function (int $part) {
      return (string) $part;
    }, $this->parts);
  }

  /**
   * Returns an array of IP Parts, guaranteed to be static::MIN_PART_COUNT long.
   * This is necessary because this class can also represent a partial IP address (e.g. 127.0)
   */
  private function paddedParts(): array {
    return array_pad(
      $this->parts(),
      static::MIN_PART_COUNT,
      static::EMPTY_PART
    );
  }

  public function start(): IIpAddress {
    return $this;
  }

  public function end(): IIpAddress {
    return $this;
  }

  /**
   * Get the Binary representation of the Ip Address.
   */
  public function binary(): string {
    return inet_pton((string) $this);
  }

  /**
   * Get the Hexadecimal representation of the Ip Address.
   */
  public function hex(): string {
    return bin2hex($this->binary());
  }

  /**
   * Convert the Ip Address to its string representation.
   */
  public function __toString(): string {
    return implode(
      $this->delim(),
      array_map(function ($octet) {
        // Handle case of 0125.0123...
        return ltrim($octet, '0') ?: static::EMPTY_PART;
      }, $this->paddedParts())
    );
  }

  /**
   * @param string $start
   * @param string $end
   *
   * @return IIpAddressRange
   * @throws InvalidIpAddress
   * @throws PartialIpAddress
   */
  public static function range(string $start, string $end): IIpAddressRange {
    return new IpAddressRange(new static($start), new static($end));
  }

  /**
   * The delimiter between parts of the address.
   *
   * @return string
   */
  public function delim() {
    return '.';
  }

  public function correctToPreferredFormat(string $address): string {
    $addr = ltrim($address, '0');
    $addr = str_replace('.0', '.', $addr);
    $addr = str_replace('.0', '.', $addr);
    $addr = str_replace('.0', '.', $addr);
    $addr = str_replace('..', '.0.', $addr);
    $addr = str_replace('..', '.0.', $addr);
    $addr = str_replace('..', '.0.', $addr);
    if ($addr[strlen($addr) - 1] === '.') {
      $addr .= '0';
    }
    if ($addr[0] === '.') {
      $addr = '0' . $addr;
    }

    return $addr;
  }

  /**
   * Get the address as a long integer for numerical operations.
   *
   * @return int
   */
  public function asLongInt(): int {
    return ip2long((string) $this);
  }

  public function long(): string {
    return (string) $this->asLongInt();
  }

  /**
   * @param int $long
   *
   * @return IIpAddress
   * @throws InvalidIpAddress
   * @throws PartialIpAddress
   */
  public static function fromLong(int $long): IIpAddress {
    return new static(long2ip($long));
  }
}
