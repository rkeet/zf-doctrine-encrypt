<?php

namespace Keet\Encrypt\Result;

class EncryptionStorage
{
    /**
     * @var string
     */
    private $encryptedText;

    /**
     * @var array
     */
    private $blindIndexes;

    /**
     * EncryptionStorageResult constructor.
     *
     * @param string $encryptedText
     * @param array  $blindIndexes
     */
    public function __construct(string $encryptedText, array $blindIndexes)
    {
        $this->encryptedText = $encryptedText;
        $this->blindIndexes  = $blindIndexes;
    }

    /**
     * @return string
     */
    public function getEncryptedText(): string
    {
        return $this->encryptedText;
    }

    /**
     * @param string $encryptedText
     *
     * @return EncryptionStorage
     */
    public function setEncryptedText(string $encryptedText): EncryptionStorage
    {
        $this->encryptedText = $encryptedText;
        return $this;
    }

    /**
     * @return array
     */
    public function getBlindIndexes(): array
    {
        return $this->blindIndexes;
    }

    /**
     * @param array $blindIndexes
     *
     * @return EncryptionStorage
     */
    public function setBlindIndexes(array $blindIndexes): EncryptionStorage
    {
        $this->blindIndexes = $blindIndexes;
        return $this;
    }

    /**
     * @param string $blindIndexName
     *
     * @return string|null
     */
    public function getBlindIndexValue(string $blindIndexName): ?string
    {
        return isset($this->getBlindIndexes()[$blindIndexName]) ? $this->getBlindIndexes()[$blindIndexName]['value'] : null;
    }

}