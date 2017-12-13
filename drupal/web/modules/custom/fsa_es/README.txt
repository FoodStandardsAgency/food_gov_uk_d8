How to use Elasticsearch in FSA site?

1. Install Elasticsearch by running 'vagrant provision' (in local development environment)
2. Make sure fsa_es module is enabled (should be fine if you've run 'drush cim')
3. Make sure you have imported some amount of fsa_establisment entities into Drupal
4. Execute the following commands to setup the indices and index the content:
    - drush eshd fsa_ratings_index -y
    - drush eshs -y
    - drush eshr fsa_ratings_index
    - drush cron
5. 'drush cron' command will only index approximately 100 items at once. If you need to
   speed up the indexing process, run 'watch -n1 drush cron'. This will run 'drush cron'
   command periodically with 1 second interval between executions.
