<?php

namespace Encrypt\Factory\Subscriber;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use DoctrineModule\Service\AbstractFactory;
use Encrypt\Adapter\HashingAdapter;
use Encrypt\Interfaces\HashingInterface;
use Encrypt\Options\HashingOptions;
use Encrypt\Subscriber\HashingSubscriber;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HashingSubscriberFactory extends AbstractFactory
{
    /**
     * @param ServiceLocatorInterface $container
     *
     * @return HashingSubscriber
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function createService(ServiceLocatorInterface $container)
    {
        /** @var HashingOptions $options */
        $options = $this->getOptions($container, 'hashing');
        /** @var Reader|AnnotationReader $reader */
        $reader = $this->createReader($container, $options->getReader());
        /** @var HashingInterface|HashingAdapter $adapter */
        $adapter = $this->createAdapter(
            $container,
            $options->getAdapter(),
            [
                'key'    => $options->getKey(),
                'pepper' => $options->getPepper(),
            ]
        );

        return new HashingSubscriber(
            $reader,
            $adapter
        );
    }

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return HashingSubscriber
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $this->createService($container);
    }

    /**
     * Get the class name of the options associated with this factory.
     *
     * @return string
     */
    public function getOptionsClass()
    {
        return HashingOptions::class;
    }

    /**
     * @param ContainerInterface $container
     * @param string             $reader
     * @param array|null         $options
     *
     * @return Reader
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function createReader(ContainerInterface $container, string $reader, array $options = null)
    {
        /** @var Reader $reader */
        $reader = $this->hydrateDefinition($reader, $container, $options);

        if ( ! $reader instanceof Reader) {
            throw new \InvalidArgumentException(
                'Invalid reader provided. Must implement ' . Reader::class
            );
        }

        return $reader;
    }

    /**
     * @param ContainerInterface $container
     * @param                    $adapter
     * @param array|null         $options
     *
     * @return HashingInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function createAdapter(ContainerInterface $container, string $adapter, array $options = null)
    {
        /** @var HashingInterface $adapter */
        $adapter = $this->hydrateDefinition($adapter, $container, $options);

        if ( ! $adapter instanceof HashingInterface) {
            throw new \InvalidArgumentException(
                'Invalid hashor provided, must be a service name, '
                . 'class name, an instance, or method returning an ' . HashingInterface::class
            );
        }

        return $adapter;
    }

    /**
     * Hydrates the value into an object
     *
     * @param                    $value
     * @param ContainerInterface $container
     * @param array|null         $options
     *
     * @return mixed
     */
    private function hydrateDefinition($value, ContainerInterface $container, array $options = null)
    {
        if (is_string($value)) {
            if ($container->has($value)) {
                if (is_array($options)) {
                    $value = $container->build($value, $options);
                } else {
                    $value = $container->get($value);
                }
            } else {
                if (class_exists($value)) {
                    $value = new $value();
                }
            }
        } else {
            if (is_callable($value)) {
                $value = $value();
            }
        }

        return $value;
    }
}