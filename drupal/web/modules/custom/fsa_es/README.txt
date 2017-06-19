How to use Elasticsearch in FSA site?

1. Install Elasticsearch by running 'vagrant provision' (in local development environment)
2. Make sure fsa_es module is enabled (should be fine if you've run 'drush cim')
3. Make sure you have imported some amount of fsa_establisment entities into Drupal
4. Execute 'drush eshd fsa_ratings_index -y; drush eshs; drush eshr fsa_ratings_index;' and 'drush cron' multiple times to index the entities.