<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Facturas
 *
 * @ORM\Table(name="facturas", indexes={@ORM\Index(name="id_empresa", columns={"id_empresa"})})
 * @ORM\Entity
 */
class Facturas
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_factura", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idFactura;

    /**
     * @var string
     *
     * @ORM\Column(name="nro_factura", type="string", length=255, nullable=false)
     */
    private $nroFactura;

    /**
     * @var string
     *
     * @ORM\Column(name="matricula", type="string", length=255, nullable=false)
     */
    private $matricula;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre_usuario", type="string", length=255, nullable=false)
     */
    private $nombreUsuario;

    /**
     * @var string
     *
     * @ORM\Column(name="concepto", type="string", length=255, nullable=false)
     */
    private $concepto;

    /**
     * @var float
     *
     * @ORM\Column(name="valor_factura", type="float", precision=10, scale=0, nullable=false)
     */
    private $valorFactura;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_vencimiento", type="datetime", nullable=false)
     */
    private $fechaVencimiento;

    /**
     * @var string
     *
     * @ORM\Column(name="observaciones", type="string", length=255, nullable=true)
     */
    private $observaciones;

    /**
     * @var string
     *
     * @ORM\Column(name="mes_facturado", type="string", length=255, nullable=false)
     */
    private $mesFacturado;

    /**
     * @var string
     *
     * @ORM\Column(name="anio_facturado", type="string", length=255, nullable=false)
     */
    private $anioFacturado;

    /**
     * @var integer
     *
     * @ORM\Column(name="meses_atrasados", type="integer", length=20, nullable=true)
     */
    private $mesesAtrasados;

    /**
     * @var integer
     *
     * @ORM\Column(name="periodo_actual", type="integer", nullable=true)
     */
    private $periodoActual;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_abono", type="integer", nullable=true)
     */
    private $isAbono = 0;

    /**
     * @var \Empresas
     *
     * @ORM\ManyToOne(targetEntity="Empresas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_empresa", referencedColumnName="id_empresa")
     * })
     */
    private $idEmpresa;



    /**
     * Get idFactura
     *
     * @return integer
     */
    public function getIdFactura()
    {
        return $this->idFactura;
    }

    /**
     * Set nroFactura
     *
     * @param string $nroFactura
     *
     * @return Facturas
     */
    public function setNroFactura($nroFactura)
    {
        $this->nroFactura = $nroFactura;

        return $this;
    }

    /**
     * Get nroFactura
     *
     * @return string
     */
    public function getNroFactura()
    {
        return $this->nroFactura;
    }

    /**
     * Set matricula
     *
     * @param string $matricula
     *
     * @return Facturas
     */
    public function setMatricula($matricula)
    {
        $this->matricula = $matricula;

        return $this;
    }

    /**
     * Get matricula
     *
     * @return string
     */
    public function getMatricula()
    {
        return $this->matricula;
    }

    /**
     * Set nombreUsuario
     *
     * @param string $nombreUsuario
     *
     * @return Facturas
     */
    public function setNombreUsuario($nombreUsuario)
    {
        $this->nombreUsuario = $nombreUsuario;

        return $this;
    }

    /**
     * Get nombreUsuario
     *
     * @return string
     */
    public function getNombreUsuario()
    {
        return $this->nombreUsuario;
    }

    /**
     * Set concepto
     *
     * @param string $concepto
     *
     * @return Facturas
     */
    public function setConcepto($concepto)
    {
        $this->concepto = $concepto;

        return $this;
    }

    /**
     * Get concepto
     *
     * @return string
     */
    public function getConcepto()
    {
        return $this->concepto;
    }

    /**
     * Set valorFactura
     *
     * @param float $valorFactura
     *
     * @return Facturas
     */
    public function setValorFactura($valorFactura)
    {
        $this->valorFactura = $valorFactura;

        return $this;
    }

    /**
     * Get valorFactura
     *
     * @return float
     */
    public function getValorFactura()
    {
        return $this->valorFactura;
    }

    /**
     * Set fechaVencimiento
     *
     * @param \DateTime $fechaVencimiento
     *
     * @return Facturas
     */
    public function setFechaVencimiento($fechaVencimiento)
    {
        $this->fechaVencimiento = $fechaVencimiento;

        return $this;
    }

    /**
     * Get fechaVencimiento
     *
     * @return \DateTime
     */
    public function getFechaVencimiento()
    {
        return $this->fechaVencimiento;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     *
     * @return Facturas
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
     * Set mesFacturado
     *
     * @param string $mesFacturado
     *
     * @return Facturas
     */
    public function setMesFacturado($mesFacturado)
    {
        $this->mesFacturado = $mesFacturado;

        return $this;
    }

    /**
     * Get mesFacturado
     *
     * @return string
     */
    public function getMesFacturado()
    {
        return $this->mesFacturado;
    }

    /**
     * Set anioFacturado
     *
     * @param string $anioFacturado
     *
     * @return Facturas
     */
    public function setAnioFacturado($anioFacturado)
    {
        $this->anioFacturado = $anioFacturado;

        return $this;
    }

    /**
     * Get anioFacturado
     *
     * @return string
     */
    public function getAnioFacturado()
    {
        return $this->anioFacturado;
    }

    /**
     * Set mesesAtrasados
     *
     * @param integer $mesesAtrasados
     *
     * @return Facturas
     */
    public function setMesesAtrasados($mesesAtrasados)
    {
        $this->mesesAtrasados = $mesesAtrasados;

        return $this;
    }

    /**
     * Get mesesAtrasados
     *
     * @return integer
     */
    public function getMesesAtrasados()
    {
        return $this->mesesAtrasados;
    }

    /**
     * Set periodoActual
     *
     * @param integer $periodoActual
     *
     * @return Facturas
     */
    public function setPeriodoActual($periodoActual)
    {
        $this->periodoActual = $periodoActual;

        return $this;
    }

    /**
     * Get periodoActual
     *
     * @return integer
     */
    public function getPeriodoActual()
    {
        return $this->periodoActual;
    }

    /**
     * Set isAbono
     *
     * @param integer $isAbono
     *
     * @return Facturas
     */
    public function setIsAbono($isAbono)
    {
        $this->isAbono = $isAbono;

        return $this;
    }

    /**
     * Get isAbono
     *
     * @return integer
     */
    public function getIsAbono()
    {
        return $this->isAbono;
    }

    /**
     * Set idEmpresa
     *
     * @param \App\Entity\Empresas $idEmpresa
     *
     * @return Facturas
     */
    public function setIdEmpresa(\App\Entity\Empresas $idEmpresa = null)
    {
        $this->idEmpresa = $idEmpresa;

        return $this;
    }

    /**
     * Get idEmpresa
     *
     * @return \App\Entity\Empresas
     */
    public function getIdEmpresa()
    {
        return $this->idEmpresa;
    }

    /**
    * __toString()
    **/
    public function __toString()
    {
      return $this->nroFactura." | ".$this->matricula." | $".number_format($this->valorFactura,0,",",".");
    }

    /**
    *
    **/
    public function getArrayData()
    {
      return [
        $this->idFactura,
        $this->nroFactura,
        $this->matricula,
        $this->nombreUsuario,
        $this->concepto,
        $this->valorFactura,
        $this->fechaVencimiento->format("Y-m-d H:i:s"),
        $this->observaciones,
        $this->mesFacturado,
        $this->anioFacturado,
        $this->mesesAtrasados,
        $this->periodoActual,
        $this->isAbono,
        $this->idEmpresa
      ];
    }
}
