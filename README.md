# Food Standards Agency

[![Build Status](https://travis-ci.com/wunderio/client-UK-FSA-beta.svg?branch=master)](https://travis-ci.com/wunderio/client-UK-FSA-beta)

Food Standards Agency (FSA) Drupal 8 site code repository.

### Continuous integration

> NB: Subject to change - check with team.

This project deploys using [Deploybot](https://wunder.deploybot.com/111465) and uses [Travis](https://travis-ci.com/wunderio/client-UK-FSA-beta) for tests.
* Production [www.food.gov.uk](https://www.food.gov.uk)
* Development: [fsa.dev.wunder.io](https://fsa.dev.wunder.io)
* Staging [fsa.stage.wunder.io](https://fsa.stage.wunder.io)

### Getting started

#### Requirements

- [Composer]()
- [Docker]()
- [DDEV]()

> NB: You'll need a database from somewhere.

```
# Install project dependencies
cd drupal && composer install
# Start ddev containers
ddev start
# Wait for docker images to pull/expand/start - could take up to 30 mins on first start dependent on bandwidth and system resources.
# Don't worry, it's really fast after you've got the images.
ddev import-db --src path-to-your-sql-file
```

See [docs/development.md](docs/development.md) for details of XDebug, Drush tooling as well as DDEV docs at https://ddev.readthedocs.io.

## Project management

> TBC

Jira: https://wunder.atlassian.net/projects/FSA

## Development workflow

Simple branching model as per Wunderflow: http://wunderflow.wunder.io

## Server Provisioning/Deployment

> NB: Legacy material.

See [docs/provisioning.md](docs/provisioning.md).

More detailed documentation at [docs/development.md](docs/development.md)
