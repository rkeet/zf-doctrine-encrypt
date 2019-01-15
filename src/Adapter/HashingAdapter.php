<?php

namespace Keet\Encrypt\Adapter;

use Keet\Encrypt\Interfaces\HashingInterface;
use ParagonIE\ConstantTime\Binary;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\Password;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;
use TypeError;

class HashingAdapter implements HashingInterface
{
    /**
     * @var EncryptionKey
     */
    private $key;

    /**
     * @var string
     */
    private $pepper;

    /**
     * HaliteAdapter constructor.
     *
     * @param $key
     * @param $pepper
     *
     * @throws InvalidKey
     * @throws TypeError
     */
    public function __construct($key, $pepper)
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

        if (Binary::safeStrlen($pepper) !== \Sodium\CRYPTO_STREAM_KEYBYTES) {
            throw new InvalidKey(
                sprintf(
                    'Encryption pepper used for %s::%s must be exactly %s characters long',
                    __CLASS__,
                    __FUNCTION__,
                    \Sodium\CRYPTO_STREAM_KEYBYTES
                )
            );
        }

        $this->setKey(new EncryptionKey(new HiddenString($key)));
        $this->setPepper($pepper);
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
    public function hash(string $data) : string
    {
        return Password::hash(new HiddenString($data . $this->getPepper()), $this->getKey());
    }

    /**
     * @param string $string
     * @param string $storedString
     *
     * @return bool
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidSignature
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     */
    public function verify(string $string, string $storedString) : bool
    {
        return Password::verify(new HiddenString($string . $this->getPepper()), $storedString, $this->getKey());
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
     * @return HashingAdapter
     */
    public function setKey(EncryptionKey $key) : HashingAdapter
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getPepper() : string
    {
        return $this->pepper;
    }

    /**
     * @param string $pepper
     *
     * @return HashingAdapter
     */
    public function setPepper(string $pepper) : HashingAdapter
    {
        $this->pepper = $pepper;

        return $this;
    }
}