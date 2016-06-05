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

You will also require [Bundler](http://bundler.io/) as we'll be using Gemfiles to manage dependencies.

#### Setup
The first step involves creating the directory structure for your Capistrano configuration. 

Navigate to the root of your Magento instance e.g. 
```
$ cd ~/code/web/magento/vanilla/1.9.2.4
```

Run the setup command
```
$ n98-magerun.phar deploy:setup
```
You should see output similar to the following:
```
Inspecting codebase for existing Cap files...
 ✔ Wrote ~/code/web/magento/vanilla/1.9.2.4/Gemfile
 ✔ Wrote ~/code/web/magento/vanilla/1.9.2.4/Capfile
 ✔ Created ~/code/web/magento/vanilla/1.9.2.4/config
 ✔ Created ~/code/web/magento/vanilla/1.9.2.4/config/deploy
 ✔ Created ~/code/web/magento/vanilla/1.9.2.4/config/sixbysix-deploy.json
```

If you have existing Capistrano files you'll see output similar to:
```
Inspecting codebase for existing Cap files...

Found the following files/dirs:
 ⇒ ~/code/web/magento/vanilla/1.9.2.4/Gemfile
 ⇒ ~/code/web/magento/vanilla/1.9.2.4/Capfile
 ⇒ ~/code/web/magento/vanilla/1.9.2.4/config/deploy
 ⇒ ~/code/web/magento/vanilla/1.9.2.4/config/sixbysix-deploy.json
 ⇒ ~/code/web/magento/vanilla/1.9.2.4/config

✖ It looks like there is an existing setup. Please run 'deploy:wipe' to remove the above files.
```
This is simply warning you that you should check out the existing files and ensure you're not about to delete anything important. If you're happy to continue run `$ n98-magerun.phar deploy:wipe`.

#### Configuration
You have two options:

##### 1. Wizard
We have included a wizard to help you setup general and stage configuration. You can invoke this by running
```
$ n98-magerun.phar deploy:config:wizard
```

##### 2. One-liner
Actually a two-liner...

The first command, demonstrated below, can be used to set your general options. You can include as many or as few options as you wish here. Check out `$ n98-magerun.phar deploy:config --help` for available options.
```
$ n98-magerun.phar deploy:config update --name="vanilla-1.9.2.4" --scm=git --repo=git@github.com:6by6/magento-1.9.2.4.git --shared_dirs=/wp --shared_files=/.htaccess --shared_files=/maintenance.flag
```

The second command is used to add/update/remove stages from your config, for example:

###### Add a stage
```
$ n98-magerun.phar deploy:config:stage add --name=prod --host=127.0.0.1 --user=daniel --deploy_to=/var/www/prod --branch=master
```

###### Update a stage
```
$ n98-magerun.phar deploy:config:stage edit --name=prod --rename=demo --branch=develop
```

###### Remove a stage
```
$ n98-magerun.phar deploy:config:stage delete --name=demo
```

## Generate Files
When you have modified your config you'll need to regenerate your Ruby files. We have included one last command for this:
```
$ n98-magerun.phar deploy:generate
```

## Deploying
With configuration out the way all that's left is initiating a deployment. 

From your codebase root:
1. `$ bundle --path=.bundle` (installs gems locally)
2. `$ bundle exec cap $STAGE_NAME deploy:setup` (creates shared files etc.)
3. `$ bundle exec cap $STAGE_NAME deploy` (deploys your code)
