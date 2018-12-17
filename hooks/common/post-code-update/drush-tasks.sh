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
  drush @$drush_alias en -y shield
fi

# Clear all caches.
drush @$drush_alias cr
