# User editable vars
PLUGIN_NAME ?= segment-cache-for-wp-engine
WORDPRESS_VERSION ?= latest
WORDPRESS_DB_NAME ?= wordpress
WORDPRESS_DB_USER ?= wordpress
WORDPRESS_DB_PASSWORD ?= password
WORDPRESS_DB_HOST ?= localhost
WORDPRESS_DIR ?= /tmp/wordpress
WORDPRESS_TEST_HARNESS_DIR ?= /tmp/wordpress-test-harness
BIN_DIR ?= /usr/local/bin
BUILD_DIR ?= build
SVN_DIR ?= svn

# Shortcuts
DOCKER_COMPOSE := @docker-compose -f docker/docker-compose.yml
DOCKER_EXEC := exec -u www-data wordpress /bin/bash -c

# Makefile phony
.PHONY: test build

all: docker_start docker_all

shell:
	$(DOCKER_COMPOSE) $(DOCKER_EXEC) "/bin/bash"

docker_setup_wp_core:
	$(DOCKER_COMPOSE) $(DOCKER_EXEC) "make setup_wp_core"

docker_start:
	$(DOCKER_COMPOSE) up -d --build

docker_stop:
	$(DOCKER_COMPOSE) stop

docker_clean:
	$(DOCKER_COMPOSE) stop | true
	$(DOCKER_COMPOSE) rm -v

docker_all:
	$(DOCKER_COMPOSE) $(DOCKER_EXEC) "pwd; make before_install install lint test build"

docker_test:
	$(DOCKER_COMPOSE) $(DOCKER_EXEC) "make lint test"

docker_build:
	$(DOCKER_COMPOSE) $(DOCKER_EXEC) "make build"

docker_phpcbf:
	$(DOCKER_COMPOSE) $(DOCKER_EXEC) "make phpcbf"

before_install: verify_new_version composer_self_update

create_version_file:
	mkdir -p $(BUILD_DIR)
	awk '/Version/{printf $$NF}' $(PLUGIN_NAME).php > $(BUILD_DIR)/VERSION

verify_new_version: create_version_file
	if curl -sI "https://api.github.com/repos/nateinaction/$(PLUGIN_NAME)/releases/tags/v$(shell cat $(BUILD_DIR)/VERSION)" \
	| grep -q '404 Not Found'; then exit; fi; echo "Version $(shell cat $(BUILD_DIR)/VERSION) already exists or unexpected Github API return."; exit 1;

composer_self_update:
	composer self-update

install: composer_install install_wp_cli install_wp install_wp_test_harness

composer_install:
	composer install -o --prefer-dist --no-interaction

install_wp_cli:
	curl -o $(BIN_DIR)/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x $(BIN_DIR)/wp

install_wp: setup_wp_core setup_wp_config setup_wp_db

setup_wp_core:
	wp core download --force --path="$(WORDPRESS_DIR)"

setup_wp_config:
	wp config create --path="$(WORDPRESS_DIR)" --force \
		--dbname="${WORDPRESS_DB_NAME}" \
		--dbuser="${WORDPRESS_DB_USER}" \
		--dbpass="${WORDPRESS_DB_PASSWORD}" \
		--dbhost="${WORDPRESS_DB_HOST}"

setup_wp_db:
	wp db reset --path="$(WORDPRESS_DIR)" --yes
	wp core install --path="$(WORDPRESS_DIR)" --skip-email \
		--url="http://localhost" \
		--title="Test" \
		--admin_user="test" \
		--admin_password="test" \
		--admin_email="test@test.com"

install_wp_test_harness:
	./bin/install-wp-test-harness.sh

lint:
	./vendor/bin/phpcs --standard=./test/phpcs.xml --warning-severity=8 .

phpcbf:
	./vendor/bin/phpcbf --standard=./test/phpcs.xml .

test:
	./vendor/bin/phpunit -c ./test/phpunit.xml --testsuite=$(PLUGIN_NAME)-unit-tests

build: create_version_file
	rm -rf $(BUILD_DIR)/$(PLUGIN_NAME)
	rm -rf $(BUILD_DIR)/$(PLUGIN_NAME).zip
	mkdir -p $(BUILD_DIR)/$(PLUGIN_NAME)
	cp -rt $(BUILD_DIR)/$(PLUGIN_NAME) composer.json composer.lock $(PLUGIN_NAME).php src/
	composer install -d $(BUILD_DIR)/$(PLUGIN_NAME) --no-dev --prefer-dist --no-interaction
	cd $(BUILD_DIR)/ && zip -r $(PLUGIN_NAME).zip $(PLUGIN_NAME)

wordpress_org_deploy:
	#./bin/wordpress-org-deploy.sh # disabled for now
