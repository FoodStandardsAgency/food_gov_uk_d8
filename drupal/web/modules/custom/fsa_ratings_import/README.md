FSA Ratings import
=======

FSA RAtings import module uses Drupal Migrate API to fetch `fsa_establishment` and `fsa_authority` content entities.

FHRS rating API documentation: [api.ratings.food.gov.uk](http://api.ratings.food.gov.uk) 

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

* The process can be memory-consuming. Especially on full reimport it may be required to run the command with physical memory limit: `php -dmemory_limit=-1 /usr/lib/composer/vendor/bin/drush mi fsa_establishment`

### Establishment import/frequency note
Module implements an eventsubscriberwhich sets the API url start page offset
 to avoid bloating the memory with fetching all +500K entities at once.
 
* To handle with the memomry consumption the module currently increments the offset of API calls and sets it to a state.
 The state can be reset with `drush sset fsa_rating_api_offset 1` or use `drush sget fsa_rating_api_offset` to check the 
 current offset. 

### Development options 

To import smaller batch of content for testing/development add following line(s) to your local `settings.local.php` file:

`$config['fsa_ratings_import']['import_mode'] = 'development';`

`$config['fsa_ratings_import']['import_random'] = 'TRUE';`