<?php

namespace  ED\BlogBundle\Util;

class EDEncryption {

  const SALT = 'coin2014';
  
  private $salt;

  public function safe_b64encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
    return $data;
  }

  public function safe_b64decode($string) {
    $data = str_replace(array('-', '_'), array('+', '/'), $string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
      $data .= substr('====', $mod4);
    }
    return base64_decode($data);
  }

  /**
   * @param  raw int
   * @return encrypted string 
   */
  public function encode($value) {

    if (!$value) {
      return false;
    }
    $this->salt = self::SALT ? self::SALT : '';
    $text = $value;
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->salt, $text, MCRYPT_MODE_ECB, $iv);
    return trim($this->safe_b64encode($crypttext));
  }

  /**
   * @param  raw int
   * @return decrypted string 
   */
  public function decode($value) {

    if (!$value) {
      return false;
    }
    $this->salt = self::SALT ? self::SALT : '';
    $crypttext = $this->safe_b64decode($value);
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->salt, $crypttext, MCRYPT_MODE_ECB, $iv);
    return trim($decrypttext);
  }

}

?>