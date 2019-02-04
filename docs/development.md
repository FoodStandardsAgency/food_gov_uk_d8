# Development instructions

We use (DDEV)[https://ddev.readthedocs.io] for this project, and although any container based solution ought to work too you will need to adapt config as needed if you're more productive with Lando/Docksal/DrupalVM or vanilla docker-compose.

## Get started

> NB: You'll need a database before this step :)

```
# Start ddev containers
ddev start
# Wait for docker images to pull/expand/start - could take up to 30 mins on first start dependent on bandwidth and system resources.
# Don't worry, it's really fast after you've got the images.
ddev import-db --src path-to-your-sql-file
```

`.ddev/config.yaml` shows a number of tasks that will execute before the services start and after a database import.

## XDebug

PHP-FPM sometimes uses port 9000 which is also frequently the default port for XDebug, making for a confusing or broken development experience.

For VS Code with the DDEV tool, see https://ddev.readthedocs.io/en/latest/users/step-debugging/#vscode - in particular the additions from https://ddev.readthedocs.io/en/latest/users/snippets/vscode_listen_for_xdebug_snippet.txt to add to your project config.

## Drush

DDEV says you can use your host's drush command but your milage may vary. Best to stick with the `ddev exec` wrapper for local work for now.

Accessing dev/stage/prod environments remains the same - it's all drush over SSH and can be run from the host. Eg: `drush @foodgovuk.[env] command args...`

> Consider using drush launcher rather than globally installed drush to interact with your projects. This ensures you use a version of drush that the project defines and keeps under `/vendor/bin/drush` to avoid any versioning issues.

## Database syncing

`ddev import-db --src path-to-your-sql-file`

*Important*: Notify settings are defined as Drupal state in the db: make sure your local will not send alerts to subscribers after copying database from production. See `/admin/config/fsa/notify` for these settings. If using ddev this will execute post-import scripts to do this for you.

Once sync is done you need to run the ratings migrate commands in order to have ratings data locally:
[fsa_ratings_import/README.md](/docroot/modules/custom/fsa_ratings_import/README)

## Configuration management

* Project uses `config_readonly` module to prevent configuration changes on other than local dev environments. See `$settings['config_readonly']` in `settings.php`
* Project uses `config_split` module to prevent development module configurations exports to live environments.
  * With `drush >= 8.1.10` normal `drush cex` & `drush cim` procedure can be used.
  * Earlier drush versions require use of `drush csex|csim`