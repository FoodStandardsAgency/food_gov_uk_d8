Development instructions
========================

#### Database syncing
Use wundertools default `./syncdb.sh -s [SOURCE] -t [TARGET]` for syncing. The target parameter (`-t`) is optional.

For example to sync staging to local run following outside the box:
```bash
`./syncdb.sh -s stage -t local`
```

If this sputters errors you may need to do `export WKV_SITE_ENV=local`.

*NB!* for a quick copying of content to local you can ignore the establishment data (+500K entities with field values) 
by temporarily editing `./syncdb.sh` around line `131`:
```
drush $SOURCE dumpdb --structure-tables-list=fsa_establish*,migrate_message_fsa_establish*,cache,cache_*,history,sessions,watchdog --dump-dir=$SYNCDIR
```

Once sync is done you need to run the ratings migrate commands in order to have ratings data locally: 
[fsa_ratings_import/README.md](/drupal/web/modules/custom/fsa_ratings_import/README) 

#### Configuration management

* Project uses `config_readonly` module to prevent configuration changes on other than local dev environments. See `$settings['config_readonly']` in `settings.php`
* Project uses `config_split` module to prevent development module configurations exports to live environments.
  * With `drush >= 8.1.10` normal `drush cex` & `drush cim` procedure can be used.
  * Earlier drush versions require use of `drush csex|csim`

#### Local settings.php overrides

Copy local settings overrides file `drupal/conf/settings.private.php` [template from here](settings.private.php.txt).

#### Drupal console & codeception on local environment

Drupal console or codeception do not work out of the box as they cannot read `getenv()` from the `$databases` array. Workaround is to export db user, pass and host to bash:
 ```
 export DB_USER_DRUPAL=drupal
 export DB_PASS_DRUPAL=password
 export DB_HOST_DRUPAL=localhost
 ```
 
FHRS Rating Search
---------------------
 
FHRS Establishment and authority data is pulled from [FHRS rating API](http://api.ratings.food.gov.uk) with Drupal Migrate API.
 
* Refer to [fsa_ratings/README.md](/drupal/web/modules/custom/fsa_ratings/README.md) for entity documentation.
* Refer to [fsa_ratings_import/README.md](/drupal/web/modules/custom/fsa_ratings_import/README.md) for import documentation. 


FSA Alerts API
---------------------

[Alert API documentation](http://fsa-staging-alerts.epimorphics.net/food-alerts/ui/reference)

Alert API data is imported to Drupal `alerts_allergen` taxonomy and `alert` nodes.

* Refer to [fsa_alerts/README.md](/drupal/web/modules/custom/fsa_alerts/README.md) for documentation.


FSA Ratings search / Elasticsearch
---------------------

* Ratings search is located at `/ratings/search`.

* The rating search is implemented with `fsa_ratings` module.

* FSA Establishments are indexed to Elasticsearch with FSA Elasticsearch integration (`fsa_es`) module, refer to [README.md](/drupal/web/modules/custom/fsa_es/README.md) for documentation. 


