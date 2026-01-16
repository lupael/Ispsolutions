.PHONY: help setup up down restart logs shell test lint migrate seed fresh

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

setup: ## Initial project setup
	@echo "Setting up project..."
	cp -n .env.example .env || true
	composer install
	npm install
	php artisan key:generate
	@echo "Setup complete. Run 'make up' to start Docker services."

up: ## Start Docker services
	docker-compose up -d
	@echo "Services started. App available at http://localhost:8000"

down: ## Stop Docker services
	docker-compose down

restart: ## Restart Docker services
	docker-compose restart

logs: ## View Docker logs
	docker-compose logs -f

shell: ## Open shell in app container
	docker-compose exec app sh

test: ## Run all tests
	php artisan test

lint: ## Run linters
	./vendor/bin/pint
	npm run lint || true

migrate: ## Run database migrations
	php artisan migrate --force

seed: ## Seed databases
	php artisan db:seed --force

fresh: ## Fresh migration with seed
	php artisan migrate:fresh --seed --force

install-deps: ## Install PHP and Node dependencies
	composer install
	npm install

build: ## Build frontend assets
	npm run build

dev: ## Start development servers
	@echo "Starting development servers..."
	@echo "Press Ctrl+C to stop all servers"
	@trap 'echo "Stopping servers..."; kill 0' EXIT INT TERM; \
	php artisan serve & \
	npm run dev
