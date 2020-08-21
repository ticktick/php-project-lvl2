install:
	composer install

lint:
	composer run-script phpcs -- --standard=PSR12 src bin tests

test:
	composer run-script phpunit tests

test-coverage:
	composer run-script phpunit tests -- --coverage-clover build/logs/clover.xml