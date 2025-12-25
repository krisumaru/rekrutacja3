COMPOSE ?= docker compose
# Prefer service name from docker-compose.yml
SERVICE ?= app

.PHONY: run migrate unit phpstan stop down logs bash composer-install

run:
	$(COMPOSE) up -d --build

# Ensure dependencies are installed in the container before running commands
migrate:
	$(COMPOSE) exec -T $(SERVICE) php bin/console doctrine:migrations:migrate --no-interaction

unit:
	$(COMPOSE) exec -T $(SERVICE) ./vendor/bin/phpunit

phpstan:
	$(COMPOSE) exec -T $(SERVICE) ./vendor/bin/phpstan analyse -c phpstan.neon.dist

# Helpers
stop:
	$(COMPOSE) stop

down:
	$(COMPOSE) down -v

logs:
	$(COMPOSE) logs -f --tail=200 $(SERVICE)

bash:
	$(COMPOSE) exec $(SERVICE) bash

composer-install:
	$(COMPOSE) exec -T $(SERVICE) composer install --no-interaction --prefer-dist

composer-update:
	$(COMPOSE) exec -T $(SERVICE) composer update --no-interaction --prefer-dist
