#!/usr/bin/make

SHELL = /bin/sh

.PHONY : help build latest install lowest test 'shell' clean
.DEFAULT_GOAL : help

help: ## Show this help
	@printf "\033[33m%s:\033[0m\n" 'Available commands'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[32m%-14s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build docker image for local development
	docker compose build --no-cache

init: clean ## Install regular PHP dependencies
	docker compose run app composer update --no-interaction --prefer-dist

test: ## Execute php tests and linters
	docker compose run app ./vendor/bin/phpunit -c phpunit.xml

cs-check: ## Execute PHPUnit tests
	docker compose run app ./vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix: ## Execute PHPUnit tests
	docker compose run app ./vendor/bin/php-cs-fixer fix --dry-run --diff

shell: ## Start shell into container with php
	docker compose run app sh

clean: ## Remove all dependencies and unimportant files
	-rm -Rf ./composer.lock ./vendor
