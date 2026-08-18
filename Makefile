SHELL := /bin/bash

# --- Configuration ---
DOCKER_COMPOSE  := docker compose
DEV_COMPOSE     := docker compose -f dev.docker-compose.yml

# --- Production (docker-compose.yml [+ override]) ---

.PHONY: prod-up prod-down prod-build prod-rebuild prod-logs prod-ps prod-shell prod-db-shell

prod-up:        ## Start production stack
	$(DOCKER_COMPOSE) up -d

prod-down:      ## Stop production stack
	$(DOCKER_COMPOSE) down

prod-build:     ## Build production image from fork (use after code changes)
	$(DOCKER_COMPOSE) build --no-cache

prod-rebuild:   ## Rebuild production image (cached)
	$(DOCKER_COMPOSE) build

prod-logs:      ## Follow production logs
	$(DOCKER_COMPOSE) logs -f

prod-ps:        ## List production containers
	$(DOCKER_COMPOSE) ps

prod-shell:     ## Open shell inside the production app container
	$(DOCKER_COMPOSE) exec app sh

prod-db-shell:  ## Open shell inside the production db container
	$(DOCKER_COMPOSE) exec db sh

prod-exec:      ## Run a command in the production app container (make prod-exec cmd="php artisan migrate")
	$(DOCKER_COMPOSE) exec app $(cmd)

# --- Development (dev.docker-compose.yml) ---

.PHONY: dev-up dev-down dev-build dev-rebuild dev-logs dev-ps dev-shell dev-db-shell dev-exec

dev-up:         ## Start dev stack (builds image automatically)
	$(DEV_COMPOSE) up --build -d

dev-down:       ## Stop dev stack
	$(DEV_COMPOSE) down

dev-build:      ## Build dev image from scratch
	$(DEV_COMPOSE) build --no-cache

dev-rebuild:    ## Rebuild dev image (cached)
	$(DEV_COMPOSE) build

dev-logs:       ## Follow dev logs
	$(DEV_COMPOSE) logs -f

dev-ps:         ## List dev containers
	$(DEV_COMPOSE) ps

dev-shell:      ## Open shell inside the dev container
	$(DEV_COMPOSE) exec snipeit sh

dev-db-shell:   ## Open shell inside the dev mariadb container
	$(DEV_COMPOSE) exec mariadb sh

dev-exec:       ## Run a command in the dev container (make dev-exec cmd="php artisan migrate")
	$(DEV_COMPOSE) exec snipeit $(cmd)

# --- Cleanup ---

.PHONY: clean clean-all help

clean:          ## Remove stopped containers and dangling images
	$(DOCKER_COMPOSE) down
	$(DEV_COMPOSE) down

clean-all:      ## Remove everything including volumes (⚠ destroys data)
	$(DOCKER_COMPOSE) down -v
	$(DEV_COMPOSE) down -v

help:           ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'
