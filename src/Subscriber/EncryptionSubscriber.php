<?php

namespace Keet\Encrypt\Subscriber;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Keet\Encrypt\Annotation\Encrypted;
use Keet\Encrypt\Interfaces\EncryptionInterface;

class EncryptionSubscriber implements EventSubscriber
{
    /**
     * Encryptor interface namespace
     */
    public const ENCRYPTOR_INTERFACE_NS = EncryptionInterface::class;

    /**
     * Encrypted annotation full name
     */
    public const ENCRYPTED_ANNOTATION_NAME = Encrypted::class;

    /**
     * Encryptor
     *
     * @var EncryptionInterface
     */
    private $encryptor;

    /**
     * Annotation reader
     *
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $reader;

    /**
     * Register to avoid multi decode operations for one entity
     *
     * @var array
     */
    private $decodedRegistry = [];

    /**
     * Caches information on an entity's encrypted fields in an array keyed on
     * the entity's class name. The value will be a list of Reflected fields that are encrypted.
     *
     * @var array
     */
    private $encryptedFieldCache = [];

    /**
     * Before flushing the objects out to the database, we modify their password value to the
     * encrypted value. Since we want the password to remain decrypted on the entity after a flush,
     * we have to write the decrypted value back to the entity.
     *
     * @var array
     */
    private $postFlushDecryptQueue = [];

    /**
     * DoctrineEncryptSubscriber constructor.
     *
     * @param Reader              $reader
     * @param EncryptionInterface $encryptor
     */
    public function __construct(Reader $reader, EncryptionInterface $encryptor)
    {
        $this->setReader($reader);
        $this->setEncryptor($encryptor);
    }

    /**
     * Encrypt the password before it is written to the database.
     *
     * Notice that we do not recalculate changes otherwise the password will be written
     * every time (Because it is going to differ from the un-encrypted value)
     *
     * @param OnFlushEventArgs $args
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $objectManager = $args->getEntityManager();
        $unitOfWork = $objectManager->getUnitOfWork();
        $this->postFlushDecryptQueue = [];

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->entityOnFlush($entity, $objectManager);
            $unitOfWork->recomputeSingleEntityChangeSet($objectManager->getClassMetadata(get_class($entity)), $entity);
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->entityOnFlush($entity, $objectManager);
            $unitOfWork->recomputeSingleEntityChangeSet($objectManager->getClassMetadata(get_class($entity)), $entity);
        }
    }

    /**
     * @param \object                     $entity
     * @param ObjectManager|EntityManager $objectManager
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    private function entityOnFlush(object $entity, ObjectManager $objectManager): void
    {
        $objId = spl_object_hash($entity);
        $fields = [];

        foreach ($this->getEncryptedFields($entity, $objectManager) as $field) {
            /** @var \ReflectionProperty $reflectionProperty */
            $reflectionProperty = $field['reflection'];
            $fields[$reflectionProperty->getName()] = [
                'field'   => $reflectionProperty,
                'value'   => $reflectionProperty->getValue($entity),
                'options' => $field['options'],
            ];
        }

        $this->postFlushDecryptQueue[$objId] = [
            'entity' => $entity,
            'fields' => $fields,
        ];

        $this->processFields($entity, $objectManager);
    }

    /**
     * After we have persisted the entities, we want to have the
     * decrypted information available once more.
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();

        foreach ($this->postFlushDecryptQueue as $pair) {
            $fieldPairs = $pair['fields'];
            $entity = $pair['entity'];
            $oid = spl_object_hash($entity);

            foreach ($fieldPairs as $fieldPair) {
                /** @var \ReflectionProperty $field */
                $field = $fieldPair['field'];
                $field->setValue($entity, $fieldPair['value']);
                $unitOfWork->setOriginalEntityProperty($oid, $field->getName(), $fieldPair['value']);
            }

            $this->addToDecodedRegistry($entity);
        }

        $this->postFlushDecryptQueue = [];
    }

    /**
     * Listen a postLoad lifecycle event. Checking and decrypt entities
     * which have @Encrypted annotations
     *
     * @param LifecycleEventArgs $args
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        $objectManager = $args->getEntityManager();

        if ( ! $this->hasInDecodedRegistry($entity)) {
            if ($this->processFields($entity, $objectManager, false)) {
                $this->addToDecodedRegistry($entity);
            }
        }
    }

    /**
     * @return array
     */
    public function getSubscribedEvents() : array
    {
        return [
            Events::postLoad,
            Events::onFlush,
            Events::postFlush,
        ];
    }

    /**
     * Process (encrypt/decrypt) entities fields
     *
     * @param               $entity
     * @param EntityManager $em
     * @param bool          $isEncryptOperation
     *
     * @return bool
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Exception
     */
    private function processFields($entity, EntityManager $em, $isEncryptOperation = true) : bool
    {
        $properties = $this->getEncryptedFields($entity, $em);
        $unitOfWork = $em->getUnitOfWork();
        $oid = spl_object_hash($entity);

        foreach ($properties as $property) {
            /** @var \ReflectionProperty $refProperty */
            $refProperty = $property['reflection'];

            /** @var Encrypted $annotationOptions */
            $annotationOptions = $property['options'];

            /** @var boolean $nullable */
            $nullable = $property['nullable'];

            $value = $refProperty->getValue($entity);
            // If the value is 'null' && is nullable, don't do anything, just skip it.
            if (is_null($value) && $nullable) {
                continue;
            }

            $tableName = $property['table'];
            $colName   = $refProperty->getName();

            if ($isEncryptOperation) {
                $encryptionStorage = $this->getEncryptor()->prepareForStorage($value, $tableName, $colName);
                $value             = $encryptionStorage->getEncryptedText();
                $blindIndexValue   = $encryptionStorage->getBlindIndexValue($this->encryptor->getBlindIndexName($tableName, $colName));
                // set the blind index
                $setter = 'set' . ucfirst($annotationOptions->getBlindIndex());
                if (method_exists($entity, $setter) && !is_null($blindIndexValue)) {
                    $entity->$setter($blindIndexValue);
                }
            } else {
                $value = $this->getEncryptor()->decrypt($value, $tableName, $colName);
            }

            $type = $annotationOptions->getType();

            // If NOT encrypting, type know to PHP and the value does not match the type. Else error
            if (
                $isEncryptOperation === false
                // We're going to try a cast using settype. Array of types defined at: https://php.net/settype
                && in_array(
                    $type,
                    [
                        'boolean',
                        'bool',
                        'integer',
                        'int',
                        'float',
                        'double',
                        'string',
                        'array',
                        'object',
                        'null',
                    ]
                )
                && gettype($value) !== $type
            ) {
                if (settype($value, $type) === false) {
                    throw new \Exception(
                        sprintf(
                            'Could not convert encrypted value back to mapped value in %s::%s' . PHP_EOL,
                            __CLASS__,
                            __FUNCTION__
                        )
                    );
                }
            }

            $refProperty->setValue($entity, $value);

            if ( ! $isEncryptOperation) {
                //we don't want the object to be dirty immediately after reading
                $unitOfWork->setOriginalEntityProperty($oid, $refProperty->getName(), $value);
            }
        }

        return ! empty($properties);
    }

    /**
     * @param \object $entity Some doctrine entity
     *
     * @return bool
     */
    private function hasInDecodedRegistry($entity) : bool
    {
        return isset($this->decodedRegistry[spl_object_hash($entity)]);
    }

    /**
     * @param \object $entity Some doctrine entity
     */
    private function addToDecodedRegistry($entity): void
    {
        $this->decodedRegistry[spl_object_hash($entity)] = true;
    }

    /**
     * @param \object       $entity
     * @param EntityManager $em
     *
     * @return array|mixed
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    private function getEncryptedFields(object $entity, EntityManager $em)
    {
        $className = get_class($entity);

        if (isset($this->encryptedFieldCache[$className])) {
            return $this->encryptedFieldCache[$className];
        }

        $meta = $em->getClassMetadata($className);
        $encryptedFields = [];

        foreach ($meta->getReflectionProperties() as $refProperty) {
            /** @var \ReflectionProperty $refProperty */
            // Gets Encrypted object from property Annotation. Includes options and their values.
            $annotationOptions =
                $this->reader->getPropertyAnnotation($refProperty, $this::ENCRYPTED_ANNOTATION_NAME) ?: [];

            if ( ! empty($annotationOptions)) {
                $refProperty->setAccessible(true);
                $encryptedFields[] = [
                    'reflection' => $refProperty,
                    'options'    => $annotationOptions,
                    'nullable'   => $meta->getFieldMapping($refProperty->getName())['nullable'],
                    'table'      => $meta->getTableName()
                ];
            }
        }
        $this->encryptedFieldCache[$className] = $encryptedFields;

        return $encryptedFields;
    }

    /**
     * @return EncryptionInterface
     */
    public function getEncryptor() : EncryptionInterface
    {
        return $this->encryptor;
    }

    /**
     * @param EncryptionInterface $encryptor
     *
     * @return EncryptionSubscriber
     */
    public function setEncryptor(EncryptionInterface $encryptor) : EncryptionSubscriber
    {
        $this->encryptor = $encryptor;

        return $this;
    }

    /**
     * @return Reader
     */
    public function getReader() : Reader
    {
        return $this->reader;
    }

    /**
     * @param Reader $reader
     *
     * @return EncryptionSubscriber
     */
    public function setReader(Reader $reader) : EncryptionSubscriber
    {
        $this->reader = $reader;

        return $this;
    }

    /**
     * @return array
     */
    public function getDecodedRegistry() : array
    {
        return $this->decodedRegistry;
    }

    /**
     * @param array $decodedRegistry
     *
     * @return EncryptionSubscriber
     */
    public function setDecodedRegistry(array $decodedRegistry) : EncryptionSubscriber
    {
        $this->decodedRegistry = $decodedRegistry;

        return $this;
    }

    /**
     * @return array
     */
    public function getEncryptedFieldCache() : array
    {
        return $this->encryptedFieldCache;
    }

    /**
     * @param array $encryptedFieldCache
     *
     * @return EncryptionSubscriber
     */
    public function setEncryptedFieldCache(array $encryptedFieldCache) : EncryptionSubscriber
    {
        $this->encryptedFieldCache = $encryptedFieldCache;

        return $this;
    }

    /**
     * @return array
     */
    public function getPostFlushDecryptQueue() : array
    {
        return $this->postFlushDecryptQueue;
    }

    /**
     * @param array $postFlushDecryptQueue
     *
     * @return EncryptionSubscriber
     */
    public function setPostFlushDecryptQueue(array $postFlushDecryptQueue) : EncryptionSubscriber
    {
        $this->postFlushDecryptQueue = $postFlushDecryptQueue;

        return $this;
    }
}