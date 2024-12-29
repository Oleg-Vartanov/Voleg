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


# ========== General ==========

init:
	docker compose build
	docker compose down
	docker compose up -d
	$(PHP_CONTAINER) composer install --no-interaction
	$(PHP_CONTAINER) php bin/console lexik:jwt:generate-keypair --skip-if-exists # Generate JWT keys.
	$(PHP_CONTAINER) chmod -R 777 var
	$(PHP_CONTAINER) php bin/console doctrine:migrations:migrate --no-interaction
	$(PHP_CONTAINER) php bin/console app:populate-db
	$(PHP_CONTAINER) php bin/console asset-map:compile
	$(NODE_CONTAINER) npm install
	$(NODE_CONTAINER) npm run build-only

deploy:
	git pull origin main
	$(MAKE) init

# Docker up dev.
up-dev:
	docker compose --profile=dev up -d


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
	$(PHP_CONTAINER) php bin/console --env=test doctrine:database:drop --force
	$(PHP_CONTAINER) php bin/console --env=test doctrine:database:create
	$(PHP_CONTAINER) php bin/console --env=test doctrine:schema:create
	$(PHP_CONTAINER) php bin/phpunit


# ========== Frontend ==========

# Run frontend dev.
watch:
	$(NODE_CONTAINER) npm run dev -- --host

front-build:
	$(NODE_CONTAINER) npm run build-only
