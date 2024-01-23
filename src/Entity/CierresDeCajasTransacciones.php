<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CierresDeCajasTransacciones
 *
 * @ORM\Table(name="cierres_de_cajas_transacciones", indexes={@ORM\Index(name="id_transaccion", columns={"id_transaccion"}), @ORM\Index(name="id_cierre_de_caja", columns={"id_cierre_de_caja"})})
 * @ORM\Entity
 */
class CierresDeCajasTransacciones
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_cdc_transacciones", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCdcTransacciones;

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
     * @var \CierresDeCajas
     *
     * @ORM\ManyToOne(targetEntity="CierresDeCajas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_cierre_de_caja", referencedColumnName="id_cierre_caja")
     * })
     */
    private $idCierreDeCaja;



    /**
     * Get idCdcTransacciones
     *
     * @return integer
     */
    public function getIdCdcTransacciones()
    {
        return $this->idCdcTransacciones;
    }

    /**
     * Set idTransaccion
     *
     * @param \App\Entity\Transacciones $idTransaccion
     *
     * @return CierresDeCajasTransacciones
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
     * Set idCierreDeCaja
     *
     * @param \App\Entity\CierresDeCajas $idCierreDeCaja
     *
     * @return CierresDeCajasTransacciones
     */
    public function setIdCierreDeCaja(\App\Entity\CierresDeCajas $idCierreDeCaja = null)
    {
        $this->idCierreDeCaja = $idCierreDeCaja;

        return $this;
    }

    /**
     * Get idCierreDeCaja
     *
     * @return \App\Entity\CierresDeCajas
     */
    public function getIdCierreDeCaja()
    {
        return $this->idCierreDeCaja;
    }

    /**
    *
    **/
    public function getArrayData()
    {
      return [
        $this->idCdcTransacciones,
        $this->idTransaccion,
        $this->idCierreDeCaja
      ];
    }
}
