Master: [![Build Status](https://travis-ci.org/janus-ssp/janus.png?branch=master)](https://travis-ci.org/janus-ssp/janus)

Develop: [![Build Status](https://travis-ci.org/janus-ssp/janus.png?branch=develop)](https://travis-ci.org/janus-ssp/janus)

janus-ssp
=========

JANUS is a fully featured metadata registration administration module build on top of simpleSAMLphp.


See the file LICENCE for the licence conditions.


For discussing this project a mailinglist is available at https://list.surfnet.nl/mailman/listinfo/janus


Installation
============

JANUS is a module for simpleSAMLphp.

Note: Janus is developed on unix based systems and might not work on windows due to the use of softlinks (amongs others)

To set up JANUS you need to do the following:

  * Set up a working copy of simpleSAMLphp >= 1.7.0
  * Set up an authentication source
  * Download JANUS -> See Obtaining Janus
  * Set up database
  * Configure JANUS

For instructions on how to set up a working copy of simpleSAMLphp and how to
set up a authentication source, please refer to http://simplesamlphp.org/docs/

Then you should get the desired version of JANUS and instlal it as a module for
your simpleSAMLphp installation and copy the configuration file template to the
simpleSAMLphp configuration directory.

Now you should have a working installation of JANUS. For a more detailed
introduction to JANUS and the configuration please go to
https://github.com/janus-ssp/janus/wiki/What-IsJANUS

More information can be found in the wiki at https://github.com/janus-ssp/janus/wiki

Installing Janus
===============

Obtaining Janus can be done in several ways.

Install from an (gzip) archive from the Github releases page
-----------------------------------------------------------------------------

Janus 1.17.5 and up will be available as a selfcontaining gzip archive from the GitHub releases page

To install:
- Download release gzip
- Extract release gzip into SimpleSamlPhp modules dir
- Create a working database
- Go to the install page: ``{urltoyoursimplesamlphp}/module.php/janus/install/``
- Configure caching dirs see: [Cache configuration](#Cache configuration)

Note: that symlinking janus into the modules dir is not supported, except when you install both SimpleSamlPHP and janus via Composer.

Install by cloning the repository
---------------------------------

Janus can also be obtained directly from the git repository at GitHub
by cloning the project in the modules dir of SimpleSamlPhp, this makes updating easier.:

To install:
- Go to the ``modules`` dir of SimpleSamlPhp
- Obtain code, run ``sh git clone https://github.com/janus-ssp/janus.git``
- Go to ``janus`` directory
- Instal dependencies, run: ``sh composer.phar install --no-dev``. Or if you want to have development tools like PHPUnit installed as well run: ``sh composer.phar install --dev``
- Configure caching dirs see: [Cache configuration](#Cache configuration)

Install Janus as a Composer dependency
--------------------------------------

While still a bit experimental. Janus itself can be now also installed using composer. This requires SimpleSamlPhp to be installed via Composer as well, 

To install:
- add the following to your composer json: 

```json
"require": {
    "janus-ssp/janus":"dev-master",
},
```
- run composer
- Configure caching dirs see: [Cache configuration](#Cache configuration)
- Make sure SimpleSamlPhp is able to load janus from the vendor directory for example by softlinking it into
the modules directory
- Correct the components softlink in the www/resources dir from:

```sh
../../components
```

to:

```sh
../../../../../components
```

For a working implementation of Janus as a dependency see:
https://github.com/OpenConext/OpenConext-serviceregistry/blob/develop/composer.json


Configuration
=============

Authentication configuration
----------------------------

Set the parameter 'useridattr' to match the attribute you want
to make the connection between the user and the entities.

Database configuration
----------------------

Create a database and configure the credentials or let the installer do this for you. 

You should change the storageengine and
characterset to fit your needs. You can use another pefix for the table names
by editing the `prefix` option in the configuration file. (Note that the prefix option has been fixed since 1.17.0)

Updating
========

- Run the database migrations: ``sh ./bin/migrate``

Note that the migrations can also upgrade an existing database. (always test this first). 


Cache configuration
-------------------

Janus expects the following dirs to be present writable: ``/var/cache/janus`` and ``/var/logs/janus``. If you want to change this you can configure paths to cache and logs dir like:

```php
'cache_dir' => '/my/own/cachedir',

'log_dir' => '/my/own/logs/dir'
```

Note that to able to upgrade the database the command line user also has to have write permissions to these directories.

Creating a release
==================

Janus has built in support for creating a release. The created releases are meant to create a version of Janus which works as a plugin for SimpleSamlPhp

Creating a release is as simple as calling
```sh
./RMT release
```

The tool will then asked a series of questions and create a release in the releases dir.

The tool behaves differently depending on which branch it is called from. While the tool is meant to make an official release from master in the first place it's also possible to make releases of other branches.

When making a release from master the following happens:
- Check if working copy is clean
- Check if unittests can be runned succesfully
- Update the changelog
- Create a tag
- Push tag to github
- Create an archive in the releases dir suffixed with the tag name
- Create an archive in the releases dir suffixed with the tag name

When making a release from a branch other than master the following happens:
- Check if working copy is clean
- Check if unittests can be runned succesfully
- Update the changelog
- Create an archive in the releases dir suffixed with the branch name and commit hash
