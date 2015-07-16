<?php

namespace  ED\BlogBundle\Util;

use ED\BlogBundle\Util\EDEncryption;

class IDEncrypt {

  private static $EDEncr;

  private static function getEDEncr() {
    if (!self::$EDEncr instanceof EDEncryption) {
      self::$EDEncr = new EDEncryption();
    }
    return self::$EDEncr;
  }

  /**
   * @param  raw int
   * @return encrypted string 
   */
  public static function encrypt($id) {
    return self::getEDEncr()->encode($id);
  }

  /**
   * @param  raw int
   * @return decrypted string 
   */
  public static function decrypt($encrypted) {
    return self::getEDEncr()->decode($encrypted);
  }

}
