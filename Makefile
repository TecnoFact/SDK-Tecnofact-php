.PHONY: help install test analyze lint fix ci clean docker-build docker-up docker-down

help:
	@echo "TecnoFact SDK - Comandos disponibles:"
	@echo ""
	@echo "Desarrollo local:"
	@echo "  make install    - Instalar dependencias (requiere PHP 8.3+)"
	@echo "  make test       - Ejecutar tests"
	@echo "  make analyze    - Ejecutar PHPStan"
	@echo "  make lint       - Verificar estilo de código"
	@echo "  make fix        - Corregir estilo de código"
	@echo "  make ci         - Ejecutar checks completos (lint + analyze + test)"
	@echo ""
	@echo "Docker:"
	@echo "  make docker-build - Construir imagen Docker"
	@echo "  make docker-up   - Iniciar contenedores"
	@echo "  make docker-down - Detener contenedores"
	@echo ""
	@echo "Docker con perfiles:"
	@echo "  make docker-test     - Ejecutar tests en Docker"
	@echo "  make docker-analyze  - Ejecutar PHPStan en Docker"
	@echo "  make docker-lint     - Ejecutar linter en Docker"

install:
	@echo "Instalando dependencias..."
	composer install --no-interaction

test:
	@echo "Ejecutando tests..."
	vendor/bin/phpunit --colors=always

analyze:
	@echo "Ejecutando PHPStan..."
	vendor/bin/phpstan analyse --level=9 --no-progress

lint:
	@echo "Verificando estilo de código..."
	vendor/bin/php-cs-fixer fix --dry-run --diff

fix:
	@echo "Corrigiendo estilo de código..."
	vendor/bin/php-cs-fixer fix --diff

ci:
	@echo "Ejecutando CI checks..."
	@make lint
	@make analyze
	@make test

clean:
	@echo "Limpiando cache..."
	rm -rf vendor composer.lock
	rm -rf .phpunit.cache
	rm -rf coverage
	find . -type d -name cache -exec rm -rf {} + 2>/dev/null || true

docker-build:
	docker-compose build

docker-up:
	docker-compose up -d sdk

docker-down:
	docker-compose down

docker-test:
	docker-compose --profile test run test

docker-analyze:
	docker-compose --profile analysis run phpstan

docker-lint:
	docker-compose --profile lint run cs-fixer

docker-ci:
	docker-compose --profile ci run ci
