#!/bin/bash

# Script para ejecutar tests en Docker
# Uso: ./run-tests-docker.sh [opciones]

set -e

echo "🐳 TecnoFact SDK - Test Runner con Docker"
echo "=========================================="
echo ""

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Función para mostrar ayuda
show_help() {
    echo "Uso: ./run-tests-docker.sh [COMANDO]"
    echo ""
    echo "Comandos disponibles:"
    echo "  test          - Ejecutar todos los tests (default)"
    echo "  coverage      - Ejecutar tests con reporte de cobertura"
    echo "  analyze       - Ejecutar análisis estático (PHPStan)"
    echo "  psalm         - Ejecutar Psalm"
    echo "  lint          - Verificar estilo de código"
    echo "  fix           - Corregir estilo de código"
    echo "  ci            - Ejecutar todos los checks (lint + analyze + test)"
    echo "  shell         - Abrir shell en el contenedor"
    echo "  build         - Construir imagen Docker"
    echo "  clean         - Limpiar contenedores y volúmenes"
    echo ""
    echo "Ejemplos:"
    echo "  ./run-tests-docker.sh test"
    echo "  ./run-tests-docker.sh coverage"
    echo "  ./run-tests-docker.sh ci"
}

# Verificar si Docker está corriendo
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        echo -e "${RED}❌ Error: Docker no está corriendo${NC}"
        echo "Por favor inicia Docker Desktop y vuelve a intentar"
        exit 1
    fi
}

# Construir imagen
build_image() {
    echo -e "${YELLOW}🔨 Construyendo imagen Docker...${NC}"
    docker-compose build
    echo -e "${GREEN}✅ Imagen construida exitosamente${NC}"
}

# Ejecutar tests
run_tests() {
    echo -e "${YELLOW}🧪 Ejecutando tests...${NC}"
    docker-compose --profile test run --rm test
    echo -e "${GREEN}✅ Tests completados${NC}"
}

# Ejecutar tests con cobertura
run_coverage() {
    echo -e "${YELLOW}📊 Ejecutando tests con cobertura...${NC}"
    docker-compose --profile test run --rm test vendor/bin/phpunit --coverage-html coverage --coverage-text
    echo -e "${GREEN}✅ Reporte de cobertura generado en ./coverage${NC}"
}

# Ejecutar PHPStan
run_phpstan() {
    echo -e "${YELLOW}🔍 Ejecutando PHPStan...${NC}"
    docker-compose --profile analysis run --rm phpstan
    echo -e "${GREEN}✅ Análisis estático completado${NC}"
}

# Ejecutar Psalm
run_psalm() {
    echo -e "${YELLOW}🔍 Ejecutando Psalm...${NC}"
    docker-compose --profile analysis run --rm phpstan vendor/bin/psalm --no-progress
    echo -e "${GREEN}✅ Psalm completado${NC}"
}

# Verificar estilo de código
run_lint() {
    echo -e "${YELLOW}💄 Verificando estilo de código...${NC}"
    docker-compose --profile lint run --rm cs-fixer vendor/bin/php-cs-fixer fix --dry-run --diff
    echo -e "${GREEN}✅ Verificación de estilo completada${NC}"
}

# Corregir estilo de código
run_fix() {
    echo -e "${YELLOW}🔧 Corrigiendo estilo de código...${NC}"
    docker-compose --profile lint run --rm cs-fixer
    echo -e "${GREEN}✅ Estilo de código corregido${NC}"
}

# Ejecutar CI completo
run_ci() {
    echo -e "${YELLOW}🚀 Ejecutando CI completo...${NC}"
    run_lint
    run_phpstan
    run_psalm
    run_tests
    echo -e "${GREEN}✅ CI completado exitosamente${NC}"
}

# Abrir shell en contenedor
run_shell() {
    echo -e "${YELLOW}🐚 Abriendo shell en contenedor...${NC}"
    docker-compose run --rm sdk bash
}

# Limpiar contenedores
clean() {
    echo -e "${YELLOW}🧹 Limpiando contenedores y volúmenes...${NC}"
    docker-compose down -v
    echo -e "${GREEN}✅ Limpieza completada${NC}"
}

# Main
check_docker

COMMAND=${1:-test}

case "$COMMAND" in
    help|-h|--help)
        show_help
        ;;
    build)
        build_image
        ;;
    test)
        run_tests
        ;;
    coverage)
        run_coverage
        ;;
    analyze|phpstan)
        run_phpstan
        ;;
    psalm)
        run_psalm
        ;;
    lint)
        run_lint
        ;;
    fix)
        run_fix
        ;;
    ci)
        run_ci
        ;;
    shell|bash)
        run_shell
        ;;
    clean)
        clean
        ;;
    *)
        echo -e "${RED}❌ Comando desconocido: $COMMAND${NC}"
        echo ""
        show_help
        exit 1
        ;;
esac
