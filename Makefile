.PHONY: help up down restart shell logs install migrate seed test lint build clean

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

up: ## Start all Docker containers
	docker-compose up -d

down: ## Stop all Docker containers
	docker-compose down

restart: down up ## Restart all Docker containers

shell: ## Enter app container shell
	docker-compose exec app sh

logs: ## Show container logs
	docker-compose logs -f

install: ## Install composer and npm dependencies
	docker-compose exec app composer install
	docker-compose exec app npm install

migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

migrate-fresh: ## Fresh migrations with seed
	docker-compose exec app php artisan migrate:fresh --seed

seed: ## Run database seeders
	docker-compose exec app php artisan db:seed

test: ## Run PHPUnit tests
	docker-compose exec app php artisan test

test-coverage: ## Run tests with coverage
	docker-compose exec app php artisan test --coverage

lint: ## Run code linters (PHPStan and Pint)
	docker-compose exec app vendor/bin/phpstan analyse || true
	docker-compose exec app vendor/bin/pint --test || true

fix: ## Fix code style issues
	docker-compose exec app vendor/bin/pint

build: ## Build production assets
	docker-compose exec app npm run build

clean: ## Clean up containers and volumes
	docker-compose down -v

db-shell: ## Enter database shell
	docker-compose exec db mysql -u$(DB_USERNAME) -p$(DB_PASSWORD) $(DB_DATABASE)

radius-shell: ## Enter RADIUS database shell
	docker-compose exec radius-db mysql -u$(RADIUS_DB_USERNAME) -p$(RADIUS_DB_PASSWORD) $(RADIUS_DB_DATABASE)

# Artisan commands shortcuts
ipam-cleanup: ## Run IPAM cleanup command
	docker-compose exec app php artisan ipam:cleanup

radius-sync: ## Sync user to RADIUS (usage: make radius-sync USER_ID=1)
	docker-compose exec app php artisan radius:sync-user $(USER_ID)

mikrotik-health: ## Check MikroTik router health
	docker-compose exec app php artisan mikrotik:health-check
