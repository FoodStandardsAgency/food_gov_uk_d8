FSA Ratings import
=======

FSA RAtings import module uses Drupal Migrate API to fetch `fsa_establishment`
and `fsa_authority` content entities.

FHRS rating API documentation: 
[api.ratings.food.gov.uk](http://api.ratings.food.gov.uk) 

### Import/migrate Rating content

* Import authorities and establishments:
  * `drush mi --tag=authorities`
  * `drush mi --tag=establishments`
* To import only specific language content use tags
  * `drush mi --tag=english` or `drush mi --tag=welsh`

* For more verbose import use `--feedback`, e.g.
  * `drush mi --tag=establishments --feedback="5000 items"`

* If import fails or is stopped set back to idle:
  * `drush mrs fsa_establishment` or `drush mrs fsa_authority`

* The process can be memory-consuming. Especially on full reimport it may be 
required to run the command with physical memory limit:
`php -dmemory_limit=-1 /usr/lib/composer/vendor/bin/drush mi fsa_establishment`

### Establishment full import and update time window control override

By default only establishment updates are fetched from the API. 
Current update mode is shown at `/admin/config/fsa/ratings`

Update mode and time window can be controlled with Drupal state variables.

##### Update time window 
Override the default update time window:  
`drush sset fsa_rating_import.updated_since "2018-01-30"`

To use default (-1 week) just delete the state:  
`drush sdel fsa_rating_import.updated_since`

##### Toggle update/full import mode:  
Enable full import mode:  
`drush sset fsa_rating_import.full_import 1`

Disable full import mode:  
`drush sdel fsa_rating_import.full_import`

### Development options 

To import smaller batch of content for testing/development add following 
line(s) to your local `settings.local.php` file:

`$config['fsa_ratings_import']['import_mode'] = 'development';`

`$config['fsa_ratings_import']['import_random'] = 'TRUE';`
