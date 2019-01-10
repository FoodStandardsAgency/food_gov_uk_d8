FSA Alerts Import
=======

[Alert API documentation
](http://fsa-staging-alerts.epimorphics.net/food-alerts/ui/reference)

Alert API data is imported to Drupal `alerts_allergen` taxonomy and 
`alert` nodes.

### Import/migrate Alerts from the API
 
Ensure the Alert API base path is set in FSA Alerts configuration 
`/admin/config/fsa-alerts`. Drupal status page will display error if this is 
not done.

* Import allergens:
  * `drush mi --tag=allergens`
  * Or update existing entries: `drush mi --tag=allergens --update`
* Import alerts:
  * `drush mi --tag=alerts`
  * Or update existing entries: `drush mi --tag=alerts --update`

* If import fails or is stopped set back to idle:
  * `drush mrs fsa_alerts` or `drush mrs fsa_allergens`
  
* Remove/rollback migrated content with `drush mr --tag=[allergens|alerts]`
  * Notice this will completely delete the created e
  
### Migrate API customization and notes

* If migrate configurations in `/sync` are modified copy changes to module 
`/config` directory

* `/Plugin/migrate/process/AlertItemProperties.php` takes care of saving most 
of extra data to fields from each alert item.

* See `/Plugin/migrate/process/` for other process plugins altering the API data 
before saving to Drupal.

* See `/Plugin/Field/Fieldformatter` for custom formatting of stored API data.
  * AlertTypeFormatter to display alert type in uniform style accrossa the site
  * AlertJsonToHtml to display alert product detail raw json data in table
