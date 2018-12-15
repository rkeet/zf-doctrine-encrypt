<?php

namespace Keet\Encrypt\Service;

use Keet\Encrypt\Interfaces\HashingInterface;

class HashingService
{
    /**
     * @var HashingInterface
     */
    protected $adapter;

    /**
     * HashingService constructor.
     *
     * @param HashingInterface $adapter
     */
    public function __construct(HashingInterface $adapter)
    {
        $this->setAdapter($adapter);
    }

    /**
     * @param string $password
     *
     * @return string
     */
    public function hash(string $password)
    {
        return $this->getAdapter()->hash($password);
    }

    /**
     * @param string $string
     * @param string $storedString
     *
     * @return bool
     */
    public function verify(string $string, string $storedString) : bool
    {
        return $this->getAdapter()->verify($string, $storedString);
    }

    /**
     * @return HashingInterface
     */
    protected function getAdapter() : HashingInterface
    {
        return $this->adapter;
    }

    /**
     * @param HashingInterface $adapter
     *
     * @return HashingService
     */
    protected function setAdapter(HashingInterface $adapter) : HashingService
    {
        $this->adapter = $adapter;

        return $this;
    }

}