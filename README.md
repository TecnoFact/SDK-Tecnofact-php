# TecnoFact SDK para Facturación Electrónica CFDI 4.0

SDK oficial de PHP para la integración con el servicio de Timbrado CFDI 4.0 de TecnoFact. Facilita la emisión, cancelación y consulta de facturas electrónicas cumpliendo con los requisitos del SAT mexicano.

[![Latest Stable Version](https://img.shields.io/packagist/v/tecnofact/sdk-tecnofact.svg)](https://packagist.org/packages/tecnofact/sdk-tecnofact)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tecnofact/sdk-tecnofact.svg)](https://packagist.org/packages/tecnofact/sdk-tecnofact)
[![License](https://img.shields.io/packagist/l/tecnofact/sdk-tecnofact.svg)](https://packagist.org/packages/tecnofact/sdk-tecnofact)
[![Build Status](https://github.com/TecnoFact/SDK-Tecnofact-php/workflows/CI/badge.svg)](https://github.com/TecnoFact/SDK-Tecnofact-php/actions)

---

## 📋 Tabla de Contenidos

- [Características](#características)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Uso Rápido](#uso-rápido)
- [Autenticación](#autenticación)
- [Timbrado CFDI](#timbrado-cfdi)
- [Cancelación](#cancelación)
- [Consultas](#consultas)
- [Reportes](#reportes)
- [Manejo de Errores](#manejo-de-errores)
- [Ejemplos Completos](#ejemplos-completos)
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
- **Manejo de Errores**: Excepciones personalizadas y mensajes descriptivos
- **Tipado Estricto**: Compatible con PHP 8.0+

---

## 🔧 Requisitos

- **PHP**: >= 8.0 (Recomendado 8.3+)
- **Extensiones**: `json`, `openssl`, `curl`
- **Composer**: Para la instalación de dependencias

---

## 📦 Instalación

Instala el SDK usando Composer:

```bash
composer require tecnofact/sdk-tecnofact
```

---

## ⚙️ Configuración

### Inicialización del Cliente

```php
<?php

require_once 'vendor/autoload.php';

use TecnoFact\Sdk\Client;
use TecnoFact\Sdk\Config;

// Configuración del cliente
$config = new Config(
    apiKey: 'tu-api-key',
    apiSecret: 'tu-api-secret',
    environment: Config::ENVIRONMENT_SANDBOX, // o Config::ENVIRONMENT_PRODUCTION
    timeout: 30 // segundos (opcional, default: 30)
);

// Crear instancia del cliente
$client = new Client($config);
```

### Variables de Entorno

También puedes usar variables de entorno:

```php
use TecnoFact\Sdk\Config;

$config = Config::fromEnvironment();
```

Variables requeridas:
- `TECN_FACT_API_KEY` - Tu API Key
- `TECN_FACT_API_SECRET` - Tu API Secret
- `TECN_FACT_ENVIRONMENT` - `sandbox` o `production`

---

## 🚀 Uso Rápido

### Ejemplo de Timbrado Completo

```php
<?php

use TecnoFact\Sdk\Client;
use TecnoFact\Sdk\Config;
use TecnoFact\Sdk\Models\Cfdi4Request;
use TecnoFact\Sdk\Models\Emisor;
use TecnoFact\Sdk\Models\Receptor;
use TecnoFact\Sdk\Models\Concepto;

$config = new Config(
    apiKey: 'tu-api-key',
    apiSecret: 'tu-api-secret',
    environment: Config::ENVIRONMENT_PRODUCTION
);

$client = new Client($config);

// Crear el emisor
$emisor = new Emisor(
    rfc: 'XAXX010101000',
    nombre: 'EMPRESA EMISORA SA DE CV',
    regimenFiscal: '601', // General de Ley Personas Morales
    cp: '06300'
);

// Crear el receptor
$receptor = new Receptor(
    rfc: 'XAXX010101001',
    nombre: 'CLIENTE RECEPTOR',
    usoCfdi: 'G03', // Gastos en general
    domicilioFiscalReceptor: '06300',
    regimenFiscalReceptor: '612' // Personas Físicas con Actividades Empresariales y Profesionales
);

// Crear conceptos
$conceptos = [
    new Concepto(
        claveProdServ: '01010101',
        cantidad: 1,
        claveUnidad: 'E48',
        descripcion: 'Servicio de desarrollo de software',
        valorUnitario: 10000.00,
        importe: 10000.00,
        objetoImp: '02' // Sí objeto de impuesto
    )
];

// Crear la solicitud de timbrado
$cfdiRequest = new Cfdi4Request(
    emisor: $emisor,
    receptor: $receptor,
    conceptos: $conceptos,
    formaPago: '01', // Efectivo
    metodoPago: 'PUE', // Pago en una sola exhibición
    tipoComprobante: 'I', // Ingreso
    serie: 'F',
    folio: '1001',
    lugarExpedicion: '06300',
    subTotal: 10000.00,
    total: 11600.00, // Incluye IVA
    moneda: 'MXN',
    tipoCambio: 1,
    fecha: new DateTime()
);

// Timbrar el CFDI
try {
    $response = $client->timbrarCfdi($cfdiRequest);
    
    echo "✅ CFDI Timbrado exitosamente!\n";
    echo "UUID: {$response->getUuid()}\n";
    echo "Folio Fiscal: {$response->getFolioFiscal()}\n";
    echo "Fecha Timbrado: {$response->getFechaTimbrado()}\n";
    echo "Sello CFD: {$response->getSelloCfd()}\n";
    echo "Sello SAT: {$response->getSelloSat()}\n";
    echo "Cadena Original SAT: {$response->getCadenaOriginalSat()}\n";
    
    // Guardar el XML timbrado
    file_put_contents('cfdi_timbrado.xml', $response->getXml());
    
} catch (TecnoFact\Sdk\Exceptions\ValidationException $e) {
    echo "❌ Error de validación: {$e->getMessage()}\n";
    print_r($e->getErrors());
} catch (TecnoFact\Sdk\Exceptions\AuthenticationException $e) {
    echo "❌ Error de autenticación: {$e->getMessage()}\n";
} catch (TecnoFact\Sdk\Exceptions\TimbradoException $e) {
    echo "❌ Error de timbrado: {$e->getMessage()}\n";
    echo "Código de error: {$e->getCodigoError()}\n";
} catch (Exception $e) {
    echo "❌ Error inesperado: {$e->getMessage()}\n";
}
```

---

## 🔐 Autenticación

El SDK maneja la autenticación automáticamente usando tu API Key y Secret. No es necesario gestionar tokens manualmente.

### Verificar Credenciales

```php
$isValid = $client->auth()->verificarCredenciales();

if ($isValid) {
    echo "✅ Credenciales válidas";
} else {
    echo "❌ Credenciales inválidas";
}
```

### Obtener Información del Usuario

```php
$userInfo = $client->auth()->getUserInfo();

echo "Usuario: {$userInfo->getNombre()}\n";
echo "RFC: {$userInfo->getRfc()}\n";
echo "Timbrados Disponibles: {$userInfo->getTimbradosDisponibles()}\n";
```

---

## 📄 Timbrado CFDI

### Timbrar CFDI 4.0

```php
use TecnoFact\Sdk\Models\Cfdi4Request;

$cfdiRequest = new Cfdi4Request(
    // ... configuración del CFDI
);

$response = $client->cfdi()->timbrar($cfdiRequest);
```

### Timbrar XML Pre-generado

Si ya tienes un XML generado:

```php
$xmlContent = file_get_contents('cfdi_generado.xml');

$response = $client->cfdi()->timbrarXml($xmlContent);
```

### Timbrado Masivo

Para múltiples CFDIs:

```php
$cfdis = [$cfdiRequest1, $cfdiRequest2, $cfdiRequest3];

$responses = $client->cfdi()->timbrarMasivo($cfdis);

foreach ($responses as $response) {
    if ($response->isSuccess()) {
        echo "Timbrado: {$response->getUuid()}\n";
    } else {
        echo "Error: {$response->getErrorMessage()}\n";
    }
}
```

### Timbrado Retraso (CFDI 3.3)

Para CFDIs con fecha de emisión diferente:

```php
use TecnoFact\Sdk\Models\Cfdi33Request;

$cfdiRequest = new Cfdi33Request(
    // ... configuración del CFDI 3.3
);

$response = $client->cfdi()->timbrarRetraso($cfdiRequest);
```

---

## ❌ Cancelación

### Cancelar CFDI

```php
use TecnoFact\Sdk\Models\CancelacionRequest;

$cancelRequest = new CancelacionRequest(
    uuid: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
    rfcEmisor: 'XAXX010101000',
    motivo: '01', // 01: Comprobante emitido con errores con relación
    folioSustitucion: null, // Solo si aplica
    cer: file_get_contents('certificado.cer'),
    key: file_get_contents('llave.key'),
    password: 'contraseña-llave'
);

$response = $client->cancelacion()->cancelar($cancelRequest);

if ($response->isSuccess()) {
    echo "✅ CFDI cancelado exitosamente\n";
    echo "Acuse de cancelación: {$response->getAcuseCancelacion()}\n";
    echo "Fecha de cancelación: {$response->getFechaCancelacion()}\n";
} else {
    echo "❌ Error: {$response->getErrorMessage()}\n";
}
```

### Consultar Estado de Cancelación

```php
$estado = $client->cancelacion()->consultarEstado(
    uuid: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
    rfcEmisor: 'XAXX010101000',
    rfcReceptor: 'XAXX010101001',
    total: 11600.00
);

echo "Estado: {$estado->getEstado()}\n"; // Cancelable, No cancelable, Cancelado, etc.
```

---

## 🔍 Consultas

### Buscar CFDIs

```php
use TecnoFact\Sdk\Models\FiltroBusqueda;

$filtro = new FiltroBusqueda(
    fechaInicio: new DateTime('2024-01-01'),
    fechaFin: new DateTime('2024-12-31'),
    rfcEmisor: 'XAXX010101000',
    rfcReceptor: 'XAXX010101001',
    estatus: 'vigente', // 'vigente' o 'cancelado'
    tipoComprobante: 'I'
);

$resultados = $client->consultas()->buscar($filtro);

foreach ($resultados->getCfdis() as $cfdi) {
    echo "UUID: {$cfdi->getUuid()}\n";
    echo "Folio: {$cfdi->getFolio()}\n";
    echo "Total: {$cfdi->getTotal()}\n";
    echo "Fecha: {$cfdi->getFechaTimbrado()}\n";
}
```

### Obtener CFDI por UUID

```php
$cfdi = $client->consultas()->obtenerPorUuid(
    uuid: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'
);

echo "XML: {$cfdi->getXml()}\n";
echo "PDF: {$cfdi->getPdf()}\n";
```

### Descargar XML y PDF

```php
// Descargar XML
$xml = $client->consultas()->descargarXml(
    uuid: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'
);
file_put_contents('cfdi.xml', $xml);

// Descargar PDF
$pdf = $client->consultas()->descargarPdf(
    uuid: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'
);
file_put_contents('cfdi.pdf', $pdf);
```

### Verificar Vigencia en SAT

```php
$vigencia = $client->consultas()->verificarVigenciaSat(
    uuid: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
    rfcEmisor: 'XAXX010101000',
    rfcReceptor: 'XAXX010101001',
    total: 11600.00
);

echo "Estatus en SAT: {$vigencia->getEstatus()}\n"; // Vigente, Cancelado, No encontrado
echo "Es cancelable: " . ($vigencia->isCancelable() ? 'Sí' : 'No') . "\n";
```

---

## 📊 Reportes

### Reporte de Timbrados

```php
use TecnoFact\Sdk\Models\FiltroReporte;

$filtro = new FiltroReporte(
    fechaInicio: new DateTime('2024-01-01'),
    fechaFin: new DateTime('2024-01-31'),
    rfcEmisor: 'XAXX010101000'
);

$reporte = $client->reportes()->timbrados($filtro);

echo "Total timbrados: {$reporte->getTotal()}\n";
echo "Monto total: {$reporte->getMontoTotal()}\n";

foreach ($reporte->getDetalle() as $item) {
    echo "{$item->getFecha()}: {$item->getCantidad()} CFDIs - {$item->getMonto()}\n";
}
```

### Reporte de Cancelados

```php
$reporte = $client->reportes()->cancelados($filtro);
```

### Estadísticas

```php
$stats = $client->reportes()->estadisticas(
    rfcEmisor: 'XAXX010101000',
    periodo: 'mes', // 'dia', 'semana', 'mes', 'anio'
    fechaInicio: new DateTime('2024-01-01'),
    fechaFin: new DateTime('2024-12-31')
);
```

---

## ✅ Validaciones y Catálogos

### Validar RFC

```php
$isValid = $client->validaciones()->validarRfc('XAXX010101000');

if ($isValid) {
    echo "✅ RFC válido";
} else {
    echo "❌ RFC inválido";
}
```

### Obtener Catálogos SAT

```php
// Regímenes Fiscales
$regimenes = $client->catalogos()->regimenesFiscales();

// Usos CFDI
$usosCfdi = $client->catalogos()->usosCfdi();

// Tipos de Comprobante
$tiposComprobante = $client->catalogos()->tiposComprobante();

// Formas de Pago
$formasPago = $client->catalogos()->formasPago();

// Métodos de Pago
$metodosPago = $client->catalogos()->metodosPago();

// Productos y Servicios (c_ClaveProdServ)
$productos = $client->catalogos()->productosServicios(
    busqueda: 'software'
);
```

---

## 🏥 Health Checks

### Verificar Estado de Servicios

```php
$health = $client->health()->check();

echo "Estado del servicio: {$health->getStatus()}\n"; // operational, degraded, down

if ($health->isOperational()) {
    echo "✅ Todos los servicios operativos\n";
} else {
    echo "⚠️ Servicios afectados:\n";
    foreach ($health->getIssues() as $issue) {
        echo "  - {$issue->getService()}: {$issue->getMessage()}\n";
    }
}

// Verificar componentes específicos
$timbradoStatus = $client->health()->timbrado();
$cancelacionStatus = $client->health()->cancelacion();
$consultasStatus = $client->health()->consultas();
```

---

## ⚠️ Manejo de Errores

El SDK utiliza excepciones específicas para diferentes tipos de errores:

| Excepción | Descripción | Código HTTP |
|-----------|-------------|-------------|
| `AuthenticationException` | Credenciales inválidas o expiradas | 401 |
| `ValidationException` | Datos de entrada inválidos | 400 |
| `TimbradoException` | Error durante el timbrado | 422 |
| `CancelacionException` | Error durante la cancelación | 422 |
| `NotFoundException` | Recurso no encontrado | 404 |
| `RateLimitException` | Límite de peticiones excedido | 429 |
| `ServerException` | Error interno del servidor | 5xx |
| `ClientException` | Error genérico del cliente | 4xx |

### Ejemplo de Manejo de Errores

```php
use TecnoFact\Sdk\Exceptions\*;

try {
    $response = $client->cfdi()->timbrar($cfdiRequest);
} catch (ValidationException $e) {
    // Error de validación - mostrar campos con error
    $errors = $e->getErrors();
    foreach ($errors as $field => $message) {
        echo "{$field}: {$message}\n";
    }
} catch (AuthenticationException $e) {
    // Re-autenticar o verificar credenciales
    echo "Error de autenticación: {$e->getMessage()}\n";
} catch (RateLimitException $e) {
    // Esperar antes de reintentar
    echo "Límite excedido. Reintentar en {$e->getRetryAfter()} segundos\n";
    sleep($e->getRetryAfter());
} catch (ServerException $e) {
    // Error del servidor - reintentar con backoff exponencial
    echo "Error del servidor: {$e->getMessage()}\n";
} catch (TecnoFactException $e) {
    // Error genérico del SDK
    echo "Error: {$e->getMessage()}\n";
    echo "Request ID: {$e->getRequestId()}\n";
}
```

---

## 📚 Ejemplos Completos

### Ejemplo 1: Factura con IVA Trasladado

```php
<?php

use TecnoFact\Sdk\Client;
use TecnoFact\Sdk\Config;
use TecnoFact\Sdk\Models\*;

$config = new Config(
    apiKey: $_ENV['TECN_FACT_API_KEY'],
    apiSecret: $_ENV['TECN_FACT_API_SECRET'],
    environment: Config::ENVIRONMENT_PRODUCTION
);

$client = new Client($config);

// Emisor
$emisor = new Emisor(
    rfc: 'XAXX010101000',
    nombre: 'EMPRESA EMISORA SA DE CV',
    regimenFiscal: '601',
    cp: '06300'
);

// Receptor
$receptor = new Receptor(
    rfc: 'XAXX010101001',
    nombre: 'CLIENTE RECEPTOR',
    usoCfdi: 'G03',
    domicilioFiscalReceptor: '06300',
    regimenFiscalReceptor: '612'
);

// Conceptos con impuestos
$concepto = new Concepto(
    claveProdServ: '01010101',
    cantidad: 2,
    claveUnidad: 'E48',
    descripcion: 'Servicio de consultoría',
    valorUnitario: 5000.00,
    importe: 10000.00,
    objetoImp: '02',
    impuestos: new ImpuestosConcepto(
        traslados: [
            new Traslado(
                base: 10000.00,
                impuesto: '002', // IVA
                tipoFactor: 'Tasa',
                tasaOCuota: '0.160000',
                importe: 1600.00
            )
        ]
    )
);

// CFDI Request
$cfdi = new Cfdi4Request(
    emisor: $emisor,
    receptor: $receptor,
    conceptos: [$concepto],
    formaPago: '03', // Transferencia electrónica
    metodoPago: 'PUE',
    tipoComprobante: 'I',
    serie: 'A',
    folio: '1234',
    lugarExpedicion: '06300',
    subTotal: 10000.00,
    total: 11600.00,
    moneda: 'MXN',
    fecha: new DateTime(),
    impuestos: new Impuestos(
        totalImpuestosTrasladados: 1600.00,
        traslados: [
            new TrasladoGlobal(
                impuesto: '002',
                tipoFactor: 'Tasa',
                tasaOCuota: '0.160000',
                importe: 1600.00
            )
        ]
    )
);

try {
    $response = $client->cfdi()->timbrar($cfdi);
    echo "✅ Timbrado: {$response->getUuid()}\n";
    file_put_contents('factura.xml', $response->getXml());
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}
```

### Ejemplo 2: Cancelación de CFDI

```php
<?php

use TecnoFact\Sdk\Client;
use TecnoFact\Sdk\Config;
use TecnoFact\Sdk\Models\CancelacionRequest;

$config = new Config(/* ... */);
$client = new Client($config);

// Verificar si es cancelable
$estado = $client->cancelacion()->consultarEstado(
    uuid: '12345678-1234-1234-1234-123456789012',
    rfcEmisor: 'XAXX010101000',
    rfcReceptor: 'XAXX010101001',
    total: 11600.00
);

if ($estado->isCancelable()) {
    $cancelRequest = new CancelacionRequest(
        uuid: '12345678-1234-1234-1234-123456789012',
        rfcEmisor: 'XAXX010101000',
        motivo: '02', // 02: Comprobante emitido con errores sin relación
        cer: file_get_contents('certificado.cer'),
        key: file_get_contents('llave.key'),
        password: 'contraseña'
    );
    
    $response = $client->cancelacion()->cancelar($cancelRequest);
    
    if ($response->isSuccess()) {
        echo "✅ Cancelado exitosamente\n";
        file_put_contents('acuse.xml', $response->getAcuseCancelacion());
    }
} else {
    echo "No es cancelable: {$estado->getMotivo()}\n";
}
```

### Ejemplo 3: Consulta de CFDIs Mensual

```php
<?php

use TecnoFact\Sdk\Client;
use TecnoFact\Sdk\Config;
use TecnoFact\Sdk\Models\FiltroBusqueda;

$config = new Config(/* ... */);
$client = new Client($config);

$filtro = new FiltroBusqueda(
    fechaInicio: new DateTime('2024-01-01'),
    fechaFin: new DateTime('2024-01-31'),
    rfcEmisor: 'XAXX010101000',
    estatus: 'vigente'
);

// Paginación
$page = 1;
$limit = 50;

do {
    $resultados = $client->consultas()->buscar($filtro, $page, $limit);
    
    foreach ($resultados->getCfdis() as $cfdi) {
        echo "{$cfdi->getUuid()} - {$cfdi->getFolio()} - {$cfdi->getTotal()}\n";
    }
    
    $page++;
} while ($resultados->hasMore());
```

---

## 🧪 Testing

### Ejecutar Tests

```bash
# Instalar dependencias de desarrollo
composer install --dev

# Ejecutar tests
vendor/bin/phpunit

# Ejecutar tests con cobertura
vendor/bin/phpunit --coverage-html coverage
```

### Tests con Sandbox

Para probar el SDK sin afectar producción:

```php
$config = new Config(
    apiKey: 'tu-api-key-sandbox',
    apiSecret: 'tu-api-secret-sandbox',
    environment: Config::ENVIRONMENT_SANDBOX
);
```

---

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor, sigue estos pasos:

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agrega nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

### Reportar Issues

Si encuentras un bug o tienes una sugerencia, por favor abre un issue en GitHub:

[https://github.com/TecnoFact/SDK-Tecnofact-php/issues](https://github.com/TecnoFact/SDK-Tecnofact-php/issues)

---

## 💬 Soporte

- 📧 Email: soporte@tecnofact.com
- 🌐 Website: [https://www.tecnofact.com](https://www.tecnofact.com)
- 📖 Documentación API: [https://docs.tecnofact.com](https://docs.tecnofact.com)
- 💬 Chat: [https://t.me/TecnoFact](https://t.me/TecnoFact)

---

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

---

## 🏢 Sobre TecnoFact

TecnoFact es un proveedor certificado de servicios de facturación electrónica en México, cumpliendo con todos los requisitos del SAT para la emisión de CFDI 4.0.

### Características del Servicio

- ✅ Timbrado masivo de CFDI
- ✅ Cancelación ante el SAT
- ✅ Consulta en tiempo real
- ✅ Reportes y analytics
- ✅ Soporte técnico especializado
- ✅ Alta disponibilidad (99.9% SLA)
- ✅ API RESTful con documentación completa

---

<p align="center">
  <strong>Desarrollado con ❤️ por el equipo de TecnoFact</strong>
</p>

<p align="center">
  <a href="https://www.tecnofact.com">Website</a> •
  <a href="https://docs.tecnofact.com">Documentación</a> •
  <a href="https://github.com/TecnoFact">GitHub</a>
</p>