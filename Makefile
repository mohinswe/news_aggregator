# Variables
DOCKER_COMPOSE_FILE = docker-compose.yml
APP_CONTAINER = laravel_app

# Default Target
.PHONY: help
help:
	@echo "Available commands:"
	@echo "  up             - Start all services in detached mode"
	@echo "  down           - Stop and remove all services"
	@echo "  restart        - Restart all services"
	@echo "  logs           - View logs for all containers"
	@echo "  exec           - Execute a custom command in the Laravel app container"
	@echo "  artisan        - Run Laravel Artisan commands"
	@echo "  composer       - Run Composer inside the app container"
	@echo "  npm            - Run npm inside the app container"
	@echo "  mysql-shell    - Open MySQL shell"
	@echo "  redis-shell    - Open Redis CLI shell"
	@echo "  clean          - Remove all containers, images, and volumes"



# Start services
.PHONY: up
up:
	docker-compose -f $(DOCKER_COMPOSE_FILE) up -d

# Stop and remove services
.PHONY: down
down:
	docker-compose -f $(DOCKER_COMPOSE_FILE) down

# Build images
.PHONY: build
build: 
	@make down
	docker-compose -f $(DOCKER_COMPOSE_FILE) build
	@make up



# Restart services
.PHONY: restart
restart: down up

# View logs
.PHONY: logs
logs:
	docker-compose -f $(DOCKER_COMPOSE_FILE) logs -f

# Execute a custom command in the Laravel app container
.PHONY: exec
exec:
	docker exec -it $(APP_CONTAINER) $(filter-out $@,$(MAKECMDGOALS))

# Run Laravel Artisan commands
.PHONY: artisan
artisan:
	docker exec -it $(APP_CONTAINER) php artisan $(filter-out $@,$(MAKECMDGOALS))

# Run Composer commands
.PHONY: composer
composer:
	docker exec -it $(APP_CONTAINER) composer $(filter-out $@,$(MAKECMDGOALS))

# Run ps commands
.PHONY: ps
ps:
	docker ps

# Open MySQL shell
.PHONY: mysql-shell
mysql-shell:
	docker exec -it laravel_mysql mysql -u news_aggregator_db_user -pnews_aggregator_db_password news_aggregator_db

# Open Redis CLI shell
.PHONY: redis-shell
redis-shell:
	docker exec -it laravel_redis redis-cli

# Clean up Docker resources
.PHONY: clean
clean: down
	docker system prune --volumes -f
