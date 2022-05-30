<?php

namespace Cabloo\IpAddress;

class IpAddressV6 implements IIpAddress {
  /**
   * A regex matching a single part of the IP address.
   */
  const REGEX_PART = '[0-9a-fA-F]{1,4}';

  /**
   * A regex matching a single delimiter of the IP address.
   */

  const REGEX_DELIM = '::?';

  /**
   * What goes between double delimiters.
   *
   * This is expected to always be empty, this variable is just here for code clarity.
   */
  const OUTPUT_DOUBLE_DELIMED = '';

  /**
   * How a double delimited part is stored in $this->parts array.
   */
  const STORE_DOUBLE_DELIMED = null;

  /**
   * A regex that finds any illegal characters in the string.
   */
  const ILLEGAL_CHARACTER_REGEX = '/[^a-fA-F0-9:]/';

  const MAX_PART_VALUE = 0xffff;
  const MIN_PART_VALUE = 0x0000;
  const MAX_PART_COUNT = 8;
  const MIN_PART_COUNT = 1;

  /** @var (?int)[] */
  private $parts = [];

  /**
   * Initialize an IP Address.
   *
   * @param string|null $input
   *
   * @throws InvalidIpAddress
   * @throws PartialIpAddress
   */
  final public function __construct($input = null) {
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

  private function hasMinPartCount(): bool {
    $min =
      $this->doubleDelimitedIndex() === null
        ? static::MAX_PART_COUNT
        : static::MIN_PART_COUNT;

    return count($this->parts()) >= $min;
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
   *
   * @return string
   */
  public function __toString() {
    return $this->str();
  }

  /**
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
  public function delim(): string {
    return ':';
  }

  /** @return string[] */
  public function parts(): array {
    return array_map(function (?string $part) {
      return $part === self::STORE_DOUBLE_DELIMED
        ? self::OUTPUT_DOUBLE_DELIMED
        : dechex($part);
    }, $this->parts);
  }

  public function long(): string {
    $binaryNum = '';
    foreach (unpack('C*', $this->binary()) as $byte) {
      $binaryNum .= str_pad(decbin($byte), 8, "0", STR_PAD_LEFT);
    }
    $binToInt = base_convert(ltrim($binaryNum, '0'), 2, 10);

    return $binToInt;
  }

  /** @return (?int)[] */
  private function getParts(string $address): array {
    return array_map(function (string $part) use ($address): ?int {
      $asInt =
        $part === self::OUTPUT_DOUBLE_DELIMED
          ? self::STORE_DOUBLE_DELIMED
          : hexdec($part);
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
          $address,
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

  private function correctToPreferredFormat(string $address): string {
    $delim = $this->delim();

    // This shouldn't be possible but when it happens,
    // it breaks everything because it's inside __toString.
    if ($address === "") {
      return "";
    }

    if ($address[0] === $delim && $address[1] !== $delim) {
      $address = $delim . $address;
    }

    // Correct to a preferred format (0: is preferred over :: when the address is a full one).
    // This resolves some issues in dealing with what looks like 9 parts (8 delimiters).
    if (substr_count($address, $delim) === static::MAX_PART_COUNT) {
      $address = str_replace($delim . $delim, $delim . '0', $address);
      if ($address[0] === $delim && $address[1] === '0') {
        $address[0] = '0';
        $address[1] = $delim;
      }
    }

    if (
      $address[strlen($address) - 1] === $delim &&
      substr($address, -2) !== $delim . $delim
    ) {
      $address = rtrim($address, $delim);
    }

    return $address;
  }

  /**
   * @return int|null
   */
  public function doubleDelimitedIndex(): ?int {
    $index = array_search(self::STORE_DOUBLE_DELIMED, $this->parts, true);

    return $index === false ? null : $index;
  }

  /**
   * Convert the Ip Address to its string representation.
   */
  private function str(): string {
    return $this->correctToPreferredFormat(
      implode($this->delim(), $this->parts())
    );
  }

  /**
   * @return string
   */
  public function longName() {
    return trim(
      chunk_split(
        unpack('H*hex', $this->binary())['hex'],
        strlen(dechex(static::MAX_PART_VALUE)),
        $this->delim()
      ),
      $this->delim()
    );
  }
}
