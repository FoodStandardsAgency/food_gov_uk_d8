# Food Standards Agency
[![Build Status](https://travis-ci.com/wunderio/client-UK-FSA-alpha.svg?token=n479wr8JE8WbYmyacHHX&branch=master)](https://travis-ci.com/wunderio/client-UK-FSA-alpha) 

Food Standards Agency (FSA) Drupal 8 site code repository.

### Continuous integration
This project deploys using [Deploybot](https://wunder.deploybot.com/111465) and uses [Travis](https://travis-ci.com/wunderio/client-UK-FSA-alpha) for tests.
* Production [beta.food.gov.uk](https://beta.food.gov.uk)
* Development: [fsa.dev.wunder.io](https://fsa.dev.wunder.io)
* Staging [fsa.stage.wunder.io](https://fsa.stage.wunder.io)

### Getting started

#### Requirements
- [Vagrant](https://www.vagrantup.com/downloads.html) 1.9.2 or greater
- [vagrant-cachier](https://github.com/fgrehm/vagrant-cachier)
 `vagrant plugin install vagrant-cachier`
- Ansible version 2.1.2 or greater in host machine. For OS X:
 `brew install ansible`
- [Virtualbox](https://www.virtualbox.org/wiki/Downloads) 5.1 or greater 

#### 1. Setup local environment

```$ git clone git@github.com:wunderio/client-UK-FSA-beta.git```

```$ vagrant up``` 

Add to your host machine `/etc/hosts` file the following line  
```192.168.100.178	local.food.gov.uk```

#### 2. First time setup

```$ vagrant ssh```

```$ cd /vagrant/drupal/ && ./build.sh reset```

Access your local environment at https://local.food.gov.uk

## Project management

Jira: https://wunder.atlassian.net/projects/FSA

## Development workflow

Refer WunderFlow for branching: http://wunderflow.wunder.io

## Server Provisioning/Deployment
See [docs/provisioning.md](docs/provisioning.md).


More detailed documentation at [docs/development.md](docs/development.md)
