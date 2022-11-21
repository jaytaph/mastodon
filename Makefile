.SILENT:

SHELL=/usr/bin/env bash -O globstar

all: help

test: test_phpcs test_phpstan test_phpmd test_unit test_psalm ## Runs tests

test_phpcs:
	source test-utils.sh ;\
	section "PHPCS" ;\
	vendor/bin/phpcs -p

test_phpstan:
	source test-utils.sh ;\
	section "PHPSTAN" ;\
	vendor/bin/phpstan analyse

test_psalm:
	source test-utils.sh ;\
	section "PSALM" ;\
	vendor/bin/psalm --threads=1

test_phpmd:
	source test-utils.sh ;\
	section "PHPMD" ;\
	vendor/bin/phpmd src/ text ruleset.phpmd.xml

test_unit: ## Run unit tests
	source test-utils.sh ;\
	section "PHPUNIT" ;\
	vendor/bin/phpunit --testsuite "Leita.io Unit Test Suite"

help: ## Display available commands
	echo "Available make commands:"
	echo
	grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
