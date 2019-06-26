#!/usr/bin/env bash

set -e

script_path=$(dirname $0)
working_dir=$(pwd)
cd "$script_path"
cd ../..
repo_root=$(pwd)

source "$repo_root/.build.env"

read -p "Enter Your drupal module name (e.g. warden): "  module
echo "Installing ${module} ..."

docker run -ti -v $repo_root:/var/www/html -w /var/www/html "$drupal_build_php_tag" /bin/bash -c "composer require drupal/${module}"