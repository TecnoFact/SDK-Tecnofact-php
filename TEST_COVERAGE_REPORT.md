# Reporte de Tests - TecnoFact SDK PHP

**Fecha**: 2026-08-04
**Versión**: 1.0.0
**Estado**: ✅ 69 tests pasando
**Generado a partir de**: ejecución real en Docker (`docker compose run --rm sdk vendor/bin/phpunit`)

---

## 📊 Resumen Ejecutivo

La suite se ejecutó dentro del contenedor Docker del SDK sobre **PHP 8.3.33** con **PHPUnit 10.5.63**.

```
Tests: 69, Assertions: 170  →  OK
```

> ⚠️ **Cobertura no medida**: la imagen Docker no incluye un driver de cobertura (Xdebug ni PCOV), por lo que PHPUnit emite `No code coverage driver available` y **no** se pueden calcular porcentajes de cobertura reales. Los conteos de este reporte son de **tests ejecutados**, no de líneas cubiertas.

---

## 🧪 Tests Ejecutados por Clase

Solo se ejecuta la suite **Unit** (ver [Configuración de Suites](#-configuración-de-suites)).

| Categoría | Archivo | Tests |
|-----------|---------|-------|
| **HTTP Client** | `tests/Unit/HttpClientTest.php` | 10 |
| **Config** | `tests/Unit/ConfigTest.php` | 9 |
| **Enums** | `tests/Unit/Enums/EnvironmentTest.php` | 7 |
| **Exceptions** | `tests/Unit/Exceptions/ExceptionsTest.php` | 13 |
| **Services** | `tests/Unit/Services/AuthServiceTest.php` | 8 |
| **Models** | `tests/Unit/Models/EmisorTest.php` | 8 |
| **Models** | `tests/Unit/Models/ReceptorTest.php` | 7 |
| **Models** | `tests/Unit/Models/ConceptoTest.php` | 7 |
| **Total** | | **69** |

---

## 🎯 Detalle por Componente

### HTTP Client (`HttpClientTest.php`) — 10 tests

1. ✅ GET request exitoso
2. ✅ POST request exitoso
3. ✅ PUT request exitoso
4. ✅ DELETE request exitoso
5. ✅ Respuesta 401 lanza `AuthenticationException`
6. ✅ Respuesta 404 lanza `NotFoundException`
7. ✅ Respuesta 422 lanza `ValidationException`
8. ✅ Respuesta 429 lanza `RateLimitException`
9. ✅ Respuesta 500 lanza `ServerException`
10. ✅ JSON inválido lanza excepción

### Config (`ConfigTest.php`) — 9 tests

1. ✅ Constructor con valores por defecto
2. ✅ Constructor con entorno production
3. ✅ Constructor con timeout y retries personalizados
4. ✅ Email vacío lanza excepción
5. ✅ Formato de email inválido lanza excepción
6. ✅ Password vacío lanza excepción
7. ✅ Timeout inválido lanza excepción
8. ✅ Retries inválidos lanza excepción
9. ✅ `toArray()` retorna el formato correcto

### Enums - Environment (`EnvironmentTest.php`) — 7 tests

1. ✅ Entorno PRODUCTION
2. ✅ `from()` con valor de cadena
3. ✅ `tryFrom()` con valor válido
4. ✅ `tryFrom()` con valor inválido
5. ✅ `isProduction()`
6. ✅ `label()`
7. ✅ Conteo de casos

> Nota: el entorno **SANDBOX** ya no existe en el SDK (está comentado en `Environment.php`); solo `PRODUCTION` está disponible.

### Exceptions (`ExceptionsTest.php`) — 13 tests

1. ✅ `TecnoFactException` es la excepción base
2. ✅ `AuthenticationException` extiende la base
3. ✅ `ValidationException` extiende la base
4. ✅ `NotFoundException` extiende la base
5. ✅ `RateLimitException` extiende la base
6. ✅ `ServerException` extiende la base
7. ✅ `TimbradoException` extiende la base
8. ✅ `CancelacionException` extiende la base
9. ✅ Excepción con código
10. ✅ Excepción con excepción previa
11. ✅ `AuthenticationException` se puede capturar
12. ✅ `ValidationException` se puede capturar
13. ✅ `ServerException` se puede capturar

### Services - AuthService (`AuthServiceTest.php`) — 8 tests

1. ✅ Login exitoso
2. ✅ Login fallido lanza excepción
3. ✅ Refresh token exitoso
4. ✅ Refresh token fallido lanza excepción
5. ✅ Logout exitoso
6. ✅ Logout fallido lanza excepción
7. ✅ Login sin `access_token` en la respuesta
8. ✅ Refresh sin `access_token` en la respuesta

### Models - Emisor (`EmisorTest.php`) — 8 tests

1. ✅ Constructor con campos requeridos
2. ✅ Constructor con campos opcionales
3. ✅ `toArray()` con campos requeridos
4. ✅ `toArray()` con campos opcionales
5. ✅ RFC persona moral
6. ✅ RFC persona física
7. ✅ Formato de código postal
8. ✅ Formato de régimen fiscal

### Models - Receptor (`ReceptorTest.php`) — 7 tests

1. ✅ Constructor con campos requeridos
2. ✅ Constructor con campos opcionales
3. ✅ `toArray()` con campos requeridos
4. ✅ `toArray()` con campos opcionales
5. ✅ Uso CFDI - Gastos Generales (G03)
6. ✅ Uso CFDI - Adquisición de Mercancías (G01)
7. ✅ Receptor extranjero

### Models - Concepto (`ConceptoTest.php`) — 7 tests

1. ✅ Constructor con campos requeridos
2. ✅ Constructor con campos opcionales
3. ✅ `toArray()` con campos requeridos
4. ✅ `toArray()` con campos opcionales
5. ✅ Cantidad decimal
6. ✅ Cálculo de importe
7. ✅ Valores de objeto de impuesto

---

## ⚙️ Configuración de Suites

`phpunit.xml` define dos suites:

```xml
<testsuites>
    <testsuite name="Unit">
        <directory suffix="Test.php">./tests/Unit</directory>
    </testsuite>
    <testsuite name="Integration">
        <directory suffix="Test.php">./tests/Integration</directory>
    </testsuite>
</testsuites>
```

Estado actual de cada suite:

- **Unit**: ✅ 69 tests, todos pasando.
- **Integration**: ⚪ vacía (no hay archivos `*Test.php` en `tests/Integration`).

> ⚠️ **Hallazgo**: existen tests en `tests/Security` (`CredentialSecurityTest.php`, `HttpSecurityTest.php`) que **no** están incluidos en ninguna suite de `phpunit.xml`, por lo que **no se ejecutan**. Para incorporarlos hay que agregar una suite `Security` apuntando a `./tests/Security`.

---

## 🐳 Ejecución con Docker

```bash
# Construir imagen
docker compose build sdk

# Ejecutar todos los tests
docker compose run --rm sdk vendor/bin/phpunit

# Desglose por clase (testdox)
docker compose run --rm sdk vendor/bin/phpunit --testdox

# Análisis estático
docker compose run --rm sdk vendor/bin/phpstan analyse
```

También disponible vía `Makefile` (`make docker-test`, `make docker-analyze`, `make docker-lint`, `make docker-ci`) y el script `run-tests-docker.sh`.

---

## 🔍 Áreas Pendientes de Testing

### Servicios sin tests (Prioridad Media)

- [ ] `CfdiService` — timbrado de CFDI
- [ ] `CancelacionService` — cancelación
- [ ] `ConsultasService` — consultas
- [ ] `ReportesService` — reportes
- [ ] `ValidacionesService` — validaciones

### Modelos sin tests (Prioridad Baja)

- [ ] `Cfdi4Request`
- [ ] `Impuestos` / `ImpuestosConcepto`
- [ ] `Traslado` / `TrasladoGlobal` / `Retencion` / `RetencionGlobal`
- [ ] `CuentaPredial`
- [ ] `InformacionAduanera`
- [ ] `Parte`
- [ ] `CfdiRelacionados`

### Infraestructura de testing

- [ ] Incorporar la suite `Security` a `phpunit.xml` (hoy los tests existen pero no corren).
- [ ] Instalar un driver de cobertura (Xdebug o PCOV) en la imagen Docker para poder medir cobertura real.
- [ ] Poblar la suite `Integration` con tests de flujo (login → timbrado → consulta) contra un entorno real.

---

## 📝 Notas Técnicas

### Mocking

`AuthServiceTest` y `HttpClientTest` usan mocking (PHPUnit / Mockery) para simular respuestas HTTP sin llamadas reales a la API.

### Warnings de análisis estático

Los warnings de Intelephense sobre `expects()` son falsos positivos: el método existe en PHPUnit cuando se usan mocks. `phpstan.neon` ya los ignora explícitamente.

---

## ✅ Conclusión

La suite **Unit** del SDK está verde: **69 tests / 170 assertions** sobre PHP 8.3.33 y PHPUnit 10.5.63. Las áreas de mejora concretas son: cablear la suite `Security`, agregar un driver de cobertura para medir cobertura real, y crear tests para los servicios y modelos que hoy no tienen.

---

**Proyecto**: TecnoFact SDK PHP
