<?php

namespace Encrypt\Interfaces;

interface EncryptionInterface
{
    /**
     * @param string $data
     *
     * @return string
     */
    public function encrypt(string $data) : string;

    /**
     * @param string $data
     *
     * @return string
     */
    public function decrypt(string $data) : string;
}