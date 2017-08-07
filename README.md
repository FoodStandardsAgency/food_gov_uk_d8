# Food Standards Agency
[![Build Status](https://travis-ci.com/wunderio/client-UK-FSA-alpha.svg?token=oxuHeqz2nYPYhP3SBFxr&branch=master)](https://travis-ci.com/wunderio/client-UK-FSA-alpha)

Food Standards Agency (FSA) Drupal 8 site code repository.

### Continuous integration
This project is using Travis CI to deploy the code.

See more here: [https://travis-ci.com/wunderio/client-UK-FSA-alpha](https://travis-ci.com/wunderio/client-UK-FSA-alpha.svg?token=oxuHeqz2nYPYhP3SBFxr&branch=master)

### Getting started

#### Requirements
- [Vagrant](https://www.vagrantup.com/downloads.html) 1.9.2 or greater
- [vagrant-cachier](https://github.com/fgrehm/vagrant-cachier)
 `vagrant plugin install vagrant-cachier`
- Ansible version 2.1.2 or greater in host machine. For OS X:
 `brew install ansible`
- [Virtualbox](https://www.virtualbox.org/wiki/Downloads) 5.1 or greater 

#### 1. Setup local environment

```$ git clone git@github.com:wunderio/client-UK-FSA-alpha.git```

```$ vagrant up``` - default is fine reply to all prompts. 

If you dont' use `vagrant-hostmanager` add following line to `/etc/hosts`
```192.168.10.178	local.food.gov.uk```

#### 2. First time setup

```$ vagrant ssh```

```$ cd /vagrant/drupal/ && ./build.sh reset```

Access your local environment at https://local.food.gov.uk

## Project management

Jira: https://wunder.atlassian.net/projects/FSA

## Development workflow

Refer WunderFlow for branching: http://wunderkraut.github.io/WunderFlow

## Server Provisioning/Deployment
See [docs/provisioning.md](docs/provisioning.md).


More detailed documentation at [docs/development.md](docs/development.md)