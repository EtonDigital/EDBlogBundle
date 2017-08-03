<?php

namespace  ED\BlogBundle\Util;

class EDEncryption {

    /**
     * Hash random string.
     */
    const HASH = '123f935d99a6d1c6cbda3fe7a1121f218237f53e61a18bf933b240ca18261321';

    /**
     * Crypt method.
     */
    const CRYPT_METHOD = 'AES-256-CBC';

    /**
     * Encoding string.
     *
     * @param string $value String to encode
     *
     * @return string
     */
    public function encode($value)
    {
        if (!$value) {
            return false;
        }

        $iv_size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        $cryptText = openssl_encrypt($value, self::CRYPT_METHOD, self::HASH, 0, $iv);

        return $this->safeB64Encode($iv . $cryptText);
    }

    /**
     * Safe B64 Encode.
     *
     * @param string $string Text to encode
     *
     * @return mixed|string
     */
    public function safeB64Encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);

        return $data;
    }

    /**
     * Decode string.
     *
     * @param string $value String to decode
     *
     * @return string
     */
    public function decode($value)
    {
        if (!$value) {
            return false;
        }

        $cryptText = $this->safeB64Decode($value);

        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = substr($cryptText, 0, $iv_size);

        $decryptText = openssl_decrypt(substr($cryptText, $iv_size), self::CRYPT_METHOD, self::HASH, 0, $iv);

        return trim($decryptText);
    }

    /**
     * Safe B64 Decode.
     *
     * @param string $string String to decode
     *
     * @return string
     */
    public function safeB64Decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }

        return base64_decode($data);
    }
}

?>