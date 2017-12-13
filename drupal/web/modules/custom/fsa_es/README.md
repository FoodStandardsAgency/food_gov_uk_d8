How to use Elasticsearch in FSA site?

1. Install Elasticsearch by running 'vagrant provision' (in local development environment)
2. Make sure `fsa_es` module is enabled (should be fine if you've run `drush cim`)
3. Make sure you have imported some amount of `fsa_establisment` entities into Drupal (you should have if you have fetched prod or stage db)
4. Execute the following commands to setup the indices and index the content:
    - `drush eshd fsa_ratings_index -y`
    - `drush eshs -y`
    - `drush eshr fsa_ratings_index`
    - `drush cron` 
        - OR `drush queue-run elasticsearch_helper_indexing`
5. Notes on indexing content to ES:
    - Full index rebuild takes approximately 1,5 to 2 hours (+500K establishments).
    - `drush cron` command will only index approximately 100 items at once. If you need to
   speed up the indexing process, run `watch -n1 drush cron`. This will run `drush cron`
   command periodically with 1 second interval between executions.
   - Alternatively run `watch -n1 drush queue-run elasticsearch_helper_indexing` (with `watch` to proceed after timeouts) 
   - Note you may want to clear the queue first:
      - `drush sqlq "DELETE from queue WHERE name = 'elasticsearch_helper_indexing';"`
