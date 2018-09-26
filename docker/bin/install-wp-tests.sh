#!/usr/bin/env bash

WORDPRESS_VERSION=${WORDPRESS_VERSION-latest}
WORDPRESS_DB_NAME=${WORDPRESS_DB_NAME-wordpress}
WORDPRESS_DB_USER=${WORDPRESS_DB_USER-wordpress}
WORDPRESS_DB_PASS=${WORDPRESS_DB_PASS-password}
WORDPRESS_DB_HOST=${WORDPRESS_DB_HOST-localhost}
WORDPRESS_TEST_HARNESS_DIR=${WORDPRESS_TEST_HARNESS_DIR-/tmp/wordpress-tests-lib/}

download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

if [[ $WORDPRESS_VERSION =~ [0-9]+\.[0-9]+(\.[0-9]+)? ]]; then
	WP_TESTS_TAG="tags/$WORDPRESS_VERSION"
elif [[ $WORDPRESS_VERSION == 'nightly' || $WORDPRESS_VERSION == 'trunk' ]]; then
	WP_TESTS_TAG="trunk"
else
	# http serves a single offer, whereas https serves multiple. we only want one
	download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
	grep '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json
	LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "Latest WordPress version could not be found"
		exit 1
	fi
	WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

set -ex

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# set up testing suite if it doesn't yet exist
	if [ ! -d $WORDPRESS_TEST_HARNESS_DIR ]; then
		# set up testing suite
		mkdir -p $WORDPRESS_TEST_HARNESS_DIR
		svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ $WORDPRESS_TEST_HARNESS_DIR/includes
		svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/ $WORDPRESS_TEST_HARNESS_DIR/data
	fi

	if [ ! -f wp-tests-config.php ]; then
		download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WORDPRESS_TEST_HARNESS_DIR"/wp-tests-config.php
		# remove all forward slashes in the end
		WORDPRESS_DIR=$(echo $WORDPRESS_DIR | sed "s:/\+$::")
		sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WORDPRESS_DIR/':" "$WORDPRESS_TEST_HARNESS_DIR"/wp-tests-config.php
		sed $ioption "s/youremptytestdbnamehere/$WORDPRESS_DB_NAME/" "$WORDPRESS_TEST_HARNESS_DIR"/wp-tests-config.php
		sed $ioption "s/yourusernamehere/$WORDPRESS_DB_USER/" "$WORDPRESS_TEST_HARNESS_DIR"/wp-tests-config.php
		sed $ioption "s/yourpasswordhere/$WORDPRESS_DB_PASS/" "$WORDPRESS_TEST_HARNESS_DIR"/wp-tests-config.php
		sed $ioption "s|localhost|$WORDPRESS_DB_HOST|" "$WORDPRESS_TEST_HARNESS_DIR"/wp-tests-config.php
	fi

}

install_test_suite
