PORT ?= 8000
HOST ?= 127.0.0.1

.PHONY: install setup start test lint lint-fix check reset-test

install:
	composer install

setup: install
	php bin/reset.php dev

start:
	php -S $(HOST):$(PORT) -t public

reset-test:
	php bin/reset.php test

test: reset-test
	php bin/phpunit

lint:
	mago fmt --check
	mago lint
	php bin/console lint:container
	php bin/console lint:twig templates
	php bin/console lint:yaml config translations

lint-fix:
	mago fmt
	mago lint --fix

check: lint test
