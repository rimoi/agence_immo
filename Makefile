## General
.DEFAULT_GOAL := help
help: ## Show the help
	    @grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## Tools commands
.PHONY: dump
dump: vendor/bin/var-dump-check ## Check if there is no dump in the code base
	    php vendor/bin/var-dump-check --no-colors --symfony --exclude bin --exclude config --exclude libraries --exclude public --exclude var --exclude vendor .

.PHONY: phpcs
phpcs: vendor/bin/php-cs-fixer .php_cs.dist ## Launch php-cs-fixer
	    php vendor/bin/php-cs-fixer fix --config=.php_cs.dist --allow-risky=yes

.PHONY: phpcs-dry-run
phpcs-dry-run: vendor/bin/php-cs-fixer ## Launch php-cs-fixer but no modifications are made
	    make phpcs --dry-run

.PHONY: phpstan
phpstan: vendor/bin/phpstan .phpstan.neon ## Launch phpstan verification
	    php vendor/bin/phpstan analyse -c .phpstan.neon

.PHONY: phpunit
phpunit: bin/phpunit ## Lauch phpunit test
	    php bin/phpunit