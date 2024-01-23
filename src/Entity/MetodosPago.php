<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MetodosPago
 *
 * @ORM\Table(name="metodos_pago")
 * @ORM\Entity
 */
class MetodosPago
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_metodo_pago", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idMetodoPago;

    /**
     * @var string
     *
     * @ORM\Column(name="metodo_pago", type="string", length=255, nullable=true)
     */
    private $metodoPago;



    /**
     * Get idMetodoPago
     *
     * @return integer
     */
    public function getIdMetodoPago()
    {
        return $this->idMetodoPago;
    }

    /**
     * Set metodoPago
     *
     * @param string $metodoPago
     *
     * @return MetodosPago
     */
    public function setMetodoPago($metodoPago)
    {
        $this->metodoPago = $metodoPago;

        return $this;
    }

    /**
     * Get metodoPago
     *
     * @return string
     */
    public function getMetodoPago()
    {
        return $this->metodoPago;
    }

    /**
    * __toString()
    **/
    public function __toString()
    {
      return $this->metodoPago;
    }
}
