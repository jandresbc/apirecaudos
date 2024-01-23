<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Auditoria
 *
 * @ORM\Table(name="auditoria", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="id_empresa", columns={"id_empresa"})})
 * @ORM\Entity
 */
class Auditoria
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_auditoria", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idAuditoria;

    /**
     * @var string
     *
     * @ORM\Column(name="TxHash", type="string", length=1000, nullable=false)
     */
    private $txhash;

    /**
     * @var string
     *
     * @ORM\Column(name="BxFrom", type="string", length=1000, nullable=false)
     */
    private $bxfrom;

    /**
     * @var string
     *
     * @ORM\Column(name="BxTo", type="string", length=1000, nullable=false)
     */
    private $bxto;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_hora", type="datetime", nullable=false)
     */
    private $fechaHora;

    /**
     * @var string
     *
     * @ORM\Column(name="accion", type="string", length=255, nullable=false)
     */
    private $accion;

    /**
     * @var string
     *
     * @ORM\Column(name="tabla", type="string", length=255, nullable=false)
     */
    private $tabla;

    /**
     * @var string
     *
     * @ORM\Column(name="id_datos", type="string", length=255, nullable=false)
     */
    private $idDatos;

    /**
     * @var string
     *
     * @ORM\Column(name="datos", type="string", length=10000, nullable=false)
     */
    private $datos;

    /**
     * @var \Usuarios
     *
     * @ORM\ManyToOne(targetEntity="Usuarios")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
     * })
     */
    private $idUsuario;

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
     * Get idAuditoria
     *
     * @return integer
     */
    public function getIdAuditoria()
    {
        return $this->idAuditoria;
    }

    /**
     * Set txhash
     *
     * @param string $txhash
     *
     * @return Auditoria
     */
    public function setTxhash($txhash)
    {
        $this->txhash = $txhash;

        return $this;
    }

    /**
     * Get txhash
     *
     * @return string
     */
    public function getTxhash()
    {
        return $this->txhash;
    }

    /**
     * Set bxfrom
     *
     * @param string $bxfrom
     *
     * @return Auditoria
     */
    public function setBxFrom($bxfrom)
    {
        $this->bxfrom = $bxfrom;

        return $this;
    }

    /**
     * Get bxfrom
     *
     * @return string
     */
    public function getBxFrom()
    {
        return $this->bxfrom;
    }

    /**
     * Set bxto
     *
     * @param string $bxto
     *
     * @return Auditoria
     */
    public function setBxTo($bxto)
    {
        $this->bxto = $bxto;

        return $this;
    }

    /**
     * Get bxto
     *
     * @return string
     */
    public function getBxTo()
    {
        return $this->bxto;
    }

    /**
     * Set fechaHora
     *
     * @param \DateTime $fechaHora
     *
     * @return Auditoria
     */
    public function setFechaHora($fechaHora)
    {
        $this->fechaHora = $fechaHora;

        return $this;
    }

    /**
     * Get fechaHora
     *
     * @return \DateTime
     */
    public function getFechaHora()
    {
        return $this->fechaHora;
    }

    /**
     * Set accion
     *
     * @param string $accion
     *
     * @return Auditoria
     */
    public function setAccion($accion)
    {
        $this->accion = $accion;

        return $this;
    }

    /**
     * Get accion
     *
     * @return string
     */
    public function getAccion()
    {
        return $this->accion;
    }

    /**
     * Set tabla
     *
     * @param string $tabla
     *
     * @return Auditoria
     */
    public function setTabla($tabla)
    {
        $this->tabla = $tabla;

        return $this;
    }

    /**
     * Get tabla
     *
     * @return string
     */
    public function getTabla()
    {
        return $this->tabla;
    }

    /**
     * Set idDatos
     *
     * @param string $idDatos
     *
     * @return Auditoria
     */
    public function setIdDatos($idDatos)
    {
        $this->idDatos = $idDatos;

        return $this;
    }

    /**
     * Get idDatos
     *
     * @return string
     */
    public function getIdDatos()
    {
        return $this->idDatos;
    }

    /**
     * Set datos
     *
     * @param string $datos
     *
     * @return Auditoria
     */
    public function setDatos($datos)
    {
        $this->datos = $datos;

        return $this;
    }

    /**
     * Get datos
     *
     * @return string
     */
    public function getDatos()
    {
        return $this->datos;
    }

    /**
     * Set idUsuario
     *
     * @param \App\Entity\Usuarios $idUsuario
     *
     * @return Auditoria
     */
    public function setIdUsuario(\App\Entity\Usuarios $idUsuario = null)
    {
        $this->idUsuario = $idUsuario;

        return $this;
    }

    /**
     * Get idUsuario
     *
     * @return \App\Entity\Usuarios
     */
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

    /**
     * Set idEmpresa
     *
     * @param \App\Entity\Empresas $idEmpresa
     *
     * @return Auditoria
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
    *
    */
    public function getArrayData()
    {
      return [
        $this->idAuditoria,
        $this->txhash,
        $this->bxfrom,
        $this->bxto,
        $this->fechaHora,
        $this->accion,
        $this->tabla,
        $this->idDatos,
        $this->datos,
        $this->idUsuario,
        $this->idEmpresa
      ];
    }
}
