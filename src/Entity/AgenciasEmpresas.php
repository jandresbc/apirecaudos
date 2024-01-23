<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AgenciasEmpresas
 *
 * @ORM\Table(name="agencias_empresas", indexes={@ORM\Index(name="id_agencia", columns={"id_agencia"}), @ORM\Index(name="id_empresa", columns={"id_empresa"})})
 * @ORM\Entity
 */
class AgenciasEmpresas
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_agencia_empresa", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idAgenciaEmpresa;

    /**
     * @var \Agencias
     *
     * @ORM\ManyToOne(targetEntity="Agencias")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_agencia", referencedColumnName="id_agencia")
     * })
     */
    private $idAgencia;

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
     * Get idAgenciaEmpresa
     *
     * @return integer
     */
    public function getIdAgenciaEmpresa()
    {
        return $this->idAgenciaEmpresa;
    }

    /**
     * Set idAgencia
     *
     * @param \App\Entity\Agencias $idAgencia
     *
     * @return AgenciasEmpresas
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

    /**
     * Set idEmpresa
     *
     * @param \App\Entity\Empresas $idEmpresa
     *
     * @return AgenciasEmpresas
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

    /*
    * __toString()
    */
    public function __toString()
    {
      return $this->idAgencia." | ".$this->idEmpresa;
    }

    /**
    *
    **/
    public function getArrayData()
    {
      return [
        $this->idAgenciaEmpresa,
        $this->idAgencia,
        $this->idEmpresa
      ];
    }
}
