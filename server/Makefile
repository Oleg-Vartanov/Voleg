# ========== Makefile Variables ==========

include .env
-include .env.local

# container
PHP_CONTAINER = docker exec $(PROJECT_NAME)_php
NODE_CONTAINER = docker exec $(PROJECT_NAME)_node
DB_CONTAINER = docker exec $(PROJECT_NAME)_database

# docker compose
ENV_ARGS := $(addprefix --env-file , .env .env.$(APP_ENV) .env.local)
DOCKER_COMPOSE := docker compose -f docker-compose.$(APP_ENV).yml $(ENV_ARGS)

# ========== General ==========

up:
	$(DOCKER_COMPOSE) up -d
down:
	$(DOCKER_COMPOSE) down

init:
	$(DOCKER_COMPOSE) build
	$(DOCKER_COMPOSE) down
	$(DOCKER_COMPOSE) up -d
	$(MAKE) init-containers

init-containers:
	$(PHP_CONTAINER) composer install --no-interaction
	$(PHP_CONTAINER) php bin/console lexik:jwt:generate-keypair --skip-if-exists # Generate JWT keys.
	$(PHP_CONTAINER) chmod -R 777 var
	$(PHP_CONTAINER) php bin/console doctrine:migrations:migrate --no-interaction
	$(PHP_CONTAINER) php bin/console app:populate-db
	$(PHP_CONTAINER) php bin/console asset-map:compile
	$(NODE_CONTAINER) npm install
	$(NODE_CONTAINER) npm run build-only

deploy:
	git -C .. pull origin main
	$(MAKE) init

# ========== Backend ==========

migrate:
	$(PHP_CONTAINER) php bin/console doctrine:migrations:migrate

consumer:
	$(PHP_CONTAINER) php bin/console messenger:consume default scheduler_fixture_predictions -vv

cache:
	$(PHP_CONTAINER) php bin/console cache:clear
	$(PHP_CONTAINER) php bin/console cache:warmup

test:
	$(PHP_CONTAINER) php bin/console --env=test cache:clear
	$(PHP_CONTAINER) php bin/console --env=test doctrine:database:drop --force || true
	$(PHP_CONTAINER) php bin/console --env=test doctrine:database:create
	$(PHP_CONTAINER) php bin/console --env=test doctrine:schema:create
	$(PHP_CONTAINER) php bin/console --env=test doctrine:fixtures:load --no-interaction
	docker exec -e XDEBUG_MODE=develop,debug,coverage $(PROJECT_NAME)_php php bin/phpunit --colors=always --display-deprecations

linter:
	$(PHP_CONTAINER) vendor/bin/phpcs
linter-fix:
	$(PHP_CONTAINER) vendor/bin/phpcbf
phpstan:
	$(PHP_CONTAINER) php bin/console cache:warmup
	$(PHP_CONTAINER) vendor/bin/phpstan analyse --memory-limit=2G

backup-db:
	$(DB_CONTAINER) sh -c 'exec mysqldump -u $(MYSQL_USER) -p"$(MYSQL_PASSWORD)" $(MYSQL_DATABASE)' > backup_$$(date +%F_%H-%M-%S).sql

# ========== Frontend ==========

# Run frontend dev.
watch:
	$(NODE_CONTAINER) npm run dev -- --host

front-build:
	$(NODE_CONTAINER) npm run build-only
