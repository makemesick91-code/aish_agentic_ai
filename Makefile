# Aish Agentic AI — developer entry points (Step 5 runtime bootstrap).
# Thin, documented wrappers over scripts/ so `make <target>` is the single UX.
.DEFAULT_GOAL := help
SHELL := /usr/bin/env bash

.PHONY: help preflight bootstrap bootstrap-fresh verify up down test analyse format format-check assets fast-ci full-ci

help: ## Show available targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN{FS=":.*?## "}{printf "  \033[36m%-16s\033[0m %s\n", $$1, $$2}'

preflight: ## Verify host prerequisites and runtime config
	@scripts/runtime/preflight.sh

bootstrap: ## Idempotent local setup (deps, .env, services, migrate, assets)
	@scripts/runtime/bootstrap-local.sh

bootstrap-fresh: ## Bootstrap and reset the local database (DROPS dev data)
	@scripts/runtime/bootstrap-local.sh --fresh

verify: ## Prove the runtime works end-to-end (live/ready/queue/scheduler)
	@scripts/runtime/verify-runtime.sh

up: ## Start local Postgres + Redis
	@docker compose up -d

down: ## Stop local Postgres + Redis
	@docker compose down

test: ## Run the PHPUnit suite (sqlite/array/sync)
	@php artisan test

analyse: ## Static analysis (PHPStan/Larastan)
	@vendor/bin/phpstan analyse --no-progress --memory-limit=1G

format: ## Auto-format (Laravel Pint)
	@vendor/bin/pint

format-check: ## Check formatting without writing
	@vendor/bin/pint --test

assets: ## Build frontend assets
	@npm run build

fast-ci: ## Fast local gate (mirrors draft CI)
	@scripts/ci/fast-local.sh

full-ci: ## Full local gate before marking a PR ready
	@scripts/ci/full-local.sh
