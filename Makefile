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

init:
	docker compose build
	docker compose up -d
	docker exec -it $(PROJECT_NAME)_php composer install
	#docker exec -it $(PROJECT_NAME)_php chmod -R 777 var
	docker exec -it $(PROJECT_NAME)_node npm install
	docker exec -it $(PROJECT_NAME)_node npm run build

# Run frontend dev
watch:
	docker exec -it $(PROJECT_NAME)_node npm run dev -- --host