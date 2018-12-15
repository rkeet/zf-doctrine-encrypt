# Zend Framework 3 & Doctrine Encrypt Module

Provides a Zend Framework 3 & Doctrine 2 encryption module.

# Installation

    composer require rkeet/zf-doctrine-encrypt
    
# Requirements

 * PHP 7.2 or greater (must have Sodium extension enabled)
 
If you're on Windows, using Xampp, the PHP 7.2 installation might not automatically enable the Sodium extension. If this
the case, you'll get an error (`'This is not implemented, as it is not possible to securely wipe memory from PHP'`). 
Enable Sodium for PHP by adding this to your `php.ini` file:

    extension = C:\xampp\php\ext\php_sodium.dll

This might also be applicable ot other local installations.  

# Configuration

## Zend Framework

Make sure to add the module to you application configuration. In your `modules.config.php` make sure to include 
`ZfDoctrineEncryptModule`.

## Module

`*.dist` files are provided. Copy these (remove extension) to your application and fill in the required key/salt values. 
If these are filled in, it works out of the box using [Halite](https://github.com/paragonie/halite) for encryption. 

However, must be said, at the moment of writing this ReadMe, the Halite module contains duplicate `const` declarations,
as such, you must disable your `E_NOTICE` warnings in your PHP config :(

## Annotation Examples

### Encryption

Simple, consider that you have an `Address` Entity, which under upcoming [EU GDPR regulation](https://www.eugdpr.org/)
requires parts of the address, such as the street, to be encrypted. This uses the key & salt required for the config
by default

To encrypt a street name, add `@Encrypted` like so: 

    /**
     * @var string
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     * @Encrypted
     */
    protected $street;
    
By default the Encryption service assumes that the data to be encrypted is of the type `string`. However, you could have
a requirement to encrypt another type of data, such as a house number. Non-string types are supported, but the type of data
must be provided if not a string. You can do this like so:

    /**
     * @var int
     * @ORM\Column(name="house_number", type="integer", length=20, nullable=false)
     * @Encrypted(type="int")
     */
    protected $houseNumber;
    
Supported types are [found here](http://php.net/settype).

### Hashing

Say you'd like to store a password, it should work in much of the same way as the above. However, it is data that should
not be de-cryptable (and there's no need for it to ever be decrypted), thus you should hash it instead.

To hash something, like a password, add the `@Hashed` Annotation. See the example below.

    /**
     * @var string
     * @ORM\Column(name="password", type="text", nullable=false)
     * @Hashed
     */
    protected $password;
    
**Note** that, unlike `@Encrypted`, there aren't options to give a type. As we can't decrypt the data (it's one-way), 
there's no need to know what the original type was. The response will always be string value.

 ## Controller Examples
 
 ### Hashing
 
 A `Hashmanager` service is provided. This manager also uses the `HaliteHashingAdapter` but provides functionality that 
 can be used in Controllers and other classes, such as plugins. The service is registered under the alias 'hashing_service'.
 You can override 'hasing_service' in your own project to provide your own implementation. 
 
 The `HashManager` provides the ability to hash and verify strings. These are two separate operations, one one-way 
 hashes a string. The other does the same (requires the hashed string) and then verifies that both strings are 
 exactly the same (thus verifying).
 
 In a Controller, to hash a string, simple do:
 
     $secret = $this->getHashManager()->hash('correct horse battery staple')
     
 To verify that your dealing the same string a next time, for example to compare passwords on login, do:
 
     $verified = $this->getHashManager()->verify('correct horse battery staple', $secret)
     
 `$verified` will be set to a boolean value. 
 
 To not store any entered data longer than you must, you could compare directly from form data, like so:
 
     if($form->isValid() && $this->getHashManager()->verify($form->getData()['password_field'], $user->getPassword()) {
         // do other things
     }
     