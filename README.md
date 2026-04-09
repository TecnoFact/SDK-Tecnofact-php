# TecnoFact SDK para Facturación Electrónica CFDI 4.0

SDK oficial de PHP para la integración con el servicio de Timbrado CFDI 4.0 de TecnoFact. Facilita la emisión, cancelación y consulta de facturas electrónicas cumpliendo con los requisitos del SAT mexicano.

[![Latest Stable Version](https://img.shields.io/packagist/v/tecnofact/sdk-tecnofact.svg)](https://packagist.org/packages/tecnofact/sdk-tecnofact)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tecnofact/sdk-tecnofact.svg)](https://packagist.org/packages/tecnofact/sdk-tecnofact)
[![License](https://img.shields.io/packagist/l/tecnofact/sdk-tecnofact.svg)](https://packagist.org/packages/tecnofact/sdk-tecnofact)
[![Tests](https://github.com/TecnoFact/SDK-Tecnofact-php/workflows/Tests/badge.svg)](https://github.com/TecnoFact/SDK-Tecnofact-php/actions/workflows/tests.yml)
[![Code Quality](https://github.com/TecnoFact/SDK-Tecnofact-php/workflows/Code%20Quality/badge.svg)](https://github.com/TecnoFact/SDK-Tecnofact-php/actions/workflows/code-quality.yml)
[![Security](https://github.com/TecnoFact/SDK-Tecnofact-php/workflows/Security/badge.svg)](https://github.com/TecnoFact/SDK-Tecnofact-php/actions/workflows/security.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-brightgreen)](https://phpstan.org/)
[![Psalm](https://img.shields.io/badge/Psalm-level%203-brightgreen)](https://psalm.dev/)
[![Docker](https://img.shields.io/badge/Docker-ready-blue)](https://www.docker.com/)

---

## 📋 Tabla de Contenidos

- [Características](#características)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Desarrollo Local con Docker](#desarrollo-local-con-docker)
- [Configuración](#configuración)
- [Estructura del SDK](#estructura-del-sdk)
- [Uso Básico](#uso-básico)
- [Modelos de Datos](#modelos-de-datos)
- [Manejo de Errores](#manejo-de-errores)
- [Testing](#testing)
- [Análisis Estático](#análisis-estático)
- [Versionado y Changelog](#versionado-y-changelog)
- [Contribuciones](#contribuciones)
- [Soporte](#soporte)
- [Licencia](#licencia)

---

## ✨ Características

- **Timbrado CFDI 4.0**: Emisión de facturas electrónicas cumpliendo con la versión 4.0 del CFDI
- **Timbrado CFDI 3.3**: Soporte retroactivo para facturación CFDI 3.3
- **Cancelación**: Cancelación de CFDIs con diferentes motivos
- **Consultas**: Búsqueda y recuperación de CFDIs timbrados
- **Reportes**: Generación de reportes y estadísticas
- **Validaciones**: Validación de RFCs y catálogos del SAT
- **Health Checks**: Verificación del estado de servicios
- **Tipado Estricto**: Compatible con PHP 8.0+ siguiendo estándares PHP Pro
- **Docker**: Entorno de desarrollo containerizado

---

## 🔧 Requisitos

- **PHP**: >= 8.3 (Recomendado)
- **Extensiones**: `json`, `openssl`, `curl`
- **Composer**: Para la instalación de dependencias
- **Docker**: Para desarrollo (opcional)

---

## 📦 Instalación

### Usando Composer

```bash
composer require tecnofact/sdk-tecnofact
```

### Desarrollo con Docker

```bash
# Clonar el repositorio
git clone https://github.com/TecnoFact/SDK-Tecnofact-php.git
cd SDK-Tecnofact-php

# Construir imagen Docker
docker-compose build

# Iniciar entorno de desarrollo
docker-compose up sdk
```

---

## 🐳 Desarrollo Local con Docker

### Comandos Disponibles

```bash
# Construir imagen
docker-compose build

# Iniciar entorno interactivo
docker-compose up sdk

# Ejecutar tests
docker-compose run --rm sdk vendor/bin/phpunit

# Ejecutar PHPStan (análisis estático)
docker-compose run --rm sdk vendor/bin/phpstan analyse

# Ejecutar linter (estilo de código)
docker-compose run --rm sdk vendor/bin/php-cs-fixer fix --diff

# Ejecutar análisis completo (CI)
docker-compose run --rm sdk vendor/bin/phpstan analyse && docker-compose run --rm sdk vendor/bin/phpunit
```

### Makefile (Alternativa)

Si prefieres usar Make:

```bash
make docker-build    # Construir imagen
make docker-up       # Iniciar entorno
make docker-test     # Ejecutar tests
make docker-analyze  # Ejecutar PHPStan
make docker-lint     # Verificar estilo
make docker-ci       # CI completo
```

---

## ⚙️ Configuración

### Constructor Directo

```php
<?php

require_once 'vendor/autoload.php';

use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Enums\Environment;

// Configuración con valores por defecto
$config = new Config(
    apiKey: 'tu-api-key',
    apiSecret: 'tu-api-secret'
);

// Configuración completa
$config = new Config(
    apiKey: 'tu-api-key',
    apiSecret: 'tu-api-secret',
    environment: Environment::SANDBOX,  // o ENVIRONMENT_PRODUCTION
    timeout: 30,    // segundos
    retries: 3      // reintentos automáticos
);
```

### Variables de Entorno

```php
<?php

use TecnoFact\Sdk\Config\Config;

// Lee automáticamente de $_ENV o $_SERVER
$config = Config::fromEnvironment();
```

Variables requeridas:
- `TECN_FACT_API_KEY` - Tu API Key
- `TECN_FACT_API_SECRET` - Tu API Secret
- `TECN_FACT_ENVIRONMENT` - `sandbox` o `production` (opcional)

### Archivos .env

```bash
# .env
TECN_FACT_API_KEY=tu-api-key
TECN_FACT_API_SECRET=tu-api-secret
TECN_FACT_ENVIRONMENT=sandbox
```

---

## 🏗️ Estructura del SDK

```
src/
├── Config/
│   └── Config.php              # Configuración inmutable del SDK
├── Contracts/
│   └── HttpClientInterface.php # Interfaz para el cliente HTTP
├── Enums/
│   ├── Environment.php         # Entornos (Sandbox/Production)
│   └── TipoComprobante.php    # Tipos de CFDI
├── Exceptions/
│   ├── TecnoFactException.php        # Excepción base
│   ├── AuthenticationException.php     # Error de autenticación
│   ├── ValidationException.php        # Error de validación
│   ├── TimbradoException.php        # Error de timbrado
│   ├── CancelacionException.php     # Error de cancelación
│   ├── NotFoundException.php         # Recurso no encontrado
│   ├── RateLimitException.php       # Límite de peticiones
│   └── ServerException.php          # Error del servidor
├── Http/
│   └── HttpClient.php       # Cliente HTTP con Guzzle
└── Models/
    ├── Emisor.php                 # Datos del emisor
    ├── Receptor.php                 # Datos del receptor
    ├── Concepto.php                # Conceptos de factura
    ├── Cfdi4Request.php            # Solicitud CFDI 4.0
    ├── CfdiRelacionados.php        # CFDIs relacionados
    ├── Impuestos.php               # Impuestos globales
    ├── ImpuestosConcepto.php       # Impuestos por concepto
    ├── Traslado.php                # Traslado de impuestos
    ├── TrasladoGlobal.php          # Traslado global
    ├── Retencion.php               # Retención de impuestos
    ├── RetencionGlobal.php         # Retención global
    ├── CuentaPredial.php           # Cuenta predial
    ├── InformacionAduanera.php     # Información aduanera
    └── Parte.php                   # Partes/componentes
```

---

## 💻 Uso Básico

### Ejemplo: Crear Configuración

```php
<?php

require_once 'vendor/autoload.php';

use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Enums\Environment;

$config = new Config(
    apiKey: 'TU_API_KEY',
    apiSecret: 'TU_API_SECRET',
    environment: Environment::SANDBOX,
    timeout: 30,
    retries: 3
);

echo "Entorno: " . $config->getEnvironment()->label() . "\n";
echo "URL Base: " . $config->getBaseUrl() . "\n";
echo "Timeout: " . $config->getTimeout() . " segundos\n";

// Convertir a array
$data = $config->toArray();
print_r($data);
```

### Ejemplo: Enum Environment

```php
<?php

use TecnoFact\Sdk\Enums\Environment;

// Usar enum con autocompletado
$env = Environment::PRODUCTION;

if ($env === Environment::PRODUCTION) {
    echo "Entorno de producción\n";
}

// Métodos del enum
echo $env->value;           // 'production'
echo $env->isProduction();  // true
echo $env->isSandbox();     // false
echo $env->label();         // 'Producción'
```

---

## 📋 Modelos de Datos

### Emisor

```php
<?php

use TecnoFact\Sdk\Models\Emisor;

$emisor = new Emisor(
    rfc: 'XAXX010101000',
    nombre: 'EMPRESA EMISORA SA DE CV',
    regimenFiscal: '601',
    cp: '06300'
);

echo $emisor->getRfc();           // XAXX010101000
echo $emisor->getNombre();       // EMPRESA EMISORA SA DE CV
print_r($emisor->toArray());
```

### Receptor

```php
<?php

use TecnoFact\Sdk\Models\Receptor;

$receptor = new Receptor(
    rfc: 'XAXX010101001',
    nombre: 'CLIENTE RECEPTOR',
    usoCfdi: 'G03',
    domicilioFiscalReceptor: '06300',
    regimenFiscalReceptor: '612'
);
```

### Concepto con Impuestos

```php
<?php

use TecnoFact\Sdk\Models\Concepto;
use TecnoFact\Sdk\Models\ImpuestosConcepto;
use TecnoFact\Sdk\Models\Traslado;

$concepto = new Concepto(
    claveProdServ: '01010101',
    cantidad: 1,
    claveUnidad: 'E48',
    descripcion: 'Servicio de desarrollo de software',
    valorUnitario: 10000.00,
    importe: 10000.00,
    objetoImp: '02',  // Sí objeto de impuesto
    impuestos: new ImpuestosConcepto(
        traslados: [
            new Traslado(
                base: 10000.00,
                impuesto: '002',      // IVA
                tipoFactor: 'Tasa',
                tasaOCuota: '0.160000',
                importe: 1600.00
            )
        ]
    )
);
```

---

## ⚠️ Manejo de Errores

El SDK lanza excepciones específicas para cada tipo de error:

| Excepción | Descripción | Código HTTP |
|-----------|-------------|-------------|
| `AuthenticationException` | Credenciales inválidas | 401 |
| `ValidationException` | Datos inválidos | 400 |
| `TimbradoException` | Error de timbrado | 422 |
| `CancelacionException` | Error de cancelación | 422 |
| `NotFoundException` | Recurso no encontrado | 404 |
| `RateLimitException` | Límite excedido | 429 |
| `ServerException` | Error del servidor | 5xx |

### Ejemplo de Manejo

```php
<?php

use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Exceptions\ValidationException;
use TecnoFact\Sdk\Exceptions\AuthenticationException;
use TecnoFact\Sdk\Exceptions\TecnoFactException;

try {
    $config = new Config(
        apiKey: 'test-api-key-1234567890',
        apiSecret: 'test-api-secret-12345678901234567890'
    );
    
    // Tu lógica aquí
    
} catch (ValidationException $e) {
    echo "Error de validación: " . $e->getMessage() . "\n";
    print_r($e->getErrors());
    
} catch (AuthenticationException $e) {
    echo "Error de autenticación: " . $e->getMessage() . "\n";
    
} catch (TecnoFactException $e) {
    echo "Error del SDK: " . $e->getMessage() . "\n";
    echo "Request ID: " . $e->getRequestId() . "\n";
}
```

---

## 🧪 Testing

### Ejecutar Tests

```bash
# Con Docker
docker-compose run --rm sdk vendor/bin/phpunit

# O local (si tienes PHP 8.3)
composer install
vendor/bin/phpunit
```

### Resultados Esperados

```
PHPUnit 10.x by Sebastian Bergmann and contributors.

Runtime:       PHP 8.3.x
Configuration: phpunit.xml

..........                                                10 / 10 (100%)

Time: 00:00.XXX, Memory: 8.00 MB

OK (10 tests, 27 assertions)
```

---

## 🔍 Análisis Estático

### PHPStan Nivel 8

```bash
# Con Docker
docker-compose run --rm sdk vendor/bin/phpstan analyse

# O local
vendor/bin/phpstan analyse --level=8
```

### Configuración PHPStan (phpstan.neon)

```yaml
parameters:
    level: 8
    paths:
        - src
        - tests
    ignoreErrors:
        - '#Method .+::__construct\(\) has parameter .+ with no value type#'
```

---

## 📦 Versionado y Changelog

Este proyecto utiliza [Semantic Versioning](https://semver.org/) y genera automáticamente versiones y changelog basado en [Conventional Commits](https://www.conventionalcommits.org/).

### Versionado Semántico

```
MAJOR.MINOR.PATCH
```

- **MAJOR**: Cambios incompatibles (breaking changes)
- **MINOR**: Nueva funcionalidad compatible
- **PATCH**: Correcciones de bugs

### Conventional Commits

Los commits deben seguir el formato:

```bash
<tipo>(<alcance>): <descripción>

# Ejemplos:
feat: agregar soporte para CFDI 4.0          # MINOR (1.x.0)
fix: corregir validación de RFC              # PATCH (1.0.x)
feat!: cambiar estructura de API             # MAJOR (x.0.0)
```

**Tipos principales:**
- `feat`: Nueva funcionalidad (MINOR)
- `fix`: Corrección de bug (PATCH)
- `perf`: Mejora de rendimiento (PATCH)
- `docs`: Cambios en documentación (PATCH)
- `refactor`: Refactorización (PATCH)
- `test`: Tests (no genera versión)
- `ci`: CI/CD (no genera versión)
- `chore`: Mantenimiento (no genera versión)

### Changelog Automático

El archivo [CHANGELOG.md](CHANGELOG.md) se actualiza automáticamente con cada release, documentando:

- ✨ **Features**: Nuevas funcionalidades
- 🐛 **Bug Fixes**: Correcciones de bugs
- ⚡ **Performance**: Mejoras de rendimiento
- 📚 **Documentation**: Cambios en documentación
- ♻️ **Refactoring**: Refactorizaciones de código
- 🔧 **Build System**: Cambios en build

### Releases

Las releases se generan automáticamente cuando se hace push a:

- **`main`**: Versiones estables (1.0.0, 1.1.0, 2.0.0)
- **`develop`**: Versiones beta (1.0.0-beta.1, 1.1.0-beta.2)

Ver todas las releases en [GitHub Releases](https://github.com/TecnoFact/SDK-Tecnofact-php/releases).

---

## 🤝 Contribuciones

Lee nuestra [Guía de Contribución](.github/CONTRIBUTING.md) para detalles sobre el proceso de desarrollo.

### Proceso Rápido

1. Fork el repositorio
2. Crea una rama (`git checkout -b feat/nueva-funcionalidad`)
3. Commit con formato convencional (`git commit -m "feat: descripción"`)
4. Push a la rama (`git push origin feat/nueva-funcionalidad`)
5. Abre un Pull Request hacia `develop`

### Estándares de Código

- ✅ `declare(strict_types=1)` en todos los archivos
- ✅ Tipado completo (parámetros, retornos, propiedades)
- ✅ PSR-12 coding standard
- ✅ Enums para valores fijos
- ✅ Clases `final` y `readonly` donde sea posible
- ✅ Tests passing
- ✅ PHPStan nivel 9 sin errores
- ✅ Psalm nivel 3 sin errores
- ✅ Commits siguen Conventional Commits

---

## 💬 Soporte

- 📧 Email: soporte@tecnofact.mx
- 🌐 Website: [https://www.tecnofact.mx](https://www.tecnofact.mx)
- 📖 Documentación API: [https://docs.tecnofact.mx](https://docs.tecnofact.mx)

---

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

---

## 🏢 Sobre TecnoFact

TecnoFact es un proveedor certificado de servicios de facturación electrónica en México, cumpliendo con todos los requisitos del SAT para la emisión de CFDI 4.0.

### Características del Servicio

- ✅ Timbrado masivo de CFDI
- ✅ Cancelación ante el SAT
- ✅ Consulta en Tiempo Real
- ✅ Reportes y analytics
- ✅ Soporte técnico especializado
- ✅ Alta disponibilidad (99.9% SLA)
- ✅ API RESTful con documentación completa

---

<p align="center">
  <strong>Desarrollado con ❤️ por el equipo de TecnoFact</strong>
</p>
