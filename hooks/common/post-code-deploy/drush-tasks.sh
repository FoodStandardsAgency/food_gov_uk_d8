#!/bin/sh
#
# Cloud Hook
#
# Run drush tasks all in the target environment. This script works as
# any Cloud hook.


# Map the script inputs to convenient names.
site=$1
target_env=$2
drush_alias=$site'.'$target_env

# Update database schema.
drush @$drush_alias updb -y

# Config import.
drush @$drush_alias cim -y

if [ $target_env != "prod" ]; then
  # Enable some environment specific overrides; consider using config_split in future.
  drush @$drush_alias en -y shield stage_file_proxy

  # Force any potentially dangerous variables to update.
  drush @$drush_alias sset fsa_notify.collect_send_log_only TRUE
  drush @$drush_alias sset fsa_notify.api test_key-6f00837a-4b8f-4ddd-ae96-ca2d3035fe57-cf19add9-e802-4fbf-8f92-dfb941ec8813
fi

# Clear all caches.
drush @$drush_alias cr
