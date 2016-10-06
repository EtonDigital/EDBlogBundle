<?php

namespace  ED\BlogBundle\Util;

use ED\BlogBundle\Util\EDEncryption;

class IDEncrypt {

    /**
     * EDEncryption class.
     *
     * @var EDEncryption
     */
    private static $_encryption;

    /**
     * Encryption.
     *
     * @param int $id Encryption text
     *
     * @return string
     */
    public static function encrypt($id)
    {
        return self::_getEDEncryption()->encode($id);
    }

    /**
     * Get EDEncryption.
     *
     * @return EDEncryption
     */
    private static function _getEDEncryption()
    {
        if (!self::$_encryption instanceof EDEncryption) {
            self::$_encryption = new EDEncryption();
        }

        return self::$_encryption;
    }

    /**
     * Decryption.
     *
     * @param string $encrypted Decryption text
     *
     * @return string|array
     */
    public static function decrypt($encrypted)
    {
        if (!is_array($encrypted)) {
            return self::_getEDEncryption()->decode($encrypted);
        }
        $val = array();
        foreach ($encrypted as $encryptedId) {
            $val[] = self::_getEDEncryption()->decode($encryptedId);
        }

        return $val;
    }

}
