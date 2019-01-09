<?php

namespace Keet\Encrypt\Adapter;

use Keet\Encrypt\Interfaces\EncryptionInterface;
use Keet\Encrypt\Result\EncryptionStorage;
use ParagonIE\CipherSweet\Backend\ModernCrypto;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\EncryptedField;
use ParagonIE\CipherSweet\KeyProvider\StringProvider;
use ParagonIE\CipherSweet\Transformation\Lowercase;
use ParagonIE\ConstantTime\Binary;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;

class EncryptionAdapter implements EncryptionInterface
{
    public const FILTER_BITS_VALUE    = 32;
    public const TRANSFORMATION_CLASS = Lowercase::class;

    /**
     * @var EncryptionKey
     */
    private $key;

    /**
     * @var CipherSweet
     */
    private $engine;

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

        $provider = new StringProvider(
            new ModernCrypto(),
            $this->getKey()->getRawKeyMaterial()
        );

        $this->engine = new CipherSweet($provider);
    }

    /**
     * @param $tableName
     * @param $colName
     *
     * @return EncryptedField
     */
    private function getEncryptedField($tableName = "", $colName = ""): EncryptedField
    {
        $transformationClass = self::TRANSFORMATION_CLASS;
        return (new EncryptedField($this->engine, $tableName, $colName))->addBlindIndex(
            new BlindIndex(
                $this->getBlindIndexName($tableName, $colName),
                [new $transformationClass],
                self::FILTER_BITS_VALUE
            )
        );
    }

    /**
     * @param string $data
     * @param string $tableName
     * @param string $colName
     *
     * @return EncryptionStorage
     */
    public function prepareForStorage(string $data, string $tableName = "", string $colName = ""): EncryptionStorage
    {
        [$encryptedText, $blindIndexes] = $this->getEncryptedField($tableName, $colName)->prepareForStorage($data);
        return new EncryptionStorage($encryptedText, $blindIndexes);
    }

    /**
     * @param string $data
     * @param string $tableName
     * @param string $colName
     *
     * @return string
     */
    public function encrypt(string $data, string $tableName = "", string $colName = ""): string
    {
        return $this->getEncryptedField($tableName, $colName)->encryptValue($data);
    }

    /**
     * @param string $data
     * @param string $tableName
     * @param string $colName
     *
     * @return string
     */
    public function decrypt(string $data, string $tableName = "", string $colName = ""): string
    {
        return $this->getEncryptedField($tableName, $colName)->decryptValue($data);
    }

    /**
     * @param string $data
     * @param string $tableName
     * @param string $colName
     *
     * @return array
     */
    public function getBlindIndex(string $data, string $tableName = "", string $colName = ""): array
    {
        return $this->getEncryptedField($tableName, $colName)->getBlindIndex($data, $this->getBlindIndexName($tableName, $colName));
    }

    /**
     * @param string $tableName
     * @param string $colName
     *
     * @return string
     */
    public function getBlindIndexName($tableName = "", $colName = ""): string
    {
        return sprintf('%s_%s', $tableName, $colName);
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