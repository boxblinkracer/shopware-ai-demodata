#
# Makefile
#

.PHONY: help
.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# ------------------------------------------------------------------------------------------------------------

prod: ## Installs all production dependencies
	composer install --no-dev

dev: ## Installs all dev dependencies
	composer install

clean: ## Clears all dependencies
	rm -rf vendor/*

# ------------------------------------------------------------------------------------------------------------

phpcheck: ## Starts the PHP syntax checks
	@find . -name '*.php' -not -path "./vendor/*" -not -path "./tests/*" | xargs -n 1 -P4 php -l

csfix: ## Starts the PHP CS Fixer
	@PHP_CS_FIXER_IGNORE_ENV=1 php vendor/bin/php-cs-fixer fix --config=./.php_cs.php --dry-run

stan: ## Starts the PHPStan Analyser
	@php vendor/bin/phpstan analyse -c ./.phpstan.neon

phpunit: ## Starts all PHPUnit Tests
	@XDEBUG_MODE=coverage php vendor/bin/phpunit --configuration=phpunit.xml --coverage-html ./.reports/phpunit/coverage

# ------------------------------------------------------------------------------------------------------------

pr: ## Prepares everything for a Pull Request
	@PHP_CS_FIXER_IGNORE_ENV=1 php vendor/bin/php-cs-fixer fix --config=./.php_cs.php
	@make phpcheck -B
	@make stan -B
	@make phpunit -B

# ------------------------------------------------------------------------------------------------------------

release: ## Builds a PROD version and creates a ZIP file in plugins/.build.
	make clean
	make prod
	mkdir -p ./.build
	rm -rf ./.build/AIDemoData.zip || true;
	# -------------------------------------------------------------------------------------------------
	@echo "INSTALL PRODUCTION DEPENDENCIES"
	make prod -B
	# -------------------------------------------------------------------------------------------------
	@echo "CREATE ZIP FILE"
	zip -qq -r -0 ./.build/AIDemoData.zip . -x '*.git*' '*.github*' '*devops*' '*.idea*' '*.build*' '*node_modules*' '*makefile*' '*phpunit.xml*' '*.php_cs.php*' '*.phpstan.neon*' '*.eslintrc.json*' '*.prettierrc.json*' '*package.json*' '*package-lock.json*'
	@echo ""
	@echo "CONGRATULATIONS"
	@echo "The new ZIP file is available"
