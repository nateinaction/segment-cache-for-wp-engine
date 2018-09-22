SHELL := /bin/bash

plugin_name := segment-cache-for-wp-engine

plugin_dir := /var/www/html/wp-content/plugins/$(plugin_name)/
cd_plugin_dir := cd $(plugin_dir)

docker_compose := @docker-compose -f docker/docker-compose.yml
docker_exec := exec wordpress /bin/bash -c
docker_run_silent := @docker run --rm -t

www_data := @sudo -u www-data
cli_skip := --skip-themes --skip-plugins
cli_path := --path="/var/www/html/"

all: docker_start lint docker_install_wp docker_test docker_build

shell:
	$(docker_compose) $(docker_exec) "$(cd_plugin_dir); /bin/bash"

lint: lint_yaml lint_markdown lint_python lint_php

test: unit install_wp smoke

docker_start:
	$(docker_compose) up -d --build

docker_stop:
	$(docker_compose) stop

docker_clean:
	$(docker_compose) stop | true
	$(docker_compose) rm -v

docker_build:
	$(docker_compose) $(docker_exec) "$(cd_plugin_dir); make build"

docker_install_wp:
	$(docker_compose) $(docker_exec) "$(cd_plugin_dir); make install_wp"

docker_test:
	$(docker_compose) $(docker_exec) "$(cd_plugin_dir); make test"

docker_unit:
	$(docker_compose) $(docker_exec) "$(cd_plugin_dir); make unit"

docker_smoke:
	$(docker_compose) $(docker_exec) "$(cd_plugin_dir); make smoke"

lint_php:
	$(docker_compose) $(docker_exec) "$(plugin_dir)vendor/bin/phpcs --standard=$(plugin_dir)test/phpcs.xml --warning-severity=8 $(plugin_dir)"

docker_phpcbf:
	$(docker_compose) $(docker_exec) "$(plugin_dir)vendor/bin/phpcbf --standard=$(plugin_dir)test/phpcs.xml $(plugin_dir)"

lint_python:
	$(docker_run_silent) -v `pwd`:$(plugin_dir) wpengine/pylint:latest "$(plugin_dir)/test/smoke/" --errors-only

lint_markdown:
	@# exclude MD013 "line too long"
	@# exclude MD024 "allow different nesting"
	@# exclude MD046 "code block style"
	$(docker_run_silent) -v `pwd`:$(plugin_dir) wpengine/mdl:latest --rules ~MD013,~MD024,~MD046 "$(plugin_dir)/README.md"

lint_yaml:
	$(docker_run_silent) -v `pwd`:$(plugin_dir) wpengine/yamllint:latest "$(plugin_dir)/docker/"

smoke:
	python3 -m pytest -v -r s "$(plugin_dir)test/smoke/"

unit:
	$(plugin_dir)vendor/bin/phpunit -c "$(plugin_dir)test/phpunit.xml" --testsuite=$(plugin_name)-unit-tests

install_wp: setup_core setup_config setup_db

setup_core:
	$(www_data) wp core download $(cli_path) --force

setup_config:
	$(www_data) wp config create $(cli_path) --force \
		--dbname="${WORDPRESS_DB_NAME}" \
		--dbuser="${WORDPRESS_DB_USER}" \
		--dbpass="${WORDPRESS_DB_PASSWORD}" \
		--dbhost="${WORDPRESS_DB_HOST}"

setup_db:
	$(www_data) wp db reset $(cli_path) --yes
	$(www_data) wp core install $(cli_path) --skip-email \
		--url="http://localhost" \
		--title="Test" \
		--admin_user="test" \
		--admin_password="test" \
		--admin_email="test@test.com"
	$(www_data) wp plugin activate $(plugin_name) $(cli_path) --quiet

load_test_content:
	$(www_data) wp plugin install wordpress-importer --activate
	$(www_data) wp import $(plugin_dir)test/files/test-post-content.xml --authors=skip

place_test_mu_plugin:
	cp $(plugin_dir)test/files/test-server-var-set.php /var/www/html/wp-content/mu-plugins

remove_test_mu_plugin:
	rm -rf /var/www/html/wp-content/mu-plugins/test-server-var-set.php

build:
	mkdir -p build/$(plugin_name) artifacts
	cp -r {$(plugin_name).php,src/,composer.json,composer.lock} build/$(plugin_name)
	composer install -d build/$(plugin_name) --no-dev
	cd build/ && sh ../docker/bin/build-zip.sh
	rm -r build/
