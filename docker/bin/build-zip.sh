#!/usr/bin/env bash

# This file is executed inside of the docker container during the build step
PLUGIN_NAME="segment-cache-for-wp-engine"
PLUGIN_VERSION=$(sudo -u www-data wp plugin get ${PLUGIN_NAME} --format=json | python3 -c 'import sys, json; print(json.load(sys.stdin)["version"])')
zip -r ../artifacts/${PLUGIN_NAME}-${PLUGIN_VERSION}.zip ${PLUGIN_NAME}
