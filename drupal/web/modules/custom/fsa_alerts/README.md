FSA Alerts
=======

Creates Alert content related blocks
* Backlink from alert node
  * Extends over to News & Consultations backlink to avoid code duplication 
  (this coud probably be better to move in more universal module)
* Alert subscribe CTA
  * Block to display subscribe link
* Alert subscribe hero
  * Block to display alert pages hero content
  

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
