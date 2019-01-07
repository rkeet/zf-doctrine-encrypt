<?php

namespace Keet\Encrypt\Service;

use Keet\Encrypt\Interfaces\EncryptionInterface;

class EncryptionService
{
    /**
     * @var EncryptionInterface
     */
    protected $adapter;

    /**
     * EncryptionService constructor.
     *
     * @param EncryptionInterface $adapter
     */
    public function __construct(EncryptionInterface $adapter)
    {
        $this->setAdapter($adapter);
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
        return $this->getAdapter()->encrypt($data, $tableName, $colName);
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
        return $this->getAdapter()->decrypt($data, $tableName, $colName);
    }

    /**
     * @param        $data
     * @param string $tableName
     * @param string $colName
     *
     * @return array
     */
    public function getBlindIndex(string $data, string $tableName = "", string $colName = ""): array
    {
        return $this->getAdapter()->getBlindIndex($data, $tableName, $colName);
    }

    /**
     * @return EncryptionInterface
     */
    protected function getAdapter() : EncryptionInterface
    {
        return $this->adapter;
    }

    /**
     * @param EncryptionInterface $adapter
     *
     * @return EncryptionService
     */
    protected function setAdapter(EncryptionInterface $adapter) : EncryptionService
    {
        $this->adapter = $adapter;

        return $this;
    }

}