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

init:
	docker compose build
	docker compose up -d
	$(PHP_CONTAINER) composer install --no-interaction
	$(PHP_CONTAINER) php bin/console lexik:jwt:generate-keypair --skip-if-exists # Generate JWT keys.
	$(PHP_CONTAINER) chmod -R 777 var
	$(PHP_CONTAINER) php bin/console doctrine:migrations:migrate --no-interaction
	$(NODE_CONTAINER) npm install
	$(NODE_CONTAINER) npm run build

# Run frontend dev.
watch:
	$(NODE_CONTAINER) npm run dev -- --host

# Run backend migrations.
migrate:
	$(PHP_CONTAINER) php bin/console doctrine:migrations:migrate

# Run backend tests.
test:
	$(PHP_CONTAINER) php bin/console --env=test doctrine:database:drop --force
	$(PHP_CONTAINER) php bin/console --env=test doctrine:database:create
	$(PHP_CONTAINER) php bin/console --env=test doctrine:schema:create
	$(PHP_CONTAINER) php bin/phpunit

# Start mail consumer.
mailer:
	$(PHP_CONTAINER) php bin/console messenger:consume async

