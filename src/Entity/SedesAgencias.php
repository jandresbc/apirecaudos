<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SedesAgencias
 *
 * @ORM\Table(name="sedes_agencias", indexes={@ORM\Index(name="id_divipola", columns={"id_divipola"}), @ORM\Index(name="id_agencia", columns={"id_agencia"})})
 * @ORM\Entity
 */
class SedesAgencias
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_sede_agencia", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idSedeAgencia;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre_sede", type="string", length=255, nullable=false)
     */
    private $nombreSede;

    /**
     * @var integer
     *
     * @ORM\Column(name="codigo_sede", type="integer", nullable=true)
     */
    private $codigoSede;

    /**
     * @var string
     *
     * @ORM\Column(name="direccion", type="string", length=255, nullable=false)
     */
    private $direccion;

    /**
     * @var integer
     *
     * @ORM\Column(name="tel_cel", type="integer", nullable=true)
     */
    private $telCel;

    /**
     * @var integer
     *
     * @ORM\Column(name="inactiva", type="integer", nullable=true)
     */
    private $inactiva;

    /**
     * @var \Divipola
     *
     * @ORM\ManyToOne(targetEntity="Divipola")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_divipola", referencedColumnName="divipola")
     * })
     */
    private $idDivipola;

    /**
     * @var \Agencias
     *
     * @ORM\ManyToOne(targetEntity="Agencias")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_agencia", referencedColumnName="id_agencia")
     * })
     */
    private $idAgencia;

    //Empresa para la entidad - relación con la tabla empresas_sedes_agencias.
    private $empresa;

    /**
     * Get EmpresasSedesAgencias
     *
     * @return \App\Entity\EmpresasSedesAgencias
     */
    public function getEmpresa()
    {
        return $this->empresa;
    }

    /**
     * Set agenciasEmpresas
     *
     * @param string $agenciasEmpresas
     *
     * @return EmpresasSedesAgencias
     */
    public function setEmpresa($empresa)
    {
        $this->empresa = $empresa;
    }

    /**
     * Get idSedeAgencia
     *
     * @return integer
     */
    public function getIdSedeAgencia()
    {
        return $this->idSedeAgencia;
    }

    /**
     * Set nombreSede
     *
     * @param string $nombreSede
     *
     * @return SedesAgencias
     */
    public function setNombreSede($nombreSede)
    {
        $this->nombreSede = $nombreSede;

        return $this;
    }

    /**
     * Get nombreSede
     *
     * @return string
     */
    public function getNombreSede()
    {
        return $this->nombreSede;
    }

    /**
     * Set codigoSede
     *
     * @param integer $codigoSede
     *
     * @return SedesAgencias
     */
    public function setCodigoSede($codigoSede)
    {
        $this->codigoSede = $codigoSede;

        return $this;
    }

    /**
     * Get codigoSede
     *
     * @return integer
     */
    public function getCodigoSede()
    {
        return $this->codigoSede;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     *
     * @return SedesAgencias
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
     * @param integer $telCel
     *
     * @return SedesAgencias
     */
    public function setTelCel($telCel)
    {
        $this->telCel = $telCel;

        return $this;
    }

    /**
     * Get telCel
     *
     * @return integer
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
     * @return SedesAgencias
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
     * Set idDivipola
     *
     * @param \App\Entity\Divipola $idDivipola
     *
     * @return SedesAgencias
     */
    public function setIdDivipola(\App\Entity\Divipola $idDivipola = null)
    {
        $this->idDivipola = $idDivipola;

        return $this;
    }

    /**
     * Get idDivipola
     *
     * @return \App\Entity\Divipola
     */
    public function getIdDivipola()
    {
        return $this->idDivipola;
    }

    /**
     * Set idAgencia
     *
     * @param \App\Entity\Agencias $idAgencia
     *
     * @return SedesAgencias
     */
    public function setIdAgencia(\App\Entity\Agencias $idAgencia = null)
    {
        $this->idAgencia = $idAgencia;

        return $this;
    }

    /**
     * Get idAgencia
     *
     * @return \App\Entity\Agencias
     */
    public function getIdAgencia()
    {
        return $this->idAgencia;
    }

    /*
    **  __toString()
    */
    public function __toString()
    {
        return $this->nombreSede;
    }

    /**
    *
    **/
    public function getArrayData()
    {
      return [
        $this->idSedeAgencia,
        $this->nombreSede,
        $this->direccion,
        $this->telCel,
        $this->idDivipola,
        $this->idAgencia,
      ];
    }
}
