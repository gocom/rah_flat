.PHONY: all clean compile help lint lint-fix shell test

HOST_UID ?= `id -u`
HOST_GID ?= `id -g`
PHP = docker compose run --rm -u $(HOST_UID):$(HOST_GID) php

all: lint

vendor:
	$(PHP) composer install

clean:
	$(PHP) bash -c 'rm -rf dist vendor composer.lock'

lint: vendor
	$(PHP) composer lint

lint-fix: vendor
	$(PHP) composer lint-fix

compile: vendor
	$(PHP) composer compile

shell: vendor
	$(PHP) bash

test: lint

help:
	@echo "Manage project"
	@echo ""
	@echo "Usage:"
	@echo "  $$ make [command]"
	@echo ""
	@echo "Commands:"
	@echo ""
	@echo "  $$ make clean"
	@echo "  Delete installed dependencies"
	@echo ""
	@echo "  $$ make compile"
	@echo "  Compiles the plugin"
	@echo ""
	@echo "  $$ make lint"
	@echo "  Lint code style"
	@echo ""
	@echo "  $$ make lint-fix"
	@echo "  Lint and fix code style"
	@echo ""
	@echo "  $$ make shell"
	@echo "  Log in to the container"
	@echo ""
	@echo "  $$ make test"
	@echo "  Run test suite, including linter"
	@echo ""
	@echo "  $$ make vendor"
	@echo "  Install dependencies"
	@echo ""
