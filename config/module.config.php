<?php

namespace Keet\Encrypt;

use Doctrine\Common\Annotations\AnnotationReader;
use Keet\Encrypt\Adapter\EncryptionAdapter;
use Keet\Encrypt\Adapter\HashingAdapter;
use Keet\Encrypt\Factory\Adapter\EncryptionAdapterFactory;
use Keet\Encrypt\Factory\Adapter\HashingAdapterFactory;
use Keet\Encrypt\Factory\Service\EncryptionServiceFactory;
use Keet\Encrypt\Factory\Service\HashingServiceFactory;
use Keet\Encrypt\Factory\Subscriber\EncryptionSubscriberFactory;
use Keet\Encrypt\Factory\Subscriber\HashingSubscriberFactory;
use Keet\Encrypt\Service\EncryptionService;
use Keet\Encrypt\Service\HashingService;

return [
    'doctrine_factories' => [
        'encryption' => EncryptionSubscriberFactory::class,
        'hashing'    => HashingSubscriberFactory::class,
    ],
    'doctrine'           => [
        'encryption'   => [
            'orm_default' => [
                'adapter' => 'encryption_adapter',
                'reader'  => AnnotationReader::class,
            ],
        ],
        'hashing'      => [
            'orm_default' => [
                'adapter' => 'hashing_adapter',
                'reader'  => AnnotationReader::class,
            ],
        ],
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    'doctrine.encryption.orm_default',
                    'doctrine.hashing.orm_default',
                ],
            ],
        ],
    ],
    'service_manager'    => [
        'aliases'   => [
            // Using aliases so someone else can use own adapter/factory
            'encryption_adapter' => EncryptionAdapter::class,
            'encryption_service' => EncryptionService::class,
            'hashing_adapter'    => HashingAdapter::class,
            'hashing_service'    => HashingService::class,
        ],
        'factories' => [
            EncryptionAdapter::class => EncryptionAdapterFactory::class,
            EncryptionService::class => EncryptionServiceFactory::class,
            HashingAdapter::class    => HashingAdapterFactory::class,
            HashingService::class    => HashingServiceFactory::class,
        ],
    ],
];