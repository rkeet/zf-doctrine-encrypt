<?php

namespace Encrypt\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * The below register the class as to be used as Doctrine's Annotation and only on properties.
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Encrypted
{
    /**
     * @var string type that the encrypted/decrypted string should be after decryption
     */
    public $type = 'string';

    /**
     * @return null|string
     */
    public function getType() : ?string
    {
        return $this->type;
    }

    /**
     * @param null|string $type
     *
     * @return Encrypted
     */
    public function setType(string $type) : Encrypted
    {
        $this->type = $type;

        return $this;
    }
}