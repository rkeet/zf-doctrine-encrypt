<?php

namespace Encrypt\Factory\Service;

use Encrypt\Interfaces\HashingInterface;
use Encrypt\Service\HashingService;
use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class HashingServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return HashingService
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');

        if ( ! isset($config['doctrine']['hashing']['orm_default'])) {
            throw new Exception(
                sprintf('Could not find hashing config in %s to create %s.', __CLASS__, HashingService::class)
            );
        }

        /** @var HashingInterface $adapter */
        $adapter = $container->build(
            'hashing_adapter',
            [
                'key'    => $config['doctrine']['hashing']['orm_default']['key'],
                'pepper' => $config['doctrine']['hashing']['orm_default']['pepper'],
            ]
        );

        return new HashingService($adapter);
    }
}