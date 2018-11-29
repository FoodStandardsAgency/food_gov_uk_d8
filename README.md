# Food Standards Agency

Food Standards Agency (FSA) Drupal 8 site code repository.

## Site environments

- Production [www.food.gov.uk](https://www.food.gov.uk)
  - Acquia Cloud domain: [http://foodgovuk.prod.acquia-sites.com](http://foodgovuk.prod.acquia-sites.com)
- Staging [http://fsauser:FCeDh4u&7n2p@foodgovukstg.prod.acquia-sites.com](http://foodgovukstg.prod.acquia-sites.com)
- Dev: [http://fsauser:FCeDh4u&7n2p@foodgovukdev.prod.acquia-sites.com](http://foodgovukdev.prod.acquia-sites.com)

### Continuous integration

This project uses Acquia Pipelines to:

- Monitor GitHub repository for new commits
- Build the project
- Run static analysis code checks on all custom code
- Push to Acquia Cloud repository, but only if the branch is prefixed with `feature/`

### Getting started

#### Requirements


- [Composer](https://getcomposer.org/)
- [Docker](https://www.docker.com/get-started)
- [DDEV](https://ddev.readthedocs.io/en/latest/)

> NB: You'll need a database from the Acquia Cloud platform.

```
# Install project dependencies
composer install
# Start ddev containers
ddev start
# Wait for docker images to pull/expand/start - could take up to 30 mins on first start dependent on bandwidth and system resources.
# Don't worry, it's really fast after you've got the images.
ddev import-db --src path-to-your-sql-file
```

See [docs/development.md](docs/development.md) for details of XDebug, Drush tooling as well as DDEV docs at https://ddev.readthedocs.io.

## Project management

Jira: https://wearesort.atlassian.net/secure/RapidBoard.jspa?rapidView=4&projectKey=FSA

## Development workflow

A simple branching model, summarised as

- branch from `master`, use feature branches with `feature/foo` naming convention. Git push to origin at least once per day and allow CI to catch any errors.
- PR for any code to be merged back into `master`
- On code merge, On Demand Environments (ODEs) will be automatically decommissioned.

## QA workflow

- ODE should be created for feature branch. Project team to evaluate content based on this. The entire stack is cloned from prod: db, files but with your feature branch.

## Releasing code

> You need to use the Acquia Cloud CLI or UI

- Prepare features
  - Merge ODE code branches or candidate features into the staging environment, evaluate for stability.
- Deploy to production
  - Ensure that candidate feature branches have all merged into `master`
  - Create a GitHub release tag (for our own tracking purposes)
  - Deploy codebase from Acquia staging environment to production via the UI or CLI.
  - Acquia Cloud will automatically create a new release tag (it uses its own naming convention, independent of the GitHub repo) and deploy code into production environment.

## FHRS Rating Search

FHRS Establishment and authority data is pulled from [FHRS rating API](http://api.ratings.food.gov.uk) with Drupal Migrate API.

- Refer to [fsa_ratings/README.md](/docroot/modules/custom/fsa_ratings/README.md) for entity documentation.
- Refer to [fsa_ratings_import/README.md](/docroot/modules/custom/fsa_ratings_import/README.md) for import documentation.


## FSA Alerts API

[Alert API documentation](http://fsa-staging-alerts.epimorphics.net/food-alerts/ui/reference)

Alert API data is imported to Drupal `alerts_allergen` taxonomy and `alert` nodes.

- Refer to [fsa_alerts/README.md](/docroot/modules/custom/fsa_alerts/README.md) for documentation.


## FSA Ratings search / Elasticsearch

- Ratings search is located at `/ratings/search`.
- The rating search is implemented with `fsa_ratings` module.
- FSA Establishments are indexed to Elasticsearch with FSA Elasticsearch integration (`fsa_es`) module, refer to [README.md](/drupal/web/modules/custom/fsa_es/README.md) for documentation.