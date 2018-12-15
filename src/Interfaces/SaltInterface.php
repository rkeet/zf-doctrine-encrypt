<?php

namespace Keet\Encrypt\Interfaces;

interface SaltInterface
{
    /**
     * @return string
     */
    public function getSalt() : string;

    /**
     * @param string $salt
     *
     * @return SaltInterface
     */
    public function setSalt(string $salt) : SaltInterface;
}