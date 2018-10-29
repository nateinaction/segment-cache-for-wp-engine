.PHONY: test build

# User editable vars
PLUGIN_NAME := segment-cache-for-wp-engine

# Shortcuts
DOCKER_RUN := @docker run --rm -v `pwd`:/workspace
PHPCS_DOCKER_IMAGE := wpengine/phpcs --standard=./test/phpcs.xml --warning-severity=8
WORDPRESS_INTEGRATION_DOCKER_IMAGE := nateinaction/wordpress-integration
COMPOSER_DOCKER_IMAGE := composer
COMPOSER_DIR := -d "/workspace/"
BUILD_DIR := ./build

# Commands
all: lint verify_new_version composer_install test build

lint:
	$(DOCKER_RUN) $(PHPCS_DOCKER_IMAGE) .

phpcbf:
	$(DOCKER_RUN) --entrypoint "/composer/vendor/bin/phpcbf" $(PHPCS_DOCKER_IMAGE) .

composer_install:
	$(DOCKER_RUN) $(COMPOSER_DOCKER_IMAGE) install $(COMPOSER_DIR)

composer_update:
	$(DOCKER_RUN) $(COMPOSER_DOCKER_IMAGE) update $(COMPOSER_DIR)

test:
	$(DOCKER_RUN) -it $(WORDPRESS_INTEGRATION_DOCKER_IMAGE) "./vendor/bin/phpunit" -c "./test/phpunit.xml" --testsuite="integration-tests"

verify_new_version: create_version_file
	@if curl -sI "https://api.github.com/repos/nateinaction/$(PLUGIN_NAME)/releases/tags/v$(shell make get_version)" \
	| grep -q '404 Not Found'; then exit; fi; echo "Version $(shell make get_version) already exists."; exit 1;

create_version_file:
	@mkdir -p $(BUILD_DIR)
	@awk '/Version/{printf $$NF}' $(PLUGIN_NAME).php > $(BUILD_DIR)/VERSION

get_version: create_version_file
	@cat $(BUILD_DIR)/VERSION

build: create_version_file
	@rm -rf $(BUILD_DIR)/$(PLUGIN_NAME)
	@rm -rf $(BUILD_DIR)/$(PLUGIN_NAME)-$(shell make get_version).zip
	@mkdir -p $(BUILD_DIR)/$(PLUGIN_NAME)
	@rsync -rR composer.json composer.lock $(PLUGIN_NAME).php src/ $(BUILD_DIR)/$(PLUGIN_NAME)/
	$(DOCKER_RUN) $(COMPOSER_DOCKER_IMAGE) install -d /workspace/$(BUILD_DIR)/$(PLUGIN_NAME) --no-dev --prefer-dist --no-interaction
	@rm $(BUILD_DIR)/$(PLUGIN_NAME)/composer.json $(BUILD_DIR)/$(PLUGIN_NAME)/composer.lock
	@cd $(BUILD_DIR)/ && zip -r $(PLUGIN_NAME)-$(shell make get_version).zip $(PLUGIN_NAME)

wordpress_org_deploy:
	# passing this for now
