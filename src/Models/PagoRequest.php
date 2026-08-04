<?php

declare(strict_types=1);

namespace TecnoFact\Sdk\Models;

/**
 * Solicitud de timbrado de un Comprobante de Recepción de Pagos (TipoDeComprobante = "P").
 *
 * El usuario solo provee los datos de negocio: emisor, receptor, serie/folio/fecha,
 * lugar de expedición y la lista de pagos. El SDK genera automáticamente:
 * - Moneda = "XXX", SubTotal = "0", Total = "0"
 * - El Concepto fijo (ClaveProdServ 84111506, ClaveUnidad ACT, Descripcion "Pago",
 *   ValorUnitario "0", Importe "0", ObjetoImp "01")
 * - El nodo cfdi:Complemento > pago20:Pagos con Totales calculados
 */
class PagoRequest
{
    /**
     * @param Emisor $emisor Datos del emisor
     * @param Receptor $receptor Datos del receptor (UsoCFDI debe ser "CP01")
     * @param array<Pago> $pagos Lista de pagos del complemento
     * @param \DateTime $fecha Fecha de expedición del comprobante
     * @param string $lugarExpedicion Código postal del lugar de expedición
     * @param string|null $serie Serie del comprobante (opcional)
     * @param string|null $folio Folio del comprobante (opcional)
     * @param string $exportacion Clave de exportación (default "01" = No aplica)
     */
    public function __construct(
        private Emisor $emisor,
        private Receptor $receptor,
        private array $pagos,
        private \DateTime $fecha,
        private string $lugarExpedicion,
        private ?string $serie = null,
        private ?string $folio = null,
        private string $exportacion = '01'
    ) {
    }

    public function getEmisor(): Emisor
    {
        return $this->emisor;
    }

    public function getReceptor(): Receptor
    {
        return $this->receptor;
    }

    /**
     * @return array<Pago>
     */
    public function getPagos(): array
    {
        return $this->pagos;
    }

    public function getFecha(): \DateTime
    {
        return $this->fecha;
    }

    public function getLugarExpedicion(): string
    {
        return $this->lugarExpedicion;
    }

    public function getSerie(): ?string
    {
        return $this->serie;
    }

    public function getFolio(): ?string
    {
        return $this->folio;
    }

    public function getExportacion(): string
    {
        return $this->exportacion;
    }
}
