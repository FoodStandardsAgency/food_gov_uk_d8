#!/bin/sh
# This file will sync local development environment with the stage server
# SQL from the server + rsync.

drush sql-sync @fsa.stage @fsa.local --structure-tables-list=cache,cache_*,history,search_*,sessions,watchdog --sanitize
echo 'SQL sync ready.';

drush rsync @fsa.stage:%files/ drupal/files/
echo 'RSync ready.';

# Set UID1 password to 'root'
#drush @fsa.local sqlq "UPDATE users SET name = 'root' WHERE name = 'admin'"
drush @fsa.local sqlq "UPDATE users_field_data SET mail = 'user@example.com' WHERE name != 'admin'"
drush @fsa.local sqlq "UPDATE users_field_data SET init = '' WHERE name != 'admin'"
drush @fsa.local sqlq "UPDATE users_field_data SET pass = '' WHERE name != 'admin'"
drush @fsa.local upwd admin --password=admin
echo 'Truncated emails and passwords from the database.';

# Download Devel
# drush @fsa.local dl devel -y;

# Download maillog to prevent emails being sent
#drush @fsa.local dl maillog -y;

# Set maillog default development environment settings
#drush @fsa.local vset maillog_devel 1;
#drush @fsa.local vset maillog_log 1;
#drush @fsa.local vset maillog_send 0;

# Enable Devel and UI modules
# drush @fsa.local en field_ui devel views_ui context_ui feeds_ui rules_admin dblog --yes;
# echo 'Enabled Devel and Views+Context+Feeds+Rules UI modules.';

# Disable google analytics
# drush @fsa.local dis googleanalytics --yes;
# echo 'Disabled Google Analytics.';

# Set site email address to admin@example.com
#drush @fsa.local vset site_mail "admin@example.com"

# Set imagemagick convert path
# drush @fsa.local vset imagemagick_convert "/opt/local/bin/convert"

#Enable stage file proxy
#drush @fsa.local pm-download stage_file_proxy;
#drush @fsa.local pm-enable --yes stage_file_proxy;
#drush @fsa.local cset --yes stage_file_proxy.settings origin "https://wundertools.site"
#echo "Enabled stage file proxy so you won't need the files locally, jeee!"

# Clear caches
drush @fsa.local cr all;

# FINISH HIM
#say --voice=Zarvox "Sync is now fully completed."
