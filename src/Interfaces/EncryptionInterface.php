<?php

namespace Keet\Encrypt\Interfaces;

use Keet\Encrypt\Result\EncryptionStorage;

interface EncryptionInterface
{
    /**
     * @param string $data
     * @param string $tableName
     * @param string $colName
     *
     * @return string
     */
    public function encrypt(string $data, string $tableName = "", string $colName = ""): string;

    /**
     * @param string $data
     * @param string $tableName
     * @param string $colName
     *
     * @return EncryptionStorage
     */
    public function prepareForStorage(string $data, string $tableName = "", string $colName = ""): EncryptionStorage;

    /**
     * @param string $data
     * @param string $tableName
     * @param string $colName
     *
     * @return string
     */
    public function decrypt(string $data, string $tableName = "", string $colName = ""): string;

    /**
     * @param string $data
     * @param string $tableName
     * @param string $colName
     *
     * @return array
     */
    public function getBlindIndex(string $data, string $tableName = "", string $colName = ""): array;

    /**
     * @param string $tableName
     * @param string $colName
     *
     * @return string
     */
    public function getBlindIndexName($tableName = "", $colName = ""): string;
}