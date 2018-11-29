#!/bin/sh
#
# db-copy Cloud hook: db-scrub
#
# Scrub important information from a Drupal database.
#
# Usage: db-scrub.sh site target-env db-name source-env

site="$1"
target_env="$2"
db_name="$3"
source_env="$4"

echo "$site.$target_env: Scrubbing database $db_name"

# Switch off notify service for non-production environments.
drush @$site.$target_env sset fsa_notify.collect_send_log_only TRUE
drush @$site.$target_env sset fsa_notify.api test_key-6f00837a-4b8f-4ddd-ae96-ca2d3035fe57-cf19add9-e802-4fbf-8f92-dfb941ec8813
