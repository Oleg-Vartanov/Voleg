# ========== Makefile Variables ==========

# Hierarchy of env files. The later overrides others.
ENV_FILES := .env .env.local

# Include .env variables.
define include_env_file
    ifneq (,$(wildcard ./$(1)))
        include $(1)
        export $(shell sed 's/=.*//' $(1))
    endif
endef
$(foreach env_file,$(ENV_FILES),$(eval $(call include_env_file,$(env_file))))

PHP_CONTAINER = docker exec $(PROJECT_NAME)_php
NODE_CONTAINER = docker exec $(PROJECT_NAME)_node
DB_CONTAINER = docker exec $(PROJECT_NAME)_database

DOCKER_COMPOSE_DEV = docker compose -f docker-compose.dev.yml
DOCKER_COMPOSE_PROD = docker compose -f docker-compose.prod.yml

# ========== General ==========

up-dev:
	$(DOCKER_COMPOSE_DEV) up -d
down-dev:
	$(DOCKER_COMPOSE_DEV) down
down-prod:
	$(DOCKER_COMPOSE_PROD) down

init-dev:
	$(DOCKER_COMPOSE_DEV) build
	$(DOCKER_COMPOSE_DEV) down
	$(DOCKER_COMPOSE_DEV) up -d
	$(MAKE) init-containers

init-prod:
	$(DOCKER_COMPOSE_PROD) build
	$(DOCKER_COMPOSE_PROD) down
	$(DOCKER_COMPOSE_PROD) up -d
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
	$(MAKE) init-prod

# ========== Backend ==========

migrate:
	$(PHP_CONTAINER) php bin/console doctrine:migrations:migrate

# Start mail consumer.
mailer:
	$(PHP_CONTAINER) php bin/console messenger:consume async

cclear:
	$(PHP_CONTAINER) php bin/console cache:clear
	$(PHP_CONTAINER) php bin/console cache:warmup

test:
	$(PHP_CONTAINER) php bin/console cache:clear
	$(PHP_CONTAINER) php bin/console --env=test doctrine:database:drop --force || true
	$(PHP_CONTAINER) php bin/console --env=test doctrine:database:create
	$(PHP_CONTAINER) php bin/console --env=test doctrine:schema:create
	$(PHP_CONTAINER) php bin/console --env=test doctrine:fixtures:load --no-interaction
	docker exec -e XDEBUG_MODE=develop,debug,coverage $(PROJECT_NAME)_php php bin/phpunit --colors=always --display-deprecations

backup-db:
	$(DB_CONTAINER) sh -c 'exec mysqldump -u $(MYSQL_USER) -p"$(MYSQL_PASSWORD)" $(MYSQL_DATABASE)' > backup_$$(date +%F_%H-%M-%S).sql

# ========== Frontend ==========

# Run frontend dev.
watch:
	$(NODE_CONTAINER) npm run dev -- --host

front-build:
	$(NODE_CONTAINER) npm run build-only
