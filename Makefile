.PHONY: help install setup run test code_check cs_fix lint stan audit fresh

help: ## Show this help message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-15s\033[0m %s\n", $$1, $$2}'

install: ## Install dependencies
	composer install

setup: ## Full local setup
	composer install
	cp .env.example .env
	touch database/database.sqlite
	php artisan key:generate
	php artisan migrate
	npm install
	npm run build

run: ## Start local development server
	php artisan serve

test: ## Run tests
	php artisan test

code_check: cs_fix stan audit ## Run all code quality checks

cs_fix: ## Run Laravel Pint (code formatting)
	./vendor/bin/pint

lint: ## Alias for cs_fix
	./vendor/bin/pint

stan: ## Run Larastan static analysis
	./vendor/bin/phpstan analyse app tests

audit: ## Run composer audit
	composer audit

fresh: ## Reset database and run migrations
	php artisan migrate:fresh --seed
