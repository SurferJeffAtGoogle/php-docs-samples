First attempt to install video intelligence.

``` sh
rennie@rennie:~/gitrepos/php-docs-samples/video$ composer.phar require --prefer-stable google/cloud-videointelligence 
Using version ^0.1.0 for google/cloud-videointelligence
./composer.json has been updated
Loading composer repositories with package information
Updating dependencies (including require-dev)
Your requirements could not be resolved to an installable set of packages.

  Problem 1
    - Installation request for google/cloud-videointelligence ^0.1.0 -> satisfiable by google/cloud-videointelligence[v0.1.0].
    - google/cloud-videointelligence v0.1.0 requires ext-grpc * -> the requested PHP extension grpc is missing from your system.

  To enable extensions, verify that they are enabled in your .ini files:
    - /usr/local/lib/php.ini
  You can also run `php --ini` inside terminal to see which files are used by PHP in CLI mode.

Installation failed, reverting ./composer.json to its original content.
rennie@rennie:~/gitrepos/php-docs-samples/video$ 
```

Ok, time to Google the error message. 
Found https://github.com/grpc/grpc/issues/8547.  Let's follow along.

    rennie@rennie:~/Downloads$ tar xvf protobuf-cpp-3.3.0.tar.gz 
    rennie@rennie:~/Downloads$ cd protobuf-3.3.0/
    rennie@rennie:~/Downloads/protobuf-3.3.0$ ./configure 
    
Scratch that, I notice I have protoc already installed:

    rennie@rennie:~/Downloads/protobuf-3.3.0$ protoc --version
    libprotoc 3.1.0
    
Hmm, this is starting to smell like a dead end.  Now, I discover
https://github.com/grpc/grpc/tree/master/src/php

    rennie@rennie:~/gitrepos/grpc$ sudo pecl install grpc
    downloading grpc-1.3.2.tgz ...
    ...
    Build process completed successfully
    Installing '/usr/lib/php5/20121212/grpc.so'
    install ok: channel://pecl.php.net/grpc-1.3.2
    configuration option "php_ini" is not set to php.ini location
    You should add "extension=grpc.so" to php.ini

And I add `extension=grpc.so` to my php.ini but

    rennie@rennie:~/gitrepos/php-docs-samples/video$ composer.phar require --prefer-stable google/cloud-videointelligence 
    PHP Warning:  PHP Startup: Unable to load dynamic library '/usr/local/lib/php/extensions/no-debug-non-zts-20131226/grpc.so' - /usr/local/lib/php/extensions/no-debug-non-zts-20131226/grpc.so: cannot open shared object file: No such file or directory in Unknown on line 0

Ok, I add `extension_dir=/usr/lib/php5/20121212` to php.ini, and now I get this error:

``` sh
rennie@rennie:~/gitrepos/php-docs-samples/video$ composer.phar require --prefer-stable google/cloud-videointelligence 
PHP Warning:  PHP Startup: grpc: Unable to initialize module
Module compiled with module API=20121212
PHP    compiled with module API=20131226
These options need to match
 in Unknown on line 0
Using version ^0.1.0 for google/cloud-videointelligence
./composer.json has been updated
Loading composer repositories with package information
Updating dependencies (including require-dev)
Your requirements could not be resolved to an installable set of packages.

  Problem 1
    - Installation request for google/cloud-videointelligence ^0.1.0 -> satisfiable by google/cloud-videointelligence[v0.1.0].
    - google/cloud-videointelligence v0.1.0 requires ext-grpc * -> the requested PHP extension grpc is missing from your system.

  To enable extensions, verify that they are enabled in your .ini files:
    - /usr/local/lib/php.ini
  You can also run `php --ini` inside terminal to see which files are used by PHP in CLI mode.

Installation failed, reverting ./composer.json to its original content.
rennie@rennie:~/gitrepos/php-docs-samples/video$ 
```

Somehow, it was still using the old 5.5.9 pecl.  This fixed it:

    rennie@rennie:~/gitrepos/php-docs-samples/video$ sudo /usr/local/bin/pecl install grpc


Yay.  New error!

    rennie@rennie:~/gitrepos/php-docs-samples/video$ composer.phar require google/cloud-videointelligence 
    php: symbol lookup error: /usr/local/lib/php/extensions/no-debug-non-zts-20131226/grpc.so: undefined symbol: pthread_once
    
Filed issue https://github.com/grpc/grpc/issues/11232


Tried switching gears and using [phpenv](https://github.com/phpenv/phpenv).
But it malfunctioned and listed no useful releases:

```sh
rennie@rennie:~/gitrepos/grpc$ phpenv install --releases
phpenv v0.0.4-dev

                    init extensions  the cloning of repositories for additional extensions           
```



