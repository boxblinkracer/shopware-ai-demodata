#
# Makefile
#

.PHONY: help
.DEFAULT_GOAL := help

help:
	@echo ""
	@echo "PROJECT COMMANDS"
	@echo "--------------------------------------------------------------------------------------------"
	@printf "\033[33mInstallation:%-30s\033[0m %s\n"
	@grep -E '^[a-zA-Z_-]+:.*?##1 .*$$' $(firstword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?##1 "}; {printf "\033[33m  - %-30s\033[0m %s\n", $$1, $$2}'
	@echo "--------------------------------------------------------------------------------------------"
	@printf "\033[36mDevelopment:%-30s\033[0m %s\n"
	@grep -E '^[a-zA-Z_-]+:.*?##2 .*$$' $(firstword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?##2 "}; {printf "\033[36m  - %-30s\033[0m %s\n", $$1, $$2}'
	@echo "--------------------------------------------------------------------------------------------"
	@printf "\033[32mTests:%-30s\033[0m %s\n"
	@grep -E '^[a-zA-Z_-]+:.*?##3 .*$$' $(firstword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?##3 "}; {printf "\033[32m  - %-30s\033[0m %s\n", $$1, $$2}'
	@echo "--------------------------------------------------------------------------------------------"
	@printf "\033[35mDevOps:%-30s\033[0m %s\n"
	@grep -E '^[a-zA-Z_-]+:.*?##4 .*$$' $(firstword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?##4 "}; {printf "\033[35m  - %-30s\033[0m %s\n", $$1, $$2}'

# ------------------------------------------------------------------------------------------------------------

prod: ## 1 Installs all production dependencies
	composer install --no-dev

dev: ##1 Installs all dev dependencies
	composer install

clean: ##1 Clears all dependencies
	rm -rf vendor/*

# ------------------------------------------------------------------------------------------------------------

phpcheck: ##3 Starts the PHP syntax checks
	@find . -name '*.php' -not -path "./vendor/*" -not -path "./tests/*" | xargs -n 1 -P4 php -l

csfix: ##3 Starts the PHP CS Fixer
	@PHP_CS_FIXER_IGNORE_ENV=1 php vendor/bin/php-cs-fixer fix --config=./.php_cs.php --dry-run

stan: ##3 Starts the PHPStan Analyser
	@php vendor/bin/phpstan analyse -c ./.phpstan.neon

phpunit: ##3 Starts all PHPUnit Tests
	@XDEBUG_MODE=coverage php vendor/bin/phpunit --configuration=phpunit.xml --coverage-html ./.reports/phpunit/coverage

# ------------------------------------------------------------------------------------------------------------

pr: ##2 Prepares everything for a Pull Request
	@PHP_CS_FIXER_IGNORE_ENV=1 php vendor/bin/php-cs-fixer fix --config=./.php_cs.php
	@make phpcheck -B
	@make stan -B
	@make phpunit -B

# ------------------------------------------------------------------------------------------------------------

release: ##4 Builds a PROD version and creates a ZIP file in plugins/.build.
	make clean
	make prod
	mkdir -p ./.build
	rm -rf ./.build/AIDemoData.zip || true;
	# -------------------------------------------------------------------------------------------------
	@echo "INSTALL PRODUCTION DEPENDENCIES"
	make prod -B
	# -------------------------------------------------------------------------------------------------
	@echo "CREATE ZIP FILE"
	zip -qq -r -0 ./.build/AIDemoData.zip . -x '*.git*' '*.github*' '*devops*' '*.idea*' '*.reports*' '*tests*' '*.build*' '*node_modules*' '*makefile*' '*phpunit.xml*' '*.php_cs.php*' '*.phpstan.neon*' '*.eslintrc.json*' '*.prettierrc.json*' '*package.json*' '*package-lock.json*'
	@echo ""
	@echo "CONGRATULATIONS"
	@echo "The new ZIP file is available"
