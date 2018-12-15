<?php

namespace Encrypt\Factory\Service;

use Encrypt\Interfaces\EncryptionInterface;
use Encrypt\Service\EncryptionService;
use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class EncryptionServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return EncryptionService|object
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');

        if ( ! isset($config['doctrine']['encryption']['orm_default'])) {
            throw new Exception(
                sprintf('Could not find encryption config in %s to create %s.', __CLASS__, EncryptionService::class)
            );
        }

        /** @var EncryptionInterface $adapter */
        $adapter = $container->build(
            'encryption_adapter',
            [
                'key' => $config['doctrine']['encryption']['orm_default']['key'],
            ]
        );

        return new EncryptionService($adapter);
    }
}