# Food Standards Agency

Food Standards Agency (FSA) Drupal 8 site code repository.

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

#### Drupal console & codeception on local environment

Drupal console or codeception do not work out of the box as they cannot read `getenv()` from the `$databases` array. Workaround is to export db user & password to bash:
 ```
 export DB_USER_DRUPAL=drupal
 export DB_PASS_DRUPAL=password
 ```