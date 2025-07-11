# Makefile for Metro Markets Backend Challenge
PHP_SERVICE := php
PHP_WORKER_SERVICE := php-worker

.PHONY: help up down install setup migrate fetch test shell

help:
	@echo "Usage: make [target]"
	@echo ""
	@echo "Lifecycle and Setup:"
	@echo "  up        - Start all services in detached mode."
	@echo "  down      - Stop and remove all services."
	@echo "  install   - Install PHP dependencies using Composer."
	@echo "  setup     - A single command to build, start, install dependencies, and run migrations."
	@echo ""
	@echo "Application Commands:"
	@echo "  fetch     - Dispatch the main price fetching job to the queue."
	@echo "  test      - Run the PHPUnit test suite."
	@echo "  shell     - Access the shell (bash) of the PHP container."
	@echo "  logs      - Tail the logs for ALL running services."
	@echo ""
	@echo "Database & User Management:"
	@echo "  migrate          - Run database migrations."
	@echo "  migration-diff   - Generate a new migration file based on entity changes."
	@echo "  create-user      - Creates a new user. Usage: make create-user EMAIL=<email@address.com>"
	@echo ""
	@echo "Dead Letter Queue Management:"
	@echo "  failed-queue     - List all messages in the failed queue."
	@echo "  retry-message    - Retry a failed message. Usage: make retry-message ID=<message_id>"
	@echo "  remove-message   - Remove a failed message. Usage: make remove-message ID=<message_id>"

up:
	@echo "Starting Docker services..."
	docker compose up -d --build

down:
	@echo "Stopping Docker services..."
	docker compose down

install:
	@echo "Installing PHP dependencies..."
	docker compose exec $(PHP_SERVICE) composer install

setup:
	@$(MAKE) up
	@echo "Waiting 10 seconds for the database to be ready..."
	@sleep 10
	@$(MAKE) install
	@$(MAKE) migrate
	@echo "Setup complete! The application is ready."

migration-diff:
	@echo "Generating a new migration diff..."
	docker compose exec $(PHP_SERVICE) bin/console doctrine:migrations:diff

migrate:
	@echo "Running database migrations..."
	docker compose exec $(PHP_SERVICE) bin/console doctrine:migrations:migrate --no-interaction

fetch:
	@echo "Fetching prices from all sources..."
	docker compose exec $(PHP_SERVICE) bin/console prices:fetch

logs:
	@echo "Tailling logs for the PHP worker..."
	docker compose logs -f

test:
	@echo "Running the test suite..."
	docker compose exec $(PHP_SERVICE) bin/phpunit

shell:
	@echo "Accessing the PHP container shell..."
	docker compose exec $(PHP_SERVICE) bash

failed-queue:
	@echo "Listing all messages in the failed queue..."
	docker compose exec $(PHP_SERVICE) bin/console messenger:failed:show

retry-message:
	@echo "Retrying failed message with ID: $$ID..."
	docker compose exec $(PHP_SERVICE) bin/console messenger:failed:retry $$ID

remove-message:
	@echo "Removing failed message with ID: $$ID..."
	docker compose exec $(PHP_SERVICE) bin/console messenger:failed:remove $$ID

create-user:
	@echo "Creating a new user with email: $$EMAIL..."
	docker compose exec $(PHP_SERVICE) bin/console api:create-user $$EMAIL
