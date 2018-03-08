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

### Removal of non-existing establishments

FHRS API does not have permanent establishment id's (FHRSID) and establishments 
may get deleted over time from the API. To overcome this issue an establishment 
deletion feature was added to module.

Every day a full set of establishments is fetched from API (Establishments/basic
endpoint) and stored in:
 * as paged files in `public://api/[date]/fhrs_results_[page].json`
 * as FHRSID and date in `fsa_establishment_api_import` table.
 
On cron run the following steps are performed:
* Page number to fetch is determined from previous stored paged result .json 
file (if last json is `fhrs_results_003.json`, then API is queried with page 4) 
and results from API are downloaded to `public://api/[date]`
* Saved JSON file is parsed and FHRS values are merged into 
`fsa_establishment_api_import` table (existing FHRS values are unique, values 
are added or updated if they exist)
* When the full set of establishments are fetched:
  * entry `api_fetch_finish_last_date` is set to current date in 
  `fsa_ratings_import` key/value collection (this allows skip the process of 
  determining the next page, etc)
  * all values in `fsa_establishment_api_import` table are matched against 
  migrated establishment entities in `migrate_map_fsa_establishment` tables and 
  entities that are defined as migrated but not available in fetched set are 
  removed as non-existing
  * entry `entity_purge_finish_last_date` is set to current date in 
  `fsa_ratings_import` key/value collection (this allows skip the process of 
  finding the result-set/migrated entity diff, etc)

### Development options 

To import smaller batch of content for testing/development add following line(s) to your local `settings.local.php` file:

`$config['fsa_ratings_import']['import_mode'] = 'development';`

`$config['fsa_ratings_import']['import_random'] = 'TRUE';`