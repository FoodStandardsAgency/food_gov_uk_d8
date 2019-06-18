# Food Standards Agency

Welcome to the repository of the *FSA* project. Thank you for choosing to work on this project.

## Prerequisites.

You'll need to be running [Docker](https://www.docker.com/) and the [Deeson Docker Proxy](https://github.com/teamdeeson/docker-proxy) to run this project locally.

## Getting started.

1. Clone the repo.
2. `make install`
3. `make start`
4. Add a database dump e.g. 

```
drush @test sql-dump > test.sql
pv test.sql | docker-compose exec -T mariadb mysql -udrupal -pdrupal drupal
```

6. Prepare the database for local usage `make update`
5. Login for the first time `drush @docker uli`

## Subsequent runs.

1. Start Docker: `make start`
2. Login: `drush @docker uli`

## When done.

Stop Docker to save resources: `make stop`

## Updating this project.

This project uses [Drush Make](https://docs.drush.org/en/7.x/make/) for pulling in dependencies.

### Updating modules.

Follow the normal advice on `https://www.drupal.org/docs/8/update/update-modules`

However, we want to run the composer command inside a docker container so use the following Make command. Note that you 
will be prompted for the module name after running the make command, it does not need to be part of the Make command

`make drupal-update-module`

### Updating core.

No special considerations for this project.  Just do it with the Make command below to make sure it happens 
inside a docker container.

`make drupal-upgrade`

### Adding a new module.

Modules are added with a Make command. This uses composer inside a docker container for you.  There's no arguments on a 
Make command, it will prompt you for the module name, e.g. to instal drupal/warden just type `warden` when asked. 

`make drupal-add-module`

## Patching the project.

Patches should be referenced from Drupal.org where possible. If you must make a patch file store it in the patches directory in the project root.

Patches are then referenced in the `composer.json` file as in the extras -> patches section.

```
        "patches": {
            "drupal/core": {
                "Human readable issue description": "https://www.drupal.org/files/uri-of-patch-file.patch",
                ...
```

## Branching strategy.

We use [GitFlow](https://www.deeson.co.uk/labs/using-git-flow-drupal-project) branching strategy on this project.

The UAT branch is used for work in progress and client demos.

## Development workflow.

1. Create a feature branch, from develop with a name that is lower case and includes the jira ticket number. The format
   should be `git flow feature start fsa-123--short-description` giving a feature branch name of `feature/fsa-123--short-description`

2. You can merge into the UAT branch without a pull request to deploy your work to the dev site for testing.  When you
   are happy then form a pull request against develop for another team member to review.  Do not merge at this point,
   the code must not go to develop yet.
 
3. Update the ticket and get the approval from the client testing on dev env (UAT).  Still don't finish the feature branch,
   it still should not be in develop.  If the client is happy then the ticket moves to CAB approval.  A CAB justification
   describing the change needs to be written and added to the ticket. A template for this is below in the README.
   
4. Feature branches should only be finished when they have passed FSA's CAB approval process.  When we have CAB approval 
   approved pull request against develop, the feature branch can be merged into develop ready for the next release. This
   releases the changed code to the test environment for any final review.
   
### Large pieces of work.

For large pieces of work formed of several tickets it is better to form a separate release branch to merge tickets
into and give that branch it's own environment on the Acquia server instead of using UAT on dev.  Pull requests can
be formed from the difference between individual features and this new branch and can be reviewed and merged without
CAB approval since they form part of a larger whole which will only be merged into develop once it is all completed.

### UAT environment renewal.

This branch can be destroyed and replaced with code from develop at any time to keep it fresh.

## Hosting.

This project is hosted on **Acquia**.

## Deployment.

The `bitbucket-pipelines.yml` file describes the build process which is execute on commit to specified branches in BitBucket.

You must create a tag release before release.  The production environment should be tracking the latest tag release.
Tag releases must only be cut from the master branch.

## Environmental configuration management.

This project uses:

* Drupal 8's configuration management for exporting database artifacts to code.
* An organised approach to [settings.php environmental variables](https://www.deeson.co.uk/labs/site-configuration-strategy-or-how-manage-your-settingsphp-files) via the `src/settings` directory.
* Secure environment settings should not be in version control and managed via the hosting environment variables.

## Jira project management.

Tickets are managed in this [Jira project](https://deeson.atlassian.net/secure/RapidBoard.jspa?rapidView=332&projectKey=FSA)

## Site environments

- Production [www.food.gov.uk](https://www.food.gov.uk)
- Acquia Cloud domain: [http://foodgovuk.prod.acquia-sites.com](http://foodgovuk.prod.acquia-sites.com)
- Staging [http://fsauser:FCeDh4u&7n2p@foodgovukstg.prod.acquia-sites.com](http://foodgovukstg.prod.acquia-sites.com)
- Dev: [http://fsauser:FCeDh4u&7n2p@foodgovukdev.prod.acquia-sites.com](http://foodgovukdev.prod.acquia-sites.com)

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

## SMTP services

The site uses Office 365 SMTP services on the production environment. The smtp Drupal contrib module provides the interception/rerouting of messages from the applictation.

Dummy configuration is stored in the `config/default` folder to avoid storing SMTP credentials in VCS.

The specific variable values for host, username, password etc are managed through environmental variables. These are defined as key/pair values in the Acquia control panel (specific per environment) or can be defined in the `.ddev/docker-compose.override.yaml` file for local debugging.

**Values that contain a `$` character need a preceeding `\` escape character in Acquia, and docker-composer expects double-dollar `$$` to prevent variable substitution taking place.**

> **Never store these - or other sensitive - values in the repository in a .env file, config export or otherwise. They could result in the SMTP service being used to handle or deliver spam**

## CAB release statement.

In order to get approval for a release you will need to provide the following on the Jirs ticket for the release:

### Justification.

The release contains the development work to implement the **Feature name** feature for the old.food.gov.uk website.

*Short description of feature.*

See [Link to Jira ticket] for full details.

### Implementation plan.

1. Merge the completed feature work in the develop branch into the master branch in the Acquia git version control repository

2. Cut a new release tag from the master branch (version number will be **enter next release tag**)

3. Backup the production database through the Acquia interface

4. Trigger the Acquia release process specifying the new tag release number. This process switches the code on the production environment then runs through the post release script that ensures all database updates specified by the release are applied then clears all caches.

### Risk and impact analysis.

Very low risk to existing page content types on the site. The features have been tested on the preproduction environments during development.

### Test plan.

1. *Note any manual testing that may be required post release*

### Back-out plan

1. Roll back tag code release

2. Restore last backup of the database

### Documentation

Description of the features provided by release **enter release version** are described in the Jira project management system for this project. The release notes are provided here (requires access to the project Jira):

*Provide link to Jira release notes*