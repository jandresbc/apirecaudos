<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CierresDeCajas
 *
 * @ORM\Table(name="cierres_de_cajas", indexes={@ORM\Index(name="id_caja", columns={"id_caja"})})
 * @ORM\Entity
 */
class CierresDeCajas
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_cierre_caja", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCierreCaja;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_hora_cierre", type="datetime", nullable=false)
     */
    private $fechaHoraCierre;

    /**
     * @var float
     *
     * @ORM\Column(name="total_recaudo_caja", type="float", precision=10, scale=0, nullable=true)
     */
    private $totalRecaudoCaja;

    /**
     * @var float
     *
     * @ORM\Column(name="vlr_en_caja", type="float", precision=10, scale=0, nullable=true)
     */
    private $vlrEnCaja;

    /**
     * @var float
     *
     * @ORM\Column(name="diferencia_cierre", type="float", precision=10, scale=0, nullable=true)
     */
    private $diferenciaCierre;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_colillas", type="integer", nullable=true)
     */
    private $totalColillas;

    /**
     * @var string
     *
     * @ORM\Column(name="nro_documento", type="string", nullable=true)
     */
    private $nroDocumento;

    /**
     * @var \Cajas
     *
     * @ORM\ManyToOne(targetEntity="Cajas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_caja", referencedColumnName="id_caja")
     * })
     */
    private $idCaja;



    /**
     * Get idCierreCaja
     *
     * @return integer
     */
    public function getIdCierreCaja()
    {
        return $this->idCierreCaja;
    }

    /**
     * Set fechaHoraCierre
     *
     * @param \DateTime $fechaHoraCierre
     *
     * @return CierresDeCajas
     */
    public function setFechaHoraCierre($fechaHoraCierre)
    {
        $this->fechaHoraCierre = $fechaHoraCierre;

        return $this;
    }

    /**
     * Get fechaHoraCierre
     *
     * @return \DateTime
     */
    public function getFechaHoraCierre()
    {
        return $this->fechaHoraCierre;
    }

    /**
     * Set totalRecaudoCaja
     *
     * @param float $totalRecaudoCaja
     *
     * @return CierresDeCajas
     */
    public function setTotalRecaudoCaja($totalRecaudoCaja)
    {
        $this->totalRecaudoCaja = $totalRecaudoCaja;

        return $this;
    }

    /**
     * Get totalRecaudoCaja
     *
     * @return float
     */
    public function getTotalRecaudoCaja()
    {
        return $this->totalRecaudoCaja;
    }

    /**
     * Set vlrEnCaja
     *
     * @param float $vlrEnCaja
     *
     * @return CierresDeCajas
     */
    public function setVlrEnCaja($vlrEnCaja)
    {
        $this->vlrEnCaja = $vlrEnCaja;

        return $this;
    }

    /**
     * Get vlrEnCaja
     *
     * @return float
     */
    public function getVlrEnCaja()
    {
        return $this->vlrEnCaja;
    }

    /**
     * Set diferenciaCierre
     *
     * @param float $diferenciaCierre
     *
     * @return CierresDeCajas
     */
    public function setDiferenciaCierre($diferenciaCierre)
    {
        $this->diferenciaCierre = $diferenciaCierre;

        return $this;
    }

    /**
     * Get diferenciaCierre
     *
     * @return float
     */
    public function getDiferenciaCierre()
    {
        return $this->diferenciaCierre;
    }

    /**
     * Set totalColillas
     *
     * @param integer $totalColillas
     *
     * @return CierresDeCajas
     */
    public function setTotalColillas($totalColillas)
    {
        $this->totalColillas = $totalColillas;

        return $this;
    }

    /**
     * Get totalColillas
     *
     * @return integer
     */
    public function getTotalColillas()
    {
        return $this->totalColillas;
    }

    /**
     * Set nroDocumento
     *
     * @param string $nroDocumento
     *
     * @return CierresDeCajas
     */
    public function setNroDocumento($nroDocumento)
    {
        $this->nroDocumento = $nroDocumento;

        return $this;
    }

    /**
     * Get nroDocumento
     *
     * @return string
     */
    public function getNroDocumento()
    {
        return $this->nroDocumento;
    }

    /**
     * Set idCaja
     *
     * @param \App\Entity\Cajas $idCaja
     *
     * @return CierresDeCajas
     */
    public function setIdCaja(\App\Entity\Cajas $idCaja = null)
    {
        $this->idCaja = $idCaja;

        return $this;
    }

    /**
     * Get idCaja
     *
     * @return \App\Entity\Cajas
     */
    public function getIdCaja()
    {
        return $this->idCaja;
    }

    /**
    * __toString()
    **/
    public function __toString()
    {
      return $this->fechaHoraCierre->format("Y-m-d H:i:s")." | ".$this->nroDocumento." | ".$this->vlrEnCaja;
    }

    /**
    *
    **/
    public function getArrayData()
    {
      return [
        $this->idCierreCaja,
        $this->fechaHoraCierre->format("Y-m-d H:i:s"),
        $this->totalRecaudoCaja,
        $this->vlrEnCaja,
        $this->diferenciaCierre,
        $this->totalColillas,
        $this->nroDocumento,
        $this->idCaja
      ];
    }
}
