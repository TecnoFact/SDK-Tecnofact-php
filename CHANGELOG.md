# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

---

## [1.1.0] - 2026-08-04

### Added

#### Complemento de Pagos 2.0 (TipoDeComprobante = "P")
- `PagoRequest` model — simplified entry point; the SDK auto-generates `Moneda=XXX`, `SubTotal=0`, `Total=0`, the fixed `Concepto` (84111506/ACT/Pago/0/0/01), and `pago20:Totales.MontoTotalPagos`.
- `Pago` model — represents a single `pago20:Pago` node (FechaPago, FormaDePagoP, MonedaP, TipoCambioP, Monto).
- `DoctoRelacionado` model — represents a `pago20:DoctoRelacionado` (IdDocumento, MonedaDR, EquivalenciaDR, NumParcialidad, ImpSaldoAnt, ImpPagado, ImpSaldoInsoluto, ObjetoImpDR, Serie?, Folio?).
- `CfdiXmlBuilder::buildPago(PagoRequest)` — builds the full Pagos 2.0 XML with correct `xmlns:pago20` declaration on the root `Comprobante`, `schemaLocation` extended with Pagos20.xsd, and `cfdi:Complemento > pago20:Pagos`.
- `CfdiService::timbrarPago(PagoRequest): ResultadoTimbrado` — new method for stamping payment receipts.

#### CFDI XML Builder v1 — concept-level nodes
- `CfdiXmlBuilder` now emits `cfdi:InformacionAduanera` (NumeroPedimento), `cfdi:CuentaPredial` (Numero), and `cfdi:Parte` within `cfdi:Concepto`, respecting the XSD order (Impuestos → InformacionAduanera → CuentaPredial → Parte).
- `Concepto` model: added optional `descuento` field.

#### CFDI XML Builder (core)
- `CfdiXmlBuilder::build(Cfdi4Request)` — builds CFDI 4.0 XML for types `I` (Ingreso) and `E` (Egreso) using `DOMDocument`; enforces XSD element order, per-field decimal formatting (TasaOCuota 6 decimals, importes 2), and conditional rules per TipoDeComprobante.
- `InformacionGlobal` model — `Periodicidad`, `Meses`, `Año` for global invoices (público en general).
- `CfdiService::timbrar(Cfdi4Request): ResultadoTimbrado` — now builds the XML internally and sends `{"xml": "..."}` to `/api/v1/stamp-cfdi`; the panel handles sealing (CSD) and stamping. The SDK never handles the private key.

#### New API endpoints
- `CfdiService::validar(string $xml): EstatusCfdi` — POST `/api/v1/validation-cfdi` as `multipart/form-data` with field `xml`; returns typed `EstatusCfdi`.
- `CancelacionService::cancelar(string $rfc, string $uuid, string $motivo): AcuseCancelacion` — POST `/api/v1/cancelled-cfdi` with JSON `{rfc, uuid, motivo}`; returns typed `AcuseCancelacion`.

#### Typed response objects (`src/Responses/`)
- `ResultadoTimbrado` — returned by `timbrar()`, `timbrarXml()`, `timbrarPago()`; exposes `isSuccess()`, `getXmlTimbrado()`, `getUuid()`, `getCode()`, `getMessage()`, `getRaw()`.
- `EstatusCfdi` — returned by `validar()`; exposes `isVigente()`, `getEstado()`, `getCodigo()`, `getEsCancelable()`, `getEfos()`, `getRaw()`.
- `AcuseCancelacion` — returned by `cancelar()`; exposes `isAceptadaPorSat()`, `getUuid()`, `getXml()`, `getPdfBase64()`, `getPdfBinario()` (decodes base64 to raw bytes), `getRaw()`.

#### HTTP client
- `HttpClientInterface::postMultipart()` + `HttpClient::postMultipart()` — multipart/form-data POST support; correctly strips `Content-Type` so Guzzle can set the boundary.

#### TLS configuration
- `Config::$verifySsl` (`bool|string`, default `true`) — pass `true` for system CA bundle, a file path for a custom PEM bundle (e.g. when the server has an incomplete chain), or `false` to disable verification (development only).
- `TECN_FACT_VERIFY_SSL` environment variable supported by `Config::fromEnvironment()`.

#### Infrastructure
- `Dockerfile`: added `ca-certificates` + `update-ca-certificates`; HTTPS now works from within the container without manual patches.

### Changed

#### Config
- Authentication credentials changed from `apiKey`/`apiSecret` to `email`/`password` (matching the real panel API).
- Environment variables renamed: `TECN_FACT_API_KEY` → `TECN_FACT_EMAIL`, `TECN_FACT_API_SECRET` → `TECN_FACT_PASSWORD`.
- `Environment::SANDBOX` removed (only `PRODUCTION` is available); `isSandbox()` removed accordingly.
- `Cfdi4Request`: replaced `subTotalConDescuento` (invalid field) with `informacionGlobal?: InformacionGlobal`.
- `Emisor`: `cp` field kept on the model for backward compatibility; the XML builder does not emit it (it is not a valid CFDI 4.0 Emisor attribute). `facAtrAdm` maps to the correct XML attribute `FacAtrAdquirente`.
- `CancelacionService::cancelar()` signature updated to `(string $rfc, string $uuid, string $motivo)` and endpoint changed from `/cancelacion/cancelar` to `/api/v1/cancelled-cfdi`.
- `TrasladoGlobal`: added required `base` attribute (CFDI 4.0); `importe` is now nullable for Exento cases.
- `RetencionGlobal`: simplified to `(impuesto, importe)` only — the comprobante-level `Retencion` node must not include `TipoFactor` or `TasaOCuota`.

### Fixed
- `HttpClient::handleRequestException()` now reads `error` and `mensaje` fields in addition to `message`, so real error messages from the panel (e.g. SAT validation codes) are surfaced instead of the generic "Error desconocido".

### Security
- Comprehensive GitHub Actions workflows for CI/CD.
- Multi-platform testing (Ubuntu, Windows, macOS) and PHP 8.0–8.3 compatibility.
- Code quality checks with PHPStan level 9 and Psalm level 3.
- Security scanning with CodeQL, TruffleHog, and Symfony Security Checker.
- Automated dependency updates with Dependabot.
- Comprehensive security documentation (SECURITY.md).
- Security-focused unit tests (22 test cases).
- OWASP Top 10 compliance verification.
- `verifySsl` never defaults to `false`; disabling TLS verification requires an explicit opt-in.

---

## [1.0.0] - 2026-08-03

Initial release targeting `https://panelcfdi.tecnofact.mx`.

- Authentication via `email` + `password` (POST `/api/login`)
- CFDI 4.0 XML builder (`CfdiXmlBuilder`) for types `I` and `E`
- `CfdiService::timbrar()` — POST `/api/v1/stamp-cfdi` with `{"xml": "..."}`
- `CancelacionService` skeleton
- `Config`, `Environment` (PRODUCTION only), `HttpClient` (Guzzle 7)
- PHPUnit test suite — 69 tests passing

---

## Previous Releases

For releases prior to automated changelog generation, please see the [GitHub Releases](https://github.com/TecnoFact/SDK-Tecnofact-php/releases) page.
