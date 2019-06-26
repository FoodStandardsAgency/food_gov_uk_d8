#!/bin/sh
# Cloud Hook: update-db

echo "Running cloud hooks from ${0##*/}"

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"
uri=""

case $target_env in
  "prod" )
    echo "Prod deploy script"
    uri="food.gov.uk"
    ;;
  "test" )
    echo "Test deploy script"
    uri="foodgovukstg.prod.acquia-sites.com"
    ;;
  "dev" )
    echo "Dev deploy script"
    uri="foodgovukdev.prod.acquia-sites.com"
    ;;
  "ode[0-9]+" )
    echo "${target_env} deployment script"
    uri="foodgovuk${target_env}.prod.acquia-sites.com"
    ;;
esac

# Update database.
drush @$site.$target_env cc drush --uri=$uri
echo "$site.$target_env: About to updatedb..."
drush @$site.$target_env updatedb --yes --uri=$uri --strict=0

# Configuration management import.
drush @$site.$target_env cc drush --uri=$uri
echo "$site.$target_env: Importing configuration management..."
drush @$site.$target_env cim --yes --uri=$uri --strict=0

if [ $target_env != "prod" ]; then
  # Enable some environment specific overrides; consider using config_split in future.
  drush @$site.$target_env en -y shield stage_file_proxy

  # Force any potentially dangerous variables to update.
  drush @$site.$target_env sset fsa_notify.collect_send_log_only TRUE
  drush @$site.$target_env sset fsa_notify.api test_key-6f00837a-4b8f-4ddd-ae96-ca2d3035fe57-cf19add9-e802-4fbf-8f92-dfb941ec8813
fi

# Clear all caches.
drush @$site.$target_env cr --uri=$uri --strict=0
