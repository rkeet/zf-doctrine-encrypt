<?php

namespace Keet\Encrypt\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * The below register the class as to be used as Doctrine's Annotation and only on properties.
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Hashed
{
    /**
     * @var string linked property which implements \Encrypt\Interfaces\SaltInterface
     */
    public $salt;

    /**
     * @return null|string
     */
    public function getSalt() : ?string
    {
        return $this->salt;
    }

    /**
     * @param null|string $salt
     *
     * @return Hashed
     */
    public function setSalt(?string $salt) : Hashed
    {
        $this->salt = $salt;

        return $this;
    }
}