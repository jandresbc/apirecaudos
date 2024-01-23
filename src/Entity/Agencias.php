<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Agencias
 *
 * @ORM\Table(name="agencias")
 * @ORM\Entity
 */
class Agencias
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_agencia", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idAgencia;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre_agencia", type="string", length=255, nullable=false)
     */
    private $nombreAgencia;

    /**
     * @var string
     *
     * @ORM\Column(name="nit_agencia", type="string", nullable=false)
     */
    private $nitAgencia;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion", type="string", length=255, nullable=false)
     */
    private $direccion;

    /**
     * @var string
     *
     * @ORM\Column(name="tel_cel", type="string", length=255, nullable=true)
     */
    private $telCel;

    /**
     * @var integer
     *
     * @ORM\Column(name="inactiva", type="integer", nullable=true)
     */
    private $inactiva;


    private $agenciasEmpresas;

    /**
     * Get agenciasEmpresas
     *
     * @return \App\Entity\AgenciasEmpresas
     */
    public function getAgenciasEmpresas()
    {
        return $this->agenciasEmpresas;
    }

    /**
     * Set agenciasEmpresas
     *
     * @param string $agenciasEmpresas
     *
     * @return AgenciasEmpresas
     */
    public function setAgenciasEmpresas($agenciasEmpresas)
    {
        $this->agenciasEmpresas = $agenciasEmpresas;
    }

    /**
     * Get idAgencia
     *
     * @return integer
     */
    public function getIdAgencia()
    {
        return $this->idAgencia;
    }

    /**
     * Set nombreAgencia
     *
     * @param string $nombreAgencia
     *
     * @return Agencias
     */
    public function setNombreAgencia($nombreAgencia)
    {
        $this->nombreAgencia = $nombreAgencia;

        return $this;
    }

    /**
     * Get nombreAgencia
     *
     * @return string
     */
    public function getNombreAgencia()
    {
        return $this->nombreAgencia;
    }

    /**
     * Set nitAgencia
     *
     * @param integer $nitAgencia
     *
     * @return Agencias
     */
    public function setNitAgencia($nitAgencia)
    {
        $this->nitAgencia = $nitAgencia;

        return $this;
    }

    /**
     * Get nitAgencia
     *
     * @return integer
     */
    public function getNitAgencia()
    {
        return $this->nitAgencia;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     *
     * @return Agencias
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * Get direccion
     *
     * @return string
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set telCel
     *
     * @param string $telCel
     *
     * @return Agencias
     */
    public function setTelCel($telCel)
    {
        $this->telCel = $telCel;

        return $this;
    }

    /**
     * Get telCel
     *
     * @return string
     */
    public function getTelCel()
    {
        return $this->telCel;
    }

    /**
     * Set inactiva
     *
     * @param integer $inactiva
     *
     * @return Agencias
     */
    public function setInactiva($inactiva)
    {
        $this->inactiva = $inactiva;

        return $this;
    }

    /**
     * Get inactiva
     *
     * @return integer
     */
    public function getInactiva()
    {
        return $this->inactiva;
    }

    /**
    * __toString()
    **/
    public function __toString()
    {
      return $this->nombreAgencia;
    }

    /**
    *
    **/
    public function getArrayData()
    {
      return [
        $this->idAgencia,
        $this->nombreAgencia,
        $this->nitAgencia,
        $this->direccion,
        $this->telCel
      ];
    }
}
