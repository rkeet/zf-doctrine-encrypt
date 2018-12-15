<?php

namespace Keet\Encrypt\Options;

use Doctrine\Common\Annotations\Reader;
use Keet\Encrypt\Interfaces\EncryptionInterface;
use Zend\Stdlib\AbstractOptions;

class EncryptionOptions extends AbstractOptions
{
    /**
     * @var Reader|string
     */
    protected $reader;

    /**
     * @var EncryptionInterface|string
     */
    protected $adapter;

    /**
     * @var string
     */
    private $key;

    /**
     * @return Reader|string
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @param Reader|string $reader
     *
     * @return EncryptionOptions
     */
    public function setReader($reader) : EncryptionOptions
    {
        $this->reader = $reader;

        return $this;
    }

    /**
     * @return EncryptionInterface|string
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param EncryptionInterface|string $adapter
     *
     * @return EncryptionOptions
     */
    public function setAdapter($adapter) : EncryptionOptions
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return EncryptionOptions
     */
    public function setKey(string $key) : EncryptionOptions
    {
        $this->key = $key;

        return $this;
    }
}