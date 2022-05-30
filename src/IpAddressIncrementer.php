<?php

namespace Cabloo\IpAddress;

class IpAddressIncrementer {
  /**
   * @param IIpAddressRange $range
   * @param int                    $limit
   *
   * @return IIpAddress[]
   */
  public function range(IIpAddressRange $range, int $limit): array {
    $result = [];
    $current = $range->start();
    $end = new IpAddressComparator($range->end());
    for ($i = 0; $i < $limit && $end->greaterThanOrEqualTo($current); $i++) {
      $result[] = $current;
      $current = $this->increment($current);
    }

    return $result;
  }

  public function increment(IIpAddress $address, int $amount = 1): IIpAddress {
    return new $address(
      inet_ntop($this->binaryIncrement($address->binary(), $amount))
    );
  }

  private function binaryIncrement(
    string $binaryIP,
    int $increment = 1
  ): string {
    // copied from: https://stackoverflow.com/a/33125642/1190975
    // inet_pton creates values where each "character" is one ip-address-byte
    // we are splitting the string so we can handle every byte on its own.
    $binaryIpArrayIn = str_split($binaryIP);
    $binaryIpArrayOut = [];
    $carry = 0 + $increment;
    // reverse array because our following addition is done from right to left.
    foreach (array_reverse($binaryIpArrayIn) as $binaryByte) {
      // transforming on byte from our ip address to decimal
      $decIp = hexdec(bin2hex($binaryByte));
      $tempValue = $decIp + $carry;
      $tempValueHex = dechex($tempValue);
      // check if we have to deal with a carry
      if (strlen($tempValueHex) > 2) {
        // split $tempValueHex in carry and result
        // str_pad because hex2bin only accepts even character counts
        $carryHex = str_pad(substr($tempValueHex, 0, 1), 2, '0', STR_PAD_LEFT);
        $tempResultHex = str_pad(
          substr($tempValueHex, 1, 2),
          2,
          '0',
          STR_PAD_LEFT
        );
        $carry = hexdec($carryHex);
      } else {
        $carry = 0;
        $tempResultHex = str_pad($tempValueHex, 2, '0', STR_PAD_LEFT);
      }
      // fill our result array
      $binaryIpArrayOut[] = hex2bin($tempResultHex);
    }

    // We have to reverse our array back to normal order and building a string
    return implode('', array_reverse($binaryIpArrayOut));
  }

  public function decrement(IIpAddress $address) {
    return $this->increment($address, -1);
  }

  public static function dec(IIpAddress $address) {
    $inst = new static();
    return $inst->decrement($address);
  }
  public static function inc(IIpAddress $address) {
    $inst = new static();
    return $inst->increment($address);
  }
}
