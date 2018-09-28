#!/usr/bin/env bash

PLUGIN_NAME=${PLUGIN_NAME-segment-cache-for-wp-engine}
WORDPRESS_ORG_USERNAME=${WORDPRESS_ORG_USERNAME-nateinaction}
WORDPRESS_ORG_PASSWORD=${WORDPRESS_ORG_PASSWORD-password}
BUILD_DIR=${BUILD_DIR-build}
BUILD_VERSION=${BUILD_VERSION-$(cat ${BUILD_DIR}/VERSION)}
SVN_DIR=${SVN_DIR-svn}

# Checkout the SVN repo
rm -rf ${SVN_DIR}
svn co -q "http://svn.wp-plugins.org/${PLUGIN_NAME}" ${SVN_DIR}

# Remove old trunk assets while preserving .svn
shopt -s extglob
rm -rf ${SVN_DIR}/trunk/!(.svn)

# Sync and tag current
rsync -r ${BUILD_DIR}/${PLUGIN_NAME}/ ${SVN_DIR}/trunk/
svn cp ${SVN_DIR}/trunk/ ${SVN_DIR}/tags/${BUILD_VERSION}/

# Add new and remove files from SVN repo
svn stat ${SVN_DIR} | grep '^?' | awk '{print $2}' | xargs -I x ${SVN_DIR} add x@
svn stat ${SVN_DIR} | grep '^!' | awk '{print $2}' | xargs -I x ${SVN_DIR} rm --force x@

# Commit changes to SVN
svn ci --no-auth-cache --username ${WORDPRESS_ORG_USERNAME} --password ${WORDPRESS_ORG_PASSWORD} ${SVN_DIR} -m "Deploy version ${BUILD_VERSION}"
