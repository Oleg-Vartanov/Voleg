init:
	docker compose build
	docker compose up -d
	docker exec -it project_php composer install
	#docker exec -it project_php chmod -R 777 var
	docker exec -it project_node npm install
	docker exec -it project_node npm run build

# Run frontend dev
watch:
	docker exec -it project_node npm run dev -- --host