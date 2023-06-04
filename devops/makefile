#
# Makefile
#

.PHONY: help
.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# ------------------------------------------------------------------------------------------------------------

run: ## Starts the project
	docker-compose up -d
	sleep 10
	docker exec -it shopware bash -c 'cd custom/plugins/AIDemoData && make dev'
	docker exec -it shopware bash -c 'php bin/console plugin:refresh'
	docker exec -it shopware bash -c 'php bin/console plugin:install --activate AIDemoData'
	open http://localhost
	docker exec -it shopware bash
