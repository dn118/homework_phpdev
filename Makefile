COMPOSE := docker compose
EMAIL ?=

.PHONY: help dev reset up down restart ps logs logs_db shell test test_local cs_fix stan audit code_check sync_articles email_confirmation_link password_reset_link prepare_env wait_db

help: ## Show available commands
	grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS=":.*?## "}; {printf "%-28s %s\n", $$1, $$2}'

prepare_env: ## Create .env.docker and generate APP_KEY if missing
	if [ ! -f .env.docker ]; then cp .env.docker.example .env.docker; fi
	key=$$(grep '^APP_KEY=' .env.docker | cut -d= -f2-); \
	if [ -z "$$key" ] || [ "$$key" = "generated_after_setup" ]; then \
		new_key=$$(php -r 'echo "base64:".base64_encode(random_bytes(32));'); \
		if grep -q '^APP_KEY=' .env.docker; then \
			sed -i "s|^APP_KEY=.*|APP_KEY=$$new_key|" .env.docker; \
		else \
			echo "APP_KEY=$$new_key" >> .env.docker; \
		fi; \
	fi

wait_db: ## Wait until MySQL is ready
	for i in $$(seq 1 60); do \
		if $(COMPOSE) exec -T db mysqladmin ping -h localhost -u app -papp --silent > /dev/null 2>&1; then \
			echo "MySQL is ready"; \
			exit 0; \
		fi; \
		sleep 2; \
	done; \
	echo "MySQL is not ready after 120 seconds"; \
	exit 1

dev: prepare_env ## First-time/full bootstrap (build, up, migrate, seed, sync)
	$(COMPOSE) up -d --build
	$(MAKE) wait_db
	$(COMPOSE) exec -T app php artisan migrate:fresh --seed
	$(COMPOSE) exec -T app php artisan articles:sync || true
	echo "App: http://localhost:8780"
	echo "Mailpit: http://localhost:8025"

reset: prepare_env ## Full Docker reset (down -v, up, DB reset, sync)
	$(COMPOSE) down -v --remove-orphans
	$(COMPOSE) up -d
	$(MAKE) wait_db
	$(COMPOSE) exec -T app php artisan migrate:fresh --seed
	$(COMPOSE) exec -T app php artisan articles:sync || true
	echo "Reset complete"

up: ## Start containers
	$(COMPOSE) up -d

down: ## Stop containers
	$(COMPOSE) down

restart: ## Restart containers
	$(COMPOSE) restart

ps: ## Show container status
	$(COMPOSE) ps

logs: ## Tail app logs
	$(COMPOSE) logs -f app

logs_db: ## Tail MySQL logs
	$(COMPOSE) logs -f db

shell: ## Open shell in app container
	$(COMPOSE) exec app sh

test: ## Run test suite in Docker
	$(COMPOSE) exec -T app php artisan test

test_local: ## Run tests on host machine (if local PHP deps are installed)
	php artisan test

sync_articles: ## Sync articles from Hacker News
	$(COMPOSE) exec -T app php artisan articles:sync

email_confirmation_link: ## Generate signed email verification link (usage: make email_confirmation_link EMAIL=user@example.com)
	test -n "$(EMAIL)" || (echo "Usage: make email_confirmation_link EMAIL=user@example.com" && exit 1)
	$(COMPOSE) exec -T app php artisan verify:link "$(EMAIL)"

password_reset_link: ## Generate password reset link (usage: make password_reset_link EMAIL=user@example.com)
	test -n "$(EMAIL)" || (echo "Usage: make password_reset_link EMAIL=user@example.com" && exit 1)
	$(COMPOSE) exec -T app php artisan password:link "$(EMAIL)"

cs_fix: ## Run Laravel Pint
	$(COMPOSE) exec -T app ./vendor/bin/pint

stan: ## Run Larastan/PHPStan
	$(COMPOSE) exec -T app ./vendor/bin/phpstan analyse app tests

audit: ## Run Composer audit
	$(COMPOSE) exec -T app composer audit

code_check: ## Run formatter + static analysis + audit
	$(MAKE) cs_fix
	$(MAKE) stan
	$(MAKE) audit
