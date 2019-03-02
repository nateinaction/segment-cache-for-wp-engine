.PHONY: test build

# User editable vars
PLUGIN_NAME := segment-cache-for-wp-engine

# Shortcuts
DOCKER_RUN := docker run --rm -v `pwd`:/workspace
WP_TEST_IMAGE := worldpeaceio/wordpress-integration
COMPOSER_IMAGE := -v ~/.composer/cache:/tmp/cache -w /workspace composer
BUILD_DIR := ./build

# Commands
all: verify_new_version composer_install lint test_integration build

clean:
	rm -rf build
	rm -rf vendor

lint:
	$(DOCKER_RUN) --entrypoint "/workspace/vendor/bin/phpcs" $(WP_TEST_IMAGE) .

phpcbf:
	$(DOCKER_RUN) --entrypoint "/workspace/vendor/bin/phpcbf" $(WP_TEST_IMAGE) .

composer_install:
	$(DOCKER_RUN) $(COMPOSER_IMAGE) install

composer_update:
	$(DOCKER_RUN) $(COMPOSER_IMAGE) update

test: lint test_integration

test_integration:
	$(DOCKER_RUN) $(WP_TEST_IMAGE) "./vendor/bin/phpunit --testsuite integration"

verify_new_version:
	@if curl -sI "https://api.github.com/repos/nateinaction/$(PLUGIN_NAME)/releases/tags/v$(shell make get_version)" \
	| grep -q '404 Not Found'; then exit; fi; echo "Version $(shell make get_version) already exists."; exit 1;

get_version:
	@awk '/Version/{printf $$NF}' $(PLUGIN_NAME).php

build:
	@rm -rf $(BUILD_DIR)/$(PLUGIN_NAME)
	@rm -rf $(BUILD_DIR)/$(PLUGIN_NAME)-$(shell make get_version).zip
	@mkdir -p $(BUILD_DIR)/$(PLUGIN_NAME)
	rsync -rR composer.json composer.lock $(PLUGIN_NAME).php src/ $(BUILD_DIR)/$(PLUGIN_NAME)/
	$(DOCKER_RUN) $(COMPOSER_IMAGE) install -d /workspace/$(BUILD_DIR)/$(PLUGIN_NAME) --no-dev --prefer-dist --no-interaction
	rm $(BUILD_DIR)/$(PLUGIN_NAME)/composer.json $(BUILD_DIR)/$(PLUGIN_NAME)/composer.lock
	@cd $(BUILD_DIR)/ && zip -r $(PLUGIN_NAME)-$(shell make get_version).zip $(PLUGIN_NAME)
