<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pagos
 *
 * @ORM\Table(name="pagos", indexes={@ORM\Index(name="id_metodo_pago", columns={"id_metodo_pago"}), @ORM\Index(name="id_transaccion", columns={"id_transaccion"}), @ORM\Index(name="id_factura", columns={"id_factura"}), @ORM\Index(name="id_tipo_pago", columns={"id_tipo_pago"})})
 * @ORM\Entity
 */
class Pagos
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_pago", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPago;

    /**
     * @var float
     *
     * @ORM\Column(name="vlr_pago", type="float", precision=10, scale=0, nullable=false)
     */
    private $vlrPago;

    /**
     * @var float
     *
     * @ORM\Column(name="saldo", type="float", precision=10, scale=0, nullable=true)
     */
    private $saldo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_hora_pago", type="datetime", nullable=false)
     */
    private $fechaHoraPago;

    /**
     * @var string
     *
     * @ORM\Column(name="banco", type="string", length=255, nullable=true)
     */
    private $banco;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_consignacion", type="datetime", nullable=true)
     */
    private $fechaConsignacion;

    /**
     * @var string
     *
     * @ORM\Column(name="nro_consignacion", type="string", length=255, nullable=true)
     */
    private $nroConsignacion;

    /**
     * @var string
     *
     * @ORM\Column(name="nro_cheque", type="string", length=255, nullable=true)
     */
    private $nroCheque;

    /**
     * @var string
     *
     * @ORM\Column(name="observaciones", type="string", length=255, nullable=true)
     */
    private $observaciones;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_deleted", type="integer", length=1, nullable=true)
     */
    private $isDeleted = 0;

    /**
     * @var \MetodosPago
     *
     * @ORM\ManyToOne(targetEntity="MetodosPago")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_metodo_pago", referencedColumnName="id_metodo_pago")
     * })
     */
    private $idMetodoPago;

    /**
     * @var \Transacciones
     *
     * @ORM\ManyToOne(targetEntity="Transacciones")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_transaccion", referencedColumnName="id_transaccion")
     * })
     */
    private $idTransaccion;

    /**
     * @var \Facturas
     *
     * @ORM\ManyToOne(targetEntity="Facturas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_factura", referencedColumnName="id_factura")
     * })
     */
    private $idFactura;

    /**
     * @var \TipoPagos
     *
     * @ORM\ManyToOne(targetEntity="TipoPagos")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_tipo_pago", referencedColumnName="id_tipo_pago")
     * })
     */
    private $idTipoPago;



    /**
     * Get idPago
     *
     * @return integer
     */
    public function getIdPago()
    {
        return $this->idPago;
    }

    /**
     * Set vlrPago
     *
     * @param float $vlrPago
     *
     * @return Pagos
     */
    public function setVlrPago($vlrPago)
    {
        $this->vlrPago = $vlrPago;

        return $this;
    }

    /**
     * Get vlrPago
     *
     * @return float
     */
    public function getVlrPago()
    {
        return $this->vlrPago;
    }

    /**
     * Set saldo
     *
     * @param float $saldo
     *
     * @return Pagos
     */
    public function setSaldo($saldo)
    {
        $this->saldo = $saldo;

        return $this;
    }

    /**
     * Get saldo
     *
     * @return float
     */
    public function getSaldo()
    {
        return $this->saldo;
    }

    /**
     * Set fechaHoraPago
     *
     * @param \DateTime $fechaHoraPago
     *
     * @return Pagos
     */
    public function setFechaHoraPago($fechaHoraPago)
    {
        $this->fechaHoraPago = $fechaHoraPago;

        return $this;
    }

    /**
     * Get fechaHoraPago
     *
     * @return \DateTime
     */
    public function getFechaHoraPago()
    {
        return $this->fechaHoraPago;
    }

    /**
     * Set banco
     *
     * @param string $banco
     *
     * @return Pagos
     */
    public function setBanco($banco)
    {
        $this->banco = $banco;

        return $this;
    }

    /**
     * Get banco
     *
     * @return string
     */
    public function getBanco()
    {
        return $this->banco;
    }

    /**
     * Set fechaConsignacion
     *
     * @param \DateTime $fechaConsignacion
     *
     * @return Pagos
     */
    public function setFechaConsignacion($fechaConsignacion)
    {
        $this->fechaConsignacion = $fechaConsignacion;

        return $this;
    }

    /**
     * Get fechaConsignacion
     *
     * @return \DateTime
     */
    public function getFechaConsignacion()
    {
        return $this->fechaConsignacion;
    }

    /**
     * Set nroConsignacion
     *
     * @param string $nroConsignacion
     *
     * @return Pagos
     */
    public function setNroConsignacion($nroConsignacion)
    {
        $this->nroConsignacion = $nroConsignacion;

        return $this;
    }

    /**
     * Get nroConsignacion
     *
     * @return string
     */
    public function getNroConsignacion()
    {
        return $this->nroConsignacion;
    }

    /**
     * Set nroCheque
     *
     * @param string $nroCheque
     *
     * @return Pagos
     */
    public function setNroCheque($nroCheque)
    {
        $this->nroCheque = $nroCheque;

        return $this;
    }

    /**
     * Get nroCheque
     *
     * @return string
     */
    public function getNroCheque()
    {
        return $this->nroCheque;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     *
     * @return Pagos
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set $isDeleted
     *
     * @param string $isDeleted
     *
     * @return Pagos
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return integer
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set idMetodoPago
     *
     * @param \App\Entity\MetodosPago $idMetodoPago
     *
     * @return Pagos
     */
    public function setIdMetodoPago(\App\Entity\MetodosPago $idMetodoPago = null)
    {
        $this->idMetodoPago = $idMetodoPago;

        return $this;
    }

    /**
     * Get idMetodoPago
     *
     * @return \App\Entity\MetodosPago
     */
    public function getIdMetodoPago()
    {
        return $this->idMetodoPago;
    }

    /**
     * Set idTransaccion
     *
     * @param \App\Entity\Transacciones $idTransaccion
     *
     * @return Pagos
     */
    public function setIdTransaccion(\App\Entity\Transacciones $idTransaccion = null)
    {
        $this->idTransaccion = $idTransaccion;

        return $this;
    }

    /**
     * Get idTransaccion
     *
     * @return \App\Entity\Transacciones
     */
    public function getIdTransaccion()
    {
        return $this->idTransaccion;
    }

    /**
     * Set idFactura
     *
     * @param \App\Entity\Facturas $idFactura
     *
     * @return Pagos
     */
    public function setIdFactura(\App\Entity\Facturas $idFactura = null)
    {
        $this->idFactura = $idFactura;

        return $this;
    }

    /**
     * Get idFactura
     *
     * @return \App\Entity\Facturas
     */
    public function getIdFactura()
    {
        return $this->idFactura;
    }

    /**
     * Set idTipoPago
     *
     * @param \App\Entity\TipoPagos $idTipoPago
     *
     * @return Pagos
     */
    public function setIdTipoPago(\App\Entity\TipoPagos $idTipoPago = null)
    {
        $this->idTipoPago = $idTipoPago;

        return $this;
    }

    /**
     * Get idTipoPago
     *
     * @return \App\Entity\TipoPagos
     */
    public function getIdTipoPago()
    {
        return $this->idTipoPago;
    }

    /**
    *
    **/
    public function getArrayData()
    {
      return [
        $this->idPago,
        $this->vlrPago,
        $this->saldo,
        $this->fechaHoraPago->format("Y-m-d H:i:s"),
        $this->banco,
        $this->nroConsignacion,
        $this->nroCheque,
        $this->observaciones,
        $this->isDeleted,
        $this->idFactura,
        $this->idTransaccion,
        $this->idMetodoPago,
        $this->idTipoPago
      ];
    }
}
