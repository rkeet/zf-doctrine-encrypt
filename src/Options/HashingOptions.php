<?php

namespace Keet\Encrypt\Options;

use Doctrine\Common\Annotations\Reader;
use Keet\Encrypt\Interfaces\HashingInterface;
use Zend\Stdlib\AbstractOptions;

class HashingOptions extends AbstractOptions
{
    /**
     * @var Reader|string
     */
    protected $reader;

    /**
     * @var HashingInterface|string
     */
    protected $adapter;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $pepper;

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
     * @return HashingOptions
     */
    public function setReader($reader) : HashingOptions
    {
        $this->reader = $reader;

        return $this;
    }

    /**
     * @return HashingInterface|string
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param HashingInterface|string $adapter
     *
     * @return HashingOptions
     */
    public function setAdapter($adapter) : HashingOptions
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
     * @return HashingOptions
     */
    public function setKey(string $key) : HashingOptions
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
     * @return HashingOptions
     */
    public function setPepper(string $pepper) : HashingOptions
    {
        $this->pepper = $pepper;

        return $this;
    }
}