# magerun-deploy

This is a module for the [n98-magerun toolset](https://github.com/netz98/n98-magerun). It adds a set of commands intended to manage Capistrano configuration for your Magento 1.x codebase.

## Installation
1. [Install n98-magerun](https://github.com/netz98/n98-magerun#installation)

2. [Create a module directory](https://github.com/netz98/n98-magerun/wiki/Modules#where-can-modules-be-placed)

3. Either clone this repository or [download the latest version](https://github.com/6by6/magerun-deploy/archive/master.zip) of 6by6/magerun-deploy. You should unzip/clone the package into the module directory configured above.

4. Ensure the module has been installed correctly by running `n98-magerun.phar deploy:config` within a Magento codebase. 

## Usage

#### Prerequesites
[Capistrano](http://capistranorb.com/) is a Ruby application. You will therefore need to ensure your environment has Ruby installed (see [Installing Ruby](https://www.ruby-lang.org/en/documentation/installation/)). We would recommend using a Ruby manager, we prefer [rbenv](https://github.com/rbenv/rbenv#readme).
