<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cajas
 *
 * @ORM\Table(name="cajas", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="id_empresa_sede_agencia", columns={"id_empresa_sede_agencia"})})
 * @ORM\Entity
 */
class Cajas
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_caja", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCaja;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre_caja", type="string", length=255, nullable=false)
     */
    private $nombreCaja;

    /**
     * @var integer
     *
     * @ORM\Column(name="session_activa", type="integer", nullable=true)
     */
    private $sessionActiva = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_hora_creacion", type="datetime", nullable=false)
     */
    private $fechaHoraCreacion;

    /**
     * @var integer
     *
     * @ORM\Column(name="hasArchivado", type="integer", nullable=true)
     */
    private $hasArchivado = 0;

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
     * @var \EmpresasSedesAgencias
     *
     * @ORM\ManyToOne(targetEntity="EmpresasSedesAgencias")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_empresa_sede_agencia", referencedColumnName="id_empresa_sede_agencia")
     * })
     */
    private $idEmpresaSedeAgencia;



    /**
     * Get idCaja
     *
     * @return integer
     */
    public function getIdCaja()
    {
        return $this->idCaja;
    }

    /**
     * Set nombreCaja
     *
     * @param string $nombreCaja
     *
     * @return Cajas
     */
    public function setNombreCaja($nombreCaja)
    {
        $this->nombreCaja = $nombreCaja;

        return $this;
    }

    /**
     * Get nombreCaja
     *
     * @return string
     */
    public function getNombreCaja()
    {
        return $this->nombreCaja;
    }

    /**
     * Set sessionActiva
     *
     * @param integer $sessionActiva
     *
     * @return Cajas
     */
    public function setSessionActiva($sessionActiva)
    {
        $this->sessionActiva = $sessionActiva;

        return $this;
    }

    /**
     * Get sessionActiva
     *
     * @return integer
     */
    public function getSessionActiva()
    {
        return $this->sessionActiva;
    }

    /**
     * Set fechaHoraCreacion
     *
     * @param \DateTime $fechaHoraCreacion
     *
     * @return Cajas
     */
    public function setFechaHoraCreacion($fechaHoraCreacion)
    {
        $this->fechaHoraCreacion = $fechaHoraCreacion;

        return $this;
    }

    /**
     * Get fechaHoraCreacion
     *
     * @return \DateTime
     */
    public function getFechaHoraCreacion()
    {
        return $this->fechaHoraCreacion;
    }

    /**
     * Set hasArchivado
     *
     * @param integer $hasArchivado
     *
     * @return Cajas
     */
    public function setHasArchivado($hasArchivado)
    {
        $this->hasArchivado = $hasArchivado;

        return $this;
    }

    /**
     * Get hasArchivado
     *
     * @return integer
     */
    public function getHasArchivado()
    {
        return $this->hasArchivado;
    }

    /**
     * Set idUsuario
     *
     * @param \App\Entity\Usuarios $idUsuario
     *
     * @return Cajas
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
     * Set idEmpresaSedeAgencia
     *
     * @param \App\Entity\EmpresasSedesAgencias $idEmpresaSedeAgencia
     *
     * @return Cajas
     */
    public function setIdEmpresaSedeAgencia(\App\Entity\EmpresasSedesAgencias $idEmpresaSedeAgencia = null)
    {
        $this->idEmpresaSedeAgencia = $idEmpresaSedeAgencia;

        return $this;
    }

    /**
     * Get idEmpresaSedeAgencia
     *
     * @return \App\Entity\EmpresasSedesAgencias
     */
    public function getIdEmpresaSedeAgencia()
    {
        return $this->idEmpresaSedeAgencia;
    }

    /*
    * __toString();
    */
    public function __toString(){
      return $this->nombreCaja;
    }

    /**
    *
    **/
    public function getArrayData()
    {
      return [
        $this->idCaja,
        $this->nombreCaja,
        $this->sessionActiva,
        $this->fechaHoraCreacion->format("Y-m-d H:i:s"),
        $this->hasArchivado,
        $this->idUsuario,
        $this->idEmpresaSedeAgencia
      ];
    }
}
