# TecnoFact SDK para Facturación Electrónica CFDI 4.0

SDK oficial de PHP para la integración con el servicio de Timbrado CFDI 4.0 de TecnoFact. Facilita la emisión, cancelación, consulta y reporte de facturas electrónicas cumpliendo con los requisitos del SAT mexicano.

[![Latest Stable Version](https://img.shields.io/packagist/v/tecnofact/sdk-tecnofact-php.svg)](https://packagist.org/packages/tecnofact/sdk-tecnofact-php)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tecnofact/sdk-tecnofact-php.svg)](https://packagist.org/packages/tecnofact/sdk-tecnofact-php)
[![License](https://img.shields.io/packagist/l/tecnofact/sdk-tecnofact-php.svg)](https://packagist.org/packages/tecnofact/sdk-tecnofact-php)
[![Tests](https://github.com/TecnoFact/SDK-Tecnofact-php/workflows/Tests/badge.svg)](https://github.com/TecnoFact/SDK-Tecnofact-php/actions/workflows/tests.yml)
[![Code Quality](https://github.com/TecnoFact/SDK-Tecnofact-php/workflows/Code%20Quality/badge.svg)](https://github.com/TecnoFact/SDK-Tecnofact-php/actions/workflows/code-quality.yml)
[![Security](https://github.com/TecnoFact/SDK-Tecnofact-php/workflows/Security/badge.svg)](https://github.com/TecnoFact/SDK-Tecnofact-php/actions/workflows/security.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-brightgreen)](https://phpstan.org/)
[![Psalm](https://img.shields.io/badge/Psalm-level%203-brightgreen)](https://psalm.dev/)
[![Docker](https://img.shields.io/badge/Docker-ready-blue)](https://www.docker.com/)

---

## 📋 Tabla de Contenidos

- [¿Qué es este SDK?](#-qué-es-este-sdk)
- [Características](#-características)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Configuración](#️-configuración)
- [Cómo Funciona el SDK](#-cómo-funciona-el-sdk)
- [Inicio Rápido](#-inicio-rápido)
- [Referencia de Servicios](#-referencia-de-servicios)
  - [AuthService](#authservice--autenticación)
  - [CfdiService](#cfdiservice--timbrado)
  - [CancelacionService](#cancelacionservice--cancelación)
  - [ConsultasService](#consultasservice--consultas)
  - [ReportesService](#reportesservice--reportes)
  - [ValidacionesService](#validacionesservice--validaciones-y-catálogos)
- [Modelos de Datos](#-modelos-de-datos)
- [Manejo de Errores](#️-manejo-de-errores)
- [Desarrollo Local con Docker](#-desarrollo-local-con-docker)
- [Testing](#-testing)
- [Análisis Estático](#-análisis-estático)
- [Versionado y Changelog](#-versionado-y-changelog)
- [Contribuciones](#-contribuciones)
- [Soporte](#-soporte)
- [Licencia](#-licencia)

---

## 🧭 ¿Qué es este SDK?

Este SDK es un cliente PHP que envuelve la API REST de TecnoFact. En lugar de armar peticiones HTTP a mano, construyes objetos tipados (`Emisor`, `Receptor`, `Concepto`, `Cfdi4Request`) y llamas a métodos de servicios (`timbrar`, `cancelar`, `buscarPorUuid`, etc.). El SDK se encarga de la autenticación por token, los reintentos automáticos y el mapeo de errores HTTP a excepciones específicas.

**Flujo de trabajo típico:**

1. Creas un `Config` con tus credenciales de TecnoFact.
2. Creas un `HttpClient` con esa configuración.
3. Te autenticas con `AuthService::login()` → esto guarda el token en el `Config`.
4. Usas los demás servicios (`CfdiService`, `CancelacionService`, etc.), que reutilizan ese token automáticamente.

---

## ✨ Características

- **Timbrado CFDI 4.0**: Emisión de facturas electrónicas por objeto tipado o por XML precalculado.
- **Cancelación**: Cancelación de CFDIs con motivo, consulta de estatus y acuses.
- **Consultas**: Búsqueda de CFDIs por UUID, RFC, serie/folio y verificación ante el SAT.
- **Reportes**: Resúmenes, ventas, cancelaciones y exportación a CSV/Excel.
- **Validaciones**: Validación de RFC, XML, certificados y consulta de catálogos del SAT.
- **Autenticación por token**: Login, refresh y logout gestionados por el SDK.
- **Reintentos automáticos**: Backoff exponencial ante errores 5xx y 429.
- **Tipado estricto**: `declare(strict_types=1)` en todo el código, PHPStan nivel 9.
- **Docker**: Entorno de desarrollo containerizado.

---

## 🔧 Requisitos

- **PHP**: >= 8.0 (recomendado 8.3+)
- **Extensiones**: `json`, `openssl`, `curl`
- **Composer**: Para la instalación de dependencias
- **Docker**: Para desarrollo (opcional)

---

## 📦 Instalación

```bash
composer require tecnofact/sdk-tecnofact-php
```

---

## ⚙️ Configuración

El SDK se autentica con el **correo y contraseña** de tu cuenta TecnoFact. Por el momento solo está disponible el entorno de **producción** (el sandbox aún no existe).

### Constructor directo

```php
<?php

require_once 'vendor/autoload.php';

use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Enums\Environment;

// Configuración con valores por defecto
$config = new Config(
    email: 'tu-email@ejemplo.com',
    password: 'tu-password'
);

// Configuración completa
$config = new Config(
    email: 'tu-email@ejemplo.com',
    password: 'tu-password',
    environment: Environment::PRODUCTION, // único entorno disponible por ahora
    timeout: 30,                          // segundos (1-300)
    retries: 3,                           // reintentos automáticos (0-10)
    verifySsl: true                       // verificación TLS (ver sección TLS)
);
```

El constructor valida los parámetros y lanza `InvalidArgumentException` si el email tiene formato inválido, la contraseña está vacía, el timeout está fuera de `1-300`, los reintentos fuera de `0-10` o `verifySsl` es una cadena vacía.

### Desde variables de entorno

```php
<?php

use TecnoFact\Sdk\Config\Config;

// Lee automáticamente de $_ENV o $_SERVER
$config = Config::fromEnvironment();
```

Variables soportadas:

| Variable | Requerida | Default |
|----------|-----------|---------|
| `TECN_FACT_EMAIL` | ✅ Sí | — |
| `TECN_FACT_PASSWORD` | ✅ Sí | — |
| `TECN_FACT_ENVIRONMENT` | No | `production` |
| `TECN_FACT_TIMEOUT` | No | `30` |
| `TECN_FACT_RETRIES` | No | `3` |
| `TECN_FACT_VERIFY_SSL` | No | `true` |

### Archivo `.env`

```bash
# .env
TECN_FACT_EMAIL=tu-email@ejemplo.com
TECN_FACT_PASSWORD=tu-password-aqui
# Solo production disponible por ahora (sandbox aún no existe)
TECN_FACT_ENVIRONMENT=production
TECN_FACT_TIMEOUT=30
TECN_FACT_RETRIES=3
# Verificación TLS: true | false | /ruta/al/bundle.pem
TECN_FACT_VERIFY_SSL=true
```

> El SDK no carga el `.env` por sí solo. Usa una librería como `vlucas/phpdotenv` para poblar `$_ENV` antes de llamar a `Config::fromEnvironment()`.

### Verificación TLS (`verifySsl`)

El parámetro `verifySsl` controla cómo se valida el certificado TLS del servidor. Se pasa tal cual a la opción `verify` de Guzzle:

| Valor | Comportamiento | Seguridad |
|-------|----------------|-----------|
| `true` (default) | Verifica con el bundle de CA del sistema. | ✅ Seguro |
| `'/ruta/bundle.pem'` | Verifica usando un bundle de CA propio (PEM). | ✅ Seguro |
| `false` | Desactiva la verificación TLS. | ⚠️ **Inseguro — solo desarrollo** |

```php
// Apuntar a un bundle de CA propio (recomendado si el servidor no envía la cadena completa)
$config = new Config(
    email: 'tu-email@ejemplo.com',
    password: 'tu-password',
    verifySsl: '/etc/ssl/certs/tecnofact-bundle.pem'
);
```

> **Nota sobre el panel:** el servidor `panelcfdi.tecnofact.mx` actualmente no envía el certificado intermedio en el handshake TLS (cadena incompleta), lo que provoca `cURL error 60` con la verificación por defecto. Hasta que el panel corrija su cadena, apunta `verifySsl` a un bundle PEM que incluya el certificado intermedio de la CA emisora. **No desactives la verificación (`false`) en producción.**

---

## 🔌 Cómo Funciona el SDK

El SDK no tiene un cliente "todo en uno": cada área funcional es un servicio independiente que recibe el `Config` y un `HttpClient`. El patrón siempre es el mismo:

```php
<?php

use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Http\HttpClient;
use TecnoFact\Sdk\Services\AuthService;
use TecnoFact\Sdk\Services\CfdiService;

// 1. Configuración
$config = new Config(email: 'tu-email@ejemplo.com', password: 'tu-password');

// 2. Cliente HTTP (Guzzle con reintentos automáticos)
$httpClient = new HttpClient($config);

// 3. Autenticación: guarda el access_token dentro de $config
$auth = new AuthService($config, $httpClient);
$auth->login($config->getEmail(), $config->getPassword());

// 4. A partir de aquí, cualquier servicio reutiliza el token guardado en $config
$cfdi = new CfdiService($config, $httpClient);
```

**Importante:** `AuthService::login()` guarda el `access_token` en la instancia de `$config` mediante `setToken()`. Todos los servicios leen ese token de la misma instancia de `$config`, así que **debes reutilizar el mismo objeto `$config`** para todos los servicios.

---

## 🚀 Inicio Rápido

Ejemplo completo: autenticarse y timbrar una factura CFDI 4.0.

```php
<?php

require_once 'vendor/autoload.php';

use TecnoFact\Sdk\Config\Config;
use TecnoFact\Sdk\Http\HttpClient;
use TecnoFact\Sdk\Services\AuthService;
use TecnoFact\Sdk\Services\CfdiService;
use TecnoFact\Sdk\Models\Emisor;
use TecnoFact\Sdk\Models\Receptor;
use TecnoFact\Sdk\Models\Concepto;
use TecnoFact\Sdk\Models\ImpuestosConcepto;
use TecnoFact\Sdk\Models\Traslado;
use TecnoFact\Sdk\Models\Cfdi4Request;
use TecnoFact\Sdk\Exceptions\TecnoFactException;

// 1. Configuración + cliente HTTP
$config = Config::fromEnvironment();
$httpClient = new HttpClient($config);

// 2. Autenticación
$auth = new AuthService($config, $httpClient);
$auth->login($config->getEmail(), $config->getPassword());

// 3. Construir la factura
$emisor = new Emisor(
    rfc: 'XAXX010101000',
    nombre: 'EMPRESA EMISORA SA DE CV',
    regimenFiscal: '601',
    cp: '06300'
);

$receptor = new Receptor(
    rfc: 'XAXX010101001',
    nombre: 'CLIENTE RECEPTOR',
    usoCfdi: 'G03',
    domicilioFiscalReceptor: '06300',
    regimenFiscalReceptor: '612'
);

$concepto = new Concepto(
    claveProdServ: '01010101',
    cantidad: 1,
    claveUnidad: 'E48',
    unidad: 'Unidad de servicio',
    descripcion: 'Servicio de desarrollo de software',
    valorUnitario: 10000.00,
    importe: 10000.00,
    objetoImp: '02', // Sí objeto de impuesto
    impuestos: new ImpuestosConcepto(
        traslados: [
            new Traslado(
                base: 10000.00,
                impuesto: '002',        // IVA
                tipoFactor: 'Tasa',
                tasaOCuota: '0.160000',
                importe: 1600.00
            ),
        ]
    )
);

$request = new Cfdi4Request(
    emisor: $emisor,
    receptor: $receptor,
    conceptos: [$concepto],
    formaPago: '03',            // Transferencia electrónica
    metodoPago: 'PUE',          // Pago en una sola exhibición
    tipoComprobante: 'I',       // Ingreso
    lugarExpedicion: '06300',
    subTotal: 10000.00,
    total: 11600.00,
    fecha: new DateTime()
);

// 4. Timbrar
try {
    $cfdi = new CfdiService($config, $httpClient);
    $respuesta = $cfdi->timbrar($request);

    echo "UUID: " . ($respuesta->getUuid() ?? 'N/A') . "\n";
    // $respuesta->getXmlTimbrado(), $respuesta->isSuccess(), $respuesta->getRaw()
} catch (TecnoFactException $e) {
    echo "Error al timbrar: " . $e->getMessage() . "\n";
    echo "Request ID: " . ($e->getRequestId() ?? 'N/A') . "\n";
}
```

> Todos los métodos de servicio devuelven `array<string, mixed>` con la respuesta JSON decodificada de la API.

---

## 📚 Referencia de Servicios

Todos los servicios extienden la clase base `Service` y se construyen igual:

```php
$servicio = new NombreService($config, $httpClient);
```

### `AuthService` — Autenticación

| Método | Descripción |
|--------|-------------|
| `login(string $email, string $password): array` | Autentica y guarda el `access_token` en `$config`. |
| `refreshToken(string $refreshToken): array` | Renueva el token de acceso. |
| `logout(): bool` | Cierra sesión y limpia el token en `$config`. |

### `CfdiService` — Timbrado

| Método | Descripción |
|--------|-------------|
| `timbrar(Cfdi4Request $cfdi): ResultadoTimbrado` | Construye el XML CFDI 4.0 (`I`/`E`) desde el objeto tipado y lo envía a timbrar. |
| `timbrarPago(PagoRequest $request): ResultadoTimbrado` | Construye el XML del Complemento de Recepción de Pagos 2.0 (`P`) y lo envía a timbrar. |
| `timbrarXml(string $xml): ResultadoTimbrado` | Timbra a partir de un XML ya construido por el integrador. |
| `validar(string $xml): EstatusCfdi` | Consulta el estatus/validez de un CFDI timbrado. Envía el XML como `multipart/form-data` (campo `xml`) a `/api/v1/validation-cfdi`. |
| `getXml(string $uuid): array` | Obtiene el XML del CFDI timbrado. |
| `getPdf(string $uuid): array` | Obtiene el PDF del CFDI. |
| `getHtml(string $uuid): array` | Obtiene la representación HTML del CFDI. |
| `getStatus(string $uuid): array` | Consulta el estatus del CFDI. |
| `sendByEmail(string $uuid, string $email): array` | Envía el CFDI por correo electrónico. |

**Cómo se timbra (construcción del XML):** `timbrar()` y `timbrarPago()` construyen el XML con `CfdiXmlBuilder` y lo envían como `{"xml": "..."}`. El SDK genera el comprobante **sin sellar**: el panel se encarga del sello (CSD del emisor) y el timbrado ante el SAT. El SDK **nunca** emite `Sello`, `NoCertificado`, `Certificado` ni `TimbreFiscalDigital`, y **nunca** maneja la llave privada del CSD.

> La `Fecha` la define el integrador; el SDK no la genera ni la modifica. Debe cumplir la regla del SAT (dentro de 72 horas del timbrado).
>
> **Alcance del builder:** tipos `I` (Ingreso), `E` (Egreso) y `P` (Pagos / Complemento de Recepción de Pagos 2.0). El tipo `N` (Nómina), que requiere su propio complemento, queda para una versión posterior.

**Timbrar un CFDI de Pagos (REP):**

```php
use TecnoFact\Sdk\Models\DoctoRelacionado;
use TecnoFact\Sdk\Models\Emisor;
use TecnoFact\Sdk\Models\Pago;
use TecnoFact\Sdk\Models\PagoRequest;
use TecnoFact\Sdk\Models\Receptor;

$request = new PagoRequest(
    emisor: new Emisor(rfc: 'KFR250210TQ1', nombre: 'KBA FILTERS Y REFACCIONES', regimenFiscal: '601', cp: '06300'),
    receptor: new Receptor(
        rfc: 'XAXX010101000',
        nombre: 'PÚBLICO EN GENERAL',
        usoCfdi: 'CP01',                   // CP01 = Pagos
        domicilioFiscalReceptor: '06300',
        regimenFiscalReceptor: '616'
    ),
    pagos: [
        new Pago(
            fechaPago: new DateTime('2026-06-04T09:34:41'),
            formaDePagoP: '01',            // Efectivo
            monedaP: 'MXN',
            monto: '1.00',
            doctosRelacionados: [
                new DoctoRelacionado(
                    idDocumento: '4EE306E1-59B0-4F0D-BA73-9C3126034CBC',
                    monedaDR: 'MXN',
                    equivalenciaDR: '1',
                    numParcialidad: 1,
                    impSaldoAnt: '1.00',
                    impPagado: '1.00',
                    impSaldoInsoluto: '0.00',
                    objetoImpDR: '01',
                    folio: '107'
                ),
            ]
        ),
    ],
    fecha: new DateTime('2026-06-04T09:34:42'),
    lugarExpedicion: '06300',
    serie: 'PAG',
    folio: '105'
);

// El SDK genera automáticamente: Moneda=XXX, SubTotal=0, Total=0,
// el Concepto fijo (84111506/ACT/Pago/0) y el pago20:Pagos con Totales.
$resultado = $cfdi->timbrarPago($request);
```

### `CancelacionService` — Cancelación

| Método | Descripción |
|--------|-------------|
| `cancelar(string $rfc, string $uuid, string $motivo): AcuseCancelacion` | Cancela un CFDI ante el SAT. Envía `{rfc, uuid, motivo}` (JSON) a `/api/v1/cancelled-cfdi`. |
| `getStatus(string $uuid): array` | Consulta el estatus de una cancelación. |
| `obtenerAcuse(string $uuid): array` | Obtiene el acuse de cancelación. |
| `getPending(): array` | Lista las cancelaciones pendientes. |

### `ConsultasService` — Consultas

| Método | Descripción |
|--------|-------------|
| `buscarPorUuid(string $uuid): array` | Busca un CFDI por su UUID. |
| `buscarPorRfc(string $rfc, ?string $fechaInicio = null, ?string $fechaFin = null): array` | Busca CFDIs por RFC, con rango de fechas opcional. |
| `buscarPorSerie(string $serie, string $folio): array` | Busca un CFDI por serie y folio. |
| `verificarSat(string $uuid): array` | Verifica el estatus del CFDI directamente ante el SAT. |
| `listar(int $page = 1, int $perPage = 20): array` | Lista CFDIs paginados. |

### `ReportesService` — Reportes

| Método | Descripción |
|--------|-------------|
| `getResumen(string $fechaInicio, string $fechaFin): array` | Resumen general del periodo. |
| `getVentas(?string $fechaInicio = null, ?string $fechaFin = null): array` | Reporte de ventas. |
| `getCancelaciones(string $fechaInicio, string $fechaFin): array` | Reporte de cancelaciones. |
| `getXml(string $uuid): array` | Reporte XML de un CFDI. |
| `getPdf(string $uuid): array` | Reporte PDF de un CFDI. |
| `exportarCsv(string $fechaInicio, string $fechaFin): array` | Exporta el periodo a CSV. |
| `exportarExcel(string $fechaInicio, string $fechaFin): array` | Exporta el periodo a Excel (xlsx). |

### `ValidacionesService` — Validaciones y Catálogos

| Método | Descripción |
|--------|-------------|
| `validarXml(string $xml): array` | Valida la estructura de un XML CFDI. |
| `validarRfc(string $rfc): array` | Valida un RFC ante el SAT. |
| `validarNoCertificado(string $noCertificado): array` | Valida un número de certificado. |
| `getCatalogos(): array` | Obtiene los catálogos del SAT. |
| `getUnidadesMedida(): array` | Catálogo de unidades de medida. |
| `getProductosServicios(): array` | Catálogo de productos y servicios. |
| `getImpuestos(): array` | Catálogo de impuestos. |

**Ejemplo — cancelar un CFDI:**

```php
use TecnoFact\Sdk\Services\CancelacionService;

$cancelacion = new CancelacionService($config, $httpClient);

// Motivo 03: No se llevó a cabo la operación
$acuse = $cancelacion->cancelar(
    rfc: 'IIA040805DZ4',
    uuid: 'e9a311f0-62e7-4f28-a218-76cef85dc6ba',
    motivo: '03'
);

if ($acuse->isAceptadaPorSat()) {          // estatus 201 del SAT
    file_put_contents('acuse.pdf', $acuse->getPdfBinario());
    file_put_contents('acuse.xml', $acuse->getXml());
}
```

**Ejemplo — consultar el estatus de un CFDI:**

```php
use TecnoFact\Sdk\Services\CfdiService;

$cfdi = new CfdiService($config, $httpClient);

// Se envía el XML del comprobante timbrado (multipart/form-data)
$estatus = $cfdi->validar($xmlTimbrado);

if ($estatus->isVigente()) {
    echo "CFDI vigente ({$estatus->getCodigo()})\n";
}
```

---

## 📦 Respuestas Tipadas

Las operaciones principales devuelven objetos tipados (namespace `TecnoFact\Sdk\Responses`) en lugar de arrays crudos. Todos exponen `getRaw()` con la respuesta original del panel por si necesitas un campo no mapeado.

### `ResultadoTimbrado` — devuelto por `timbrar()` / `timbrarXml()`

| Método | Descripción |
|--------|-------------|
| `isSuccess(): bool` | Si el timbrado fue exitoso. |
| `getCode(): ?int` | Código de respuesta del panel. |
| `getMessage(): ?string` | Mensaje del panel (o error). |
| `getXmlTimbrado(): ?string` | XML del comprobante ya timbrado (con `TimbreFiscalDigital`). |
| `getUuid(): ?string` | UUID del comprobante, si el panel lo devuelve. |
| `getRaw(): array` | Respuesta cruda. |

### `EstatusCfdi` — devuelto por `validar()`

| Método | Descripción |
|--------|-------------|
| `isVigente(): bool` | Si el CFDI está vigente. |
| `getEstado(): ?string` | Estado (ej. `Vigente`, `Cancelado`). |
| `getCodigo(): ?string` | Código del SAT (ej. `S - Cancelable con aceptación`). |
| `getEsCancelable(): ?string` | Si es cancelable y bajo qué condición. |
| `getEstatusCancelacion(): ?string` | Estatus de una cancelación en curso. |
| `getEfos(): ?string` | Situación en la lista EFOS. |

### `AcuseCancelacion` — devuelto por `cancelar()`

| Método | Descripción |
|--------|-------------|
| `isValidado(): bool` | Si la solicitud fue validada. |
| `isAceptadaPorSat(): bool` | Si el SAT aceptó la cancelación (estatus `201`). |
| `getStatusSat(): ?string` | Texto del estatus del SAT. |
| `getUuid(): ?string` | UUID de la solicitud de cancelación. |
| `getXml(): ?string` | XML del acuse (con sello del SAT). |
| `getPdfBase64(): ?string` | PDF del acuse en base64. |
| `getPdfBinario(): ?string` | PDF del acuse ya decodificado (bytes), listo para guardar. |

---

## 📋 Modelos de Datos

Los modelos son objetos tipados que representan las partes de un CFDI. Cada uno expone `toArray()` para serialización.

### Estructura del SDK

```
src/
├── Config/
│   └── Config.php                  # Configuración del SDK (credenciales, entorno, token)
├── Contracts/
│   └── HttpClientInterface.php     # Interfaz del cliente HTTP
├── Enums/
│   ├── Environment.php             # Entorno (solo PRODUCTION por ahora)
│   └── TipoComprobante.php         # Tipos de CFDI (I, E, T, N, P)
├── Exceptions/
│   ├── TecnoFactException.php      # Excepción base
│   ├── AuthenticationException.php # Error de autenticación (401)
│   ├── ValidationException.php     # Error de validación (400/422)
│   ├── TimbradoException.php       # Error de timbrado
│   ├── CancelacionException.php    # Error de cancelación
│   ├── NotFoundException.php       # Recurso no encontrado (404)
│   ├── RateLimitException.php      # Límite de peticiones (429)
│   └── ServerException.php         # Error del servidor (5xx)
├── Http/
│   └── HttpClient.php              # Cliente HTTP con Guzzle + reintentos
├── Models/
│   ├── DoctoRelacionado.php        # Documento relacionado en un pago (REP)
│   ├── Emisor.php                  # Datos del emisor
│   ├── Receptor.php                # Datos del receptor
│   ├── Concepto.php                # Conceptos de la factura
│   ├── Cfdi4Request.php            # Solicitud de timbrado CFDI 4.0
│   ├── InformacionGlobal.php       # Comprobante global (público en general)
│   ├── Pago.php                    # Nodo Pago del Complemento de Pagos 2.0
│   ├── PagoRequest.php             # Solicitud de timbrado de CFDI tipo P
│   ├── CfdiRelacionados.php        # CFDIs relacionados
│   ├── Impuestos.php               # Impuestos globales
│   ├── ImpuestosConcepto.php       # Impuestos por concepto
│   ├── Traslado.php / TrasladoGlobal.php     # Traslado de impuestos
│   ├── Retencion.php / RetencionGlobal.php   # Retención de impuestos
│   ├── CuentaPredial.php           # Cuenta predial
│   ├── InformacionAduanera.php     # Información aduanera
│   └── Parte.php                   # Partes/componentes de un concepto
├── Responses/
│   ├── ResultadoTimbrado.php       # Respuesta tipada de timbrar()
│   ├── EstatusCfdi.php             # Respuesta tipada de validar()
│   └── AcuseCancelacion.php        # Respuesta tipada de cancelar()
├── Services/
│   ├── Service.php                 # Clase base de los servicios
│   ├── AuthService.php             # Autenticación
│   ├── CfdiService.php             # Timbrado y estatus
│   ├── CancelacionService.php      # Cancelación
│   ├── ConsultasService.php        # Consultas
│   ├── ReportesService.php         # Reportes
│   └── ValidacionesService.php     # Validaciones y catálogos
└── Xml/
    └── CfdiXmlBuilder.php          # Construye el XML CFDI 4.0 (sin sellar)
```

### Emisor

```php
use TecnoFact\Sdk\Models\Emisor;

$emisor = new Emisor(
    rfc: 'XAXX010101000',
    nombre: 'EMPRESA EMISORA SA DE CV',
    regimenFiscal: '601',
    cp: '06300',
    facAtrAdm: null // opcional
);
```

### Receptor

```php
use TecnoFact\Sdk\Models\Receptor;

$receptor = new Receptor(
    rfc: 'XAXX010101001',
    nombre: 'CLIENTE RECEPTOR',
    usoCfdi: 'G03',
    domicilioFiscalReceptor: '06300',
    regimenFiscalReceptor: '612',
    residenciaFiscal: null, // opcional
    numRegIdTrib: null      // opcional
);
```

### Concepto con impuestos

> ⚠️ El parámetro `unidad` no tiene valor por defecto: debes pasarlo siempre (puede ser `null`).

```php
use TecnoFact\Sdk\Models\Concepto;
use TecnoFact\Sdk\Models\ImpuestosConcepto;
use TecnoFact\Sdk\Models\Traslado;

$concepto = new Concepto(
    claveProdServ: '01010101',
    cantidad: 1,
    claveUnidad: 'E48',
    unidad: 'Unidad de servicio', // requerido (puede ser null)
    descripcion: 'Servicio de desarrollo de software',
    valorUnitario: 10000.00,
    importe: 10000.00,
    objetoImp: '02', // Sí objeto de impuesto
    impuestos: new ImpuestosConcepto(
        traslados: [
            new Traslado(
                base: 10000.00,
                impuesto: '002',      // IVA
                tipoFactor: 'Tasa',
                tasaOCuota: '0.160000',
                importe: 1600.00
            ),
        ]
    )
);
```

### Enum `Environment`

```php
use TecnoFact\Sdk\Enums\Environment;

$env = Environment::PRODUCTION;

echo $env->value;          // 'production'
echo $env->isProduction(); // true
echo $env->label();        // 'Producción'
```

### Enum `TipoComprobante`

```php
use TecnoFact\Sdk\Enums\TipoComprobante;

echo TipoComprobante::INGRESO->value;  // 'I'
echo TipoComprobante::EGRESO->label(); // 'Egreso'
// Casos: INGRESO (I), EGRESO (E), TRASLADO (T), NOMINA (N), PAGO (P)
```

---

## ⚠️ Manejo de Errores

El `HttpClient` traduce los códigos HTTP de la API a excepciones específicas. Todas heredan de `TecnoFactException`, así que puedes capturar la base para manejarlas todas juntas.

| Excepción | Descripción | Código HTTP |
|-----------|-------------|-------------|
| `ValidationException` | Datos inválidos (expone `getErrors()`) | 400 / 422 |
| `AuthenticationException` | Credenciales o token inválidos | 401 |
| `NotFoundException` | Recurso no encontrado | 404 |
| `RateLimitException` | Límite de peticiones excedido | 429 |
| `ServerException` | Error del servidor | 5xx |
| `TimbradoException` | Error específico de timbrado | — |
| `CancelacionException` | Error específico de cancelación | — |

Todas exponen `getRequestId()` y `getResponseData()` desde la clase base.

```php
use TecnoFact\Sdk\Exceptions\ValidationException;
use TecnoFact\Sdk\Exceptions\AuthenticationException;
use TecnoFact\Sdk\Exceptions\RateLimitException;
use TecnoFact\Sdk\Exceptions\TecnoFactException;

try {
    $respuesta = $cfdi->timbrar($request);
} catch (ValidationException $e) {
    echo "Error de validación: " . $e->getMessage() . "\n";
    print_r($e->getErrors());
} catch (AuthenticationException $e) {
    echo "Error de autenticación: " . $e->getMessage() . "\n";
} catch (RateLimitException $e) {
    echo "Límite excedido, reintenta en " . $e->getRetryAfter() . "s\n";
} catch (TecnoFactException $e) {
    echo "Error del SDK: " . $e->getMessage() . "\n";
    echo "Request ID: " . ($e->getRequestId() ?? 'N/A') . "\n";
}
```

> **Reintentos automáticos:** el `HttpClient` reintenta con backoff exponencial (1s, 2s, 4s…) ante respuestas `5xx` y `429`, hasta el máximo definido en `retries`. Solo después de agotar los reintentos se lanza la excepción.

---

## 🐳 Desarrollo Local con Docker

```bash
# Clonar el repositorio
git clone https://github.com/TecnoFact/SDK-Tecnofact-php.git
cd SDK-Tecnofact-php

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
```

### Makefile (alternativa)

```bash
make docker-build    # Construir imagen
make docker-up       # Iniciar entorno
make docker-test     # Ejecutar tests
make docker-analyze  # Ejecutar PHPStan
make docker-lint     # Verificar estilo
make docker-ci       # CI completo
```

---

## 🧪 Testing

```bash
# Con Docker
docker-compose run --rm sdk vendor/bin/phpunit

# O local (requiere PHP 8.x)
composer install
composer test

# Con cobertura
composer test-coverage        # Reporte HTML en coverage/
composer test-coverage-text   # Reporte en consola
```

La suite está organizada en `tests/Unit`, `tests/Integration`, `tests/Security` y `tests/Fixtures`.

---

## 🔍 Análisis Estático

El proyecto usa **PHPStan nivel 9** y **Psalm** para garantizar la calidad del tipado.

```bash
# PHPStan + Psalm juntos
composer analyze

# Solo estilo (dry-run)
composer style

# Corregir estilo automáticamente
composer fix-style

# Verificación completa (estilo + análisis + tests)
composer check
```

Configuración de PHPStan (`phpstan.neon`):

```yaml
parameters:
    level: 9
    paths:
        - src
        - tests
```

---

## 📦 Versionado y Changelog

Este proyecto utiliza [Semantic Versioning](https://semver.org/) y genera automáticamente versiones y changelog basado en [Conventional Commits](https://www.conventionalcommits.org/).

### Conventional Commits

```bash
<tipo>(<alcance>): <descripción>

# Ejemplos:
feat: agregar soporte para CFDI 4.0     # MINOR (1.x.0)
fix: corregir validación de RFC         # PATCH (1.0.x)
feat!: cambiar estructura de API        # MAJOR (x.0.0)
```

**Tipos principales:**

- `feat`: Nueva funcionalidad (MINOR)
- `fix`: Corrección de bug (PATCH)
- `perf`: Mejora de rendimiento (PATCH)
- `docs`: Cambios en documentación (PATCH)
- `refactor`: Refactorización (PATCH)
- `test` / `ci` / `chore`: No generan versión

El archivo [CHANGELOG.md](CHANGELOG.md) se actualiza automáticamente con cada release. Las releases se generan al hacer push a:

- **`main`**: Versiones estables (1.0.0, 1.1.0, 2.0.0)
- **`develop`**: Versiones beta (1.0.0-beta.1, 1.1.0-beta.2)

Ver todas las releases en [GitHub Releases](https://github.com/TecnoFact/SDK-Tecnofact-php/releases).

---

## 🤝 Contribuciones

Lee nuestra [Guía de Contribución](CONTRIBUTING.md) para detalles sobre el proceso de desarrollo.

### Proceso rápido

1. Fork el repositorio
2. Crea una rama (`git checkout -b feat/nueva-funcionalidad`)
3. Commit con formato convencional (`git commit -m "feat: descripción"`)
4. Push a la rama (`git push origin feat/nueva-funcionalidad`)
5. Abre un Pull Request hacia `develop`

### Estándares de código

- ✅ `declare(strict_types=1)` en todos los archivos
- ✅ Tipado completo (parámetros, retornos, propiedades)
- ✅ PSR-12 coding standard
- ✅ Enums para valores fijos
- ✅ Tests passing
- ✅ PHPStan nivel 9 sin errores
- ✅ Psalm sin errores
- ✅ Commits siguen Conventional Commits

---

## 💬 Soporte

- 📧 Email: soporte@tecnofact.mx
- 🌐 Website: [https://www.tecnofact.mx](https://www.tecnofact.mx)
- 📖 Documentación API: [https://docs.tecnofact.mx](https://docs.tecnofact.mx)
- 🐛 Issues: [GitHub Issues](https://github.com/TecnoFact/SDK-Tecnofact-php/issues)

---

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

---

<p align="center">
  <strong>Desarrollado con ❤️ por el equipo de TecnoFact</strong>
</p>
