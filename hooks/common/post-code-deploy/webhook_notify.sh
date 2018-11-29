#!/bin/sh
#
# Cloud Hook: post-code-deploy
#
# The post-code-deploy hook is run whenever you use the Workflow page to
# deploy new code to an environment, either via drag-drop or by selecting
# an existing branch or tag from the Code drop-down list. See
# ../README.md for details.
#
# Usage: post-code-deploy site target-env source-branch deployed-tag repo-url
#                         repo-type
set -x
site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"

# Load the webhook URL (which is not stored in this repo).
. $HOME/webhook_notify_settings

# Post deployment notice to webhook endpoint.
message=''

if [ "$source_branch" != "$deployed_tag" ]; then
  message="[DEPLOYMENT] ${source_branch} deployed to ${site} -- ${target_env}"
else
  message="[DEPLOYMENT] ${deployed_tag} deployed to ${site} -- ${target_env}"
fi

# Take care with variable expansion in Bash; must be in double quotes and sometimes curly braces too.
curl -X POST -H 'Content-type: application/json' -d "{\"text\": \"${message}\"}" $WEBHOOK_URL
