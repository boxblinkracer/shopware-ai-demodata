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

test: ## Starts all Tests
	php ./vendor/bin/phpunit --configuration=./phpunit.xml

stan: ## Starts the PHPStan Analyser
	php ./vendor/bin/phpstan --memory-limit=1G analyse .


release: ## Builds a PROD version and creates a ZIP file in plugins/.build.
	mkdir -p ./.build
	rm -rf ./.build/AIDemoData.zip || true;
	# -------------------------------------------------------------------------------------------------
	@echo "INSTALL PRODUCTION DEPENDENCIES"
	make prod -B
	# -------------------------------------------------------------------------------------------------
	@echo "CREATE ZIP FILE"
	zip -qq -r -0 ./.build/AIDemoData.zip . -x '*.git*' '*.github*' '*devops*' '*.idea*' '*.build*' '*node_modules*' '*makefile*' '*.eslintrc.json*' '*.prettierrc.json*' '*package.json*' '*package-lock.json*'
	@echo ""
	@echo "CONGRATULATIONS"
	@echo "The new ZIP file is available"
