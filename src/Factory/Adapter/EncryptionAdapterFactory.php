<?php

namespace Keet\Encrypt\Factory\Adapter;

use Keet\Encrypt\Adapter\EncryptionAdapter;
use Keet\Encrypt\Exception\OptionsNotFoundException;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class EncryptionAdapterFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return EncryptionAdapter
     * @throws OptionsNotFoundException
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if ( ! is_array($options) || empty($options)) {
            throw new OptionsNotFoundException('Options required to be set in the config for HaliteAdapter are "key".');
        }

        if ( ! key_exists('key', $options) && ! is_string($options['key'])) {
            throw new OptionsNotFoundException('Option "key" is required.');
        }

        return new EncryptionAdapter($options['key']);
    }
}