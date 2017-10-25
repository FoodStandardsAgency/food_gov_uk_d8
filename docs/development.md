Development instructions
========================

#### Database syncing
Use `./syncdb.sh [SOURCE] [TARGET]` for syncing. Second parameter is optional.

For example to sync staging to local run following outside the box:
```bash
./syncdb.sh stage
```

#### Configuration management

* Project uses `config_readonly` module to prevent configuration changes on other than local dev environments. See `$settings['config_readonly']` in `settings.php`
* Project uses `config_split` module to prevent development module configurations exports to live environments.
  * With `drush >= 8.1.10` normal `drush cex` & `drush cim` procedure can be used.
  * Earlier drush versions require use of `drush csex|csim`

#### Local settings.php overrides

Copy local `drupal/conf/settings.local.php` [template from here](settings.local.php.txt).

#### Drupal console & codeception on local environment

Drupal console or codeception do not work out of the box as they cannot read `getenv()` from the `$databases` array. Workaround is to export db user & password to bash:
 ```
 export DB_USER_DRUPAL=drupal
 export DB_PASS_DRUPAL=password
 ```
 

FSA Rating content/entities
---------------------
 
FHRS rating API: [api.ratings.food.gov.uk](http://api.ratings.food.gov.uk) 

To import smaller batch of content for testing/development use following line(s) in your local `settings.local.php` file:

`$config['fsa_ratings_import']['import_mode'] = 'development';`

`$config['fsa_ratings_import']['import_random'] = 'TRUE';`

### Import/migrate Rating content

* Import authorities and establishments:
  * `drush mi --tag=authorities`
  * `drush mi --tag=establishments`
* To import only specific language content use tags
  * `drush mi --tag=english` or `drush mi --tag=welsh`

* For more verbose import use `--feedback`, e.g.
  * `drush mi --tag=establishments --feedback="1000 items"`

* If import fails or is stopped set back to idle:
  * `drush mrs fsa_establishment` or `drush mrs fsa_authority`


FSA Alerts API
---------------------

[Alert API documentation](http://fsa-staging-alerts.epimorphics.net/food-alerts/ui/reference)

Alert API data is imported to Drupal `alerts_allergen` taxonomy and `alert` nodes.

### Import/migrate Alerts from the API
 
Ensure the Alert API base path is set in FSA Alerts configuration `/admin/config/fsa-alerts`. Drupal status page will display error if this is not done.

* Import allergens:
  * `drush mi --tag=allergens`
  * Or update existing entries: `drush mi --tag=allergens --update`
* Import alerts:
  * `drush mi --tag=alerts`
  * Or update existing entries: `drush mi --tag=alerts --update`

* If import fails or is stopped set back to idle:
  * `drush mrs fsa_alerts` or `drush mrs fsa_allergens`
  
* Remove/rollback migrated content with `drush mr --tag=[allergens|alerts]`
  * Notice this will completely delete the created entries and next migrate recreates the entity id's.

FSA Ratings search / Elasticsearch
---------------------

Ratings search is located at domain.com/ratings/search.

Drush commands to rebuild ES index.

`drush eshd fsa_ratings_index -y; drush eshs; drush eshr fsa_ratings_index;` 

And `drush cron`, multiple times if there are lots of establishment entities.
