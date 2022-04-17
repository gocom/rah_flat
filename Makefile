.PHONY: all clean compile help lint lint-fix

PHP = docker-compose run --rm php

all: lint

vendor:
	$(PHP) composer --ignore-platform-req=ext-memcached install

clean:
	$(PHP) rm -rf vendor composer.lock

lint: vendor
	$(PHP) composer lint

lint-fix: vendor
	$(PHP) composer lint-fix

compile: vendor
	$(PHP) composer compile

help:
	@echo "Manage project"
	@echo ""
	@echo "Usage:"
	@echo "  $$ make [command]"
	@echo ""
	@echo "Commands:"
	@echo ""
	@echo "  $$ make lint"
	@echo "  Lint code style"
	@echo ""
	@echo "  $$ make lint-fix"
	@echo "  Lint and fix code style"
	@echo ""
	@echo "  $$ make compile"
	@echo "  Compiles the plugin"
	@echo ""
	@echo "  $$ make clean"
	@echo "  Delete installed dependencies"
	@echo ""
	@echo "  $$ make vendor"
	@echo "  Install dependencies"
	@echo ""
