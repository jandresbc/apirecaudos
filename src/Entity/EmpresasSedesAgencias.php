<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmpresasSedesAgencias
 *
 * @ORM\Table(name="empresas_sedes_agencias", indexes={@ORM\Index(name="id_empresa", columns={"id_empresa"}), @ORM\Index(name="id_sede_agencia", columns={"id_sede_agencia"})})
 * @ORM\Entity
 */
class EmpresasSedesAgencias
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_empresa_sede_agencia", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEmpresaSedeAgencia;

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
     * @var \SedesAgencias
     *
     * @ORM\ManyToOne(targetEntity="SedesAgencias")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_sede_agencia", referencedColumnName="id_sede_agencia")
     * })
     */
    private $idSedeAgencia;



    /**
     * Get idEmpresaSedeAgencia
     *
     * @return integer
     */
    public function getIdEmpresaSedeAgencia()
    {
        return $this->idEmpresaSedeAgencia;
    }

    /**
     * Set idEmpresa
     *
     * @param \App\Entity\Empresas $idEmpresa
     *
     * @return EmpresasSedesAgencias
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
     * Set idSedeAgencia
     *
     * @param \App\Entity\SedesAgencias $idSedeAgencia
     *
     * @return EmpresasSedesAgencias
     */
    public function setIdSedeAgencia(\App\Entity\SedesAgencias $idSedeAgencia = null)
    {
        $this->idSedeAgencia = $idSedeAgencia;

        return $this;
    }

    /**
     * Get idSedeAgencia
     *
     * @return \App\Entity\SedesAgencias
     */
    public function getIdSedeAgencia()
    {
        return $this->idSedeAgencia;
    }

    /**
    * _toSring()
    **/
    public function __toString()
    {
      return $this->getIdEmpresa()." | ".$this->getIdSedeAgencia();
    }

    /**
    *
    **/
    public function getArrayData()
    {
      return [
        $this->idEmpresaSedeAgencia,
        $this->idSedeAgencia,
        $this->idEmpresa
      ];
    }
}
