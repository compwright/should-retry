test:
	vendor/bin/phpunit src tests

lint:
	vendor/bin/phpstan analyse --level 9 src tests
	vendor/bin/php-cs-fixer fix
