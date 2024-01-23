<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transacciones
 *
 * @ORM\Table(name="transacciones", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="id_empresa_sede_agencia", columns={"id_empresa_sede_agencia"}), @ORM\Index(name="id_caja", columns={"id_caja"})})
 * @ORM\Entity
 */
class Transacciones
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_transaccion", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTransaccion;

    /**
     * @var string
     *
     * @ORM\Column(name="nro_transaccion", type="string", nullable=false)
     */
    private $nroTransaccion;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo_seguridad", type="string", nullable=false)
     */
    private $codigoSeguridad;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_hora_transaccion", type="datetime", nullable=true)
     */
    private $fechaHoraTransaccion;

    /**
     * @var float
     *
     * @ORM\Column(name="total_transaccion", type="float", nullable=true)
     */
    private $totalTransaccion;

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
     * @var \Cajas
     *
     * @ORM\ManyToOne(targetEntity="Cajas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_caja", referencedColumnName="id_caja")
     * })
     */
    private $idCaja;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_closed", type="integer", nullable=true)
     */
    private $isClosed = 0;



    /**
     * Get idTransaccion
     *
     * @return integer
     */
    public function getIdTransaccion()
    {
        return $this->idTransaccion;
    }

    /**
     * Set nroTransaccion
     *
     * @param string $nroTransaccion
     *
     * @return Transacciones
     */
    public function setNroTransaccion($nroTransaccion)
    {
        $this->nroTransaccion = $nroTransaccion;

        return $this;
    }

    /**
     * Get nroTransaccion
     *
     * @return string
     */
    public function getNroTransaccion()
    {
        return $this->nroTransaccion;
    }

    /**
     * Set codigoSeguridad
     *
     * @param string $codigoSeguridad
     *
     * @return Transacciones
     */
    public function setCodigoSeguridad($codigoSeguridad)
    {
        $this->codigoSeguridad = $codigoSeguridad;

        return $this;
    }

    /**
     * Get codigoSeguridad
     *
     * @return string
     */
    public function getCodigoSeguridad()
    {
        return $this->codigoSeguridad;
    }

    /**
     * Set fechaHoraTransaccion
     *
     * @param \DateTime $fechaHoraTransaccion
     *
     * @return Transacciones
     */
    public function setFechaHoraTransaccion($fechaHoraTransaccion)
    {
        $this->fechaHoraTransaccion = $fechaHoraTransaccion;

        return $this;
    }

    /**
     * Get fechaHoraTransaccion
     *
     * @return \DateTime
     */
    public function getFechaHoraTransaccion()
    {
        return $this->fechaHoraTransaccion;
    }

    /**
     * Set totalTransaccion
     *
     * @param float $totalTransaccion
     *
     * @return Transacciones
     */
    public function setTotalTransaccion($totalTransaccion)
    {
        $this->totalTransaccion = $totalTransaccion;

        return $this;
    }

    /**
     * Get totalTransaccion
     *
     * @return float
     */
    public function getTotalTransaccion()
    {
        return $this->totalTransaccion;
    }

    /**
     * Set idUsuario
     *
     * @param \App\Entity\Usuarios $idUsuario
     *
     * @return Transacciones
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
     * @return Transacciones
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

    /**
     * Set idCaja
     *
     * @param \App\Entity\Cajas $idCaja
     *
     * @return Transacciones
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
     * Set isClosed
     *
     * @param integer $isClosed
     *
     * @return Transacciones
     */
    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    /**
     * Get isCLosed
     *
     * @return integer
     */
    public function getIsClosed()
    {
        return $this->totalTransaccion;
    }

    /**
    * __toString()
    **/
    public function __toString()
    {
      return $this->nroTransaccion;
    }

    /**
    *
    **/
    public function getArrayData()
    {
      return [
        $this->idTransaccion,
        $this->nroTransaccion,
        $this->codigoSeguridad,
        $this->fechaHoraTransaccion->format("Y-m-d H:i:s"),
        $this->totalTransaccion,
        $this->idUsuario,
        $this->idCaja,
        $this->idEmpresaSedeAgencia,
        $this->isClosed
      ];
    }
}
