Development instructions
========================

#### Local settings.php overrides

`web/sites/default/settings.local.php` is not versioned, copy a [template from here](settings.local.php.txt) and modify to your needs.

#### Drupal console & codeception on local environment

Drupal console or codeception do not work out of the box as they cannot read `getenv()` from the `$databases` array. Workaround is to export db user & password to bash:
 ```
 export DB_USER_DRUPAL=drupal
 export DB_PASS_DRUPAL=password
 ```
 

FHRS Rating content/entities
---------------------
 
[FHRS rating API](http://api.ratings.food.gov.uk/help) 

The migrate/import pulls ~520K establishments to the database. To avoid migrating everything to your local environment make sure you have following lines on your `settings.local.php` file:

```$config['fsa_ratings_import']['import_mode'] = 'development';``` 

And ```$config['fsa_ratings_import']['import_random'] = 'TRUE';``` if you want to import more variation to establishment entities.