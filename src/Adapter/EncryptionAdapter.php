<?php

namespace Keet\Encrypt\Adapter;

use Keet\Encrypt\Interfaces\EncryptionInterface;
use ParagonIE\ConstantTime\Binary;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;

class EncryptionAdapter implements EncryptionInterface
{
    /**
     * @var EncryptionKey
     */
    private $key;

    /**
     * HaliteAdapter constructor.
     *
     * @param $key
     *
     * @throws InvalidKey
     * @throws \TypeError
     */
    public function __construct($key)
    {
        if (Binary::safeStrlen($key) !== \Sodium\CRYPTO_STREAM_KEYBYTES) {
            throw new InvalidKey(
                sprintf(
                    'Encryption key used for %s::%s must be exactly %s characters long',
                    __CLASS__,
                    __FUNCTION__,
                    \Sodium\CRYPTO_STREAM_KEYBYTES
                )
            );
        }

        $this->setKey(new EncryptionKey(new HiddenString($key)));
    }

    /**
     * @param string $data
     *
     * @return string
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     */
    public function encrypt(string $data) : string
    {
        return Crypto::encrypt(new HiddenString($data), $this->getKey());
    }

    /**
     * @param string $data
     *
     * @return HiddenString|string
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidSignature
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     */
    public function decrypt(string $data) : string
    {
        return Crypto::decrypt($data, $this->getKey());
    }

    /**
     * @return EncryptionKey
     */
    public function getKey() : EncryptionKey
    {
        return $this->key;
    }

    /**
     * @param EncryptionKey $key
     *
     * @return EncryptionAdapter
     */
    public function setKey(EncryptionKey $key) : EncryptionAdapter
    {
        $this->key = $key;

        return $this;
    }
}