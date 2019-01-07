<?php

namespace Keet\Encrypt\Interfaces;

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
     * @return array
     */
    public function prepareForStorage(string $data, string $tableName = "", string $colName = ""): array;

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