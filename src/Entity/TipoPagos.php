<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TipoPagos
 *
 * @ORM\Table(name="tipo_pagos")
 * @ORM\Entity
 */
class TipoPagos
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_tipo_pago", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTipoPago;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo_pago", type="string", length=255, nullable=true)
     */
    private $tipoPago;



    /**
     * Get idTipoPago
     *
     * @return integer
     */
    public function getIdTipoPago()
    {
        return $this->idTipoPago;
    }

    /**
     * Set tipoPago
     *
     * @param string $tipoPago
     *
     * @return TipoPagos
     */
    public function setTipoPago($tipoPago)
    {
        $this->tipoPago = $tipoPago;

        return $this;
    }

    /**
     * Get tipoPago
     *
     * @return string
     */
    public function getTipoPago()
    {
        return $this->tipoPago;
    }

    /**
    * __toString()
    **/
    public function __toString()
    {
      return $this->tipoPago;
    }

}
