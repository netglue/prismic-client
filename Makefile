# Run `make` (no arguments) to get a short description of what is available
# within this `Makefile`.

help: ## shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
.PHONY: help

install: ## Install PHP dependencies
	composer install
.PHONY: install

update: ## Update PHP dependencies
	composer update
.PHONY: update

bump: ## Update PHP dependencies
	composer update
	composer bump -D
	composer update
.PHONY: update

clean: ## Clear out caches
	rm -rf .phpunit.cache
	rm -f .php_cs-cache
	vendor/bin/psalm --clear-cache

sa: ## Run static analysis checks
	vendor/bin/psalm --no-cache --threads=1
.PHONY: sa

cs: ## Run coding standards checks
	vendor/bin/phpcs
.PHONY: cs

test: ## Run unit tests
	vendor/bin/phpunit
.PHONY: test

qa: cs sa test ## Run all QA Checks
.PHONY: check

get-rector: ## Install rector as a dev dependency
ifeq (,$(wildcard ./vendor/bin/rector))
	composer require --dev rector/rector
endif
.PHONY: get-rector

remove-rector: ## Remove rector dependency
	composer remove --dev rector/rector
.PHONY: remove-rector

rector: get-rector ## Run Rector
	vendor/bin/rector
.PHONY: rector
