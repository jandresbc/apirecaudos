<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Novedades
 *
 * @ORM\Table(name="novedades", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="id_tipo_novedad", columns={"id_tipo_novedad"}), @ORM\Index(name="id_novedad", columns={"id_novedad"})})
 * @ORM\Entity
 */
class Novedades
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_novedad", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idNovedad;

    /**
     * @var string
     *
     * @ORM\Column(name="tx_hash", type="string", length=255, nullable=false)
     */
    private $txHash;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_hora_novedad", type="datetime", nullable=false)
     */
    private $fechaHoraNovedad;

    /**
     * @var string
     *
     * @ORM\Column(name="modulo_afectado", type="string", length=255, nullable=false)
     */
    private $moduloAfectado;

    /**
     * @var string
     *
     * @ORM\Column(name="identificador_data", type="string", length=255, nullable=false)
     */
    private $identificadorData;

    /**
     * @var string
     *
     * @ORM\Column(name="anterior_data", type="string", length=255, nullable=false)
     */
    private $anteriorData;

    /**
     * @var string
     *
     * @ORM\Column(name="observaciones_novedad", type="string", length=255, nullable=false)
     */
    private $observacionesNovedad;

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
     * @var \TipoNovedades
     *
     * @ORM\ManyToOne(targetEntity="TipoNovedades")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_tipo_novedad", referencedColumnName="id_tipo_novedad")
     * })
     */
    private $idTipoNovedad;

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
     * Get idNovedad
     *
     * @return integer
     */
    public function getIdNovedad()
    {
        return $this->idNovedad;
    }

    /**
     * Set txHash
     *
     * @param string $txHash
     *
     * @return Novedades
     */
    public function setTxHash($txHash)
    {
        $this->txHash = $txHash;

        return $this;
    }

    /**
     * Get txHash
     *
     * @return string
     */
    public function getTxHash()
    {
        return $this->txHash;
    }

    /**
     * Set fechaHoraNovedad
     *
     * @param \DateTime $fechaHoraNovedad
     *
     * @return Novedades
     */
    public function setFechaHoraNovedad($fechaHoraNovedad)
    {
        $this->fechaHoraNovedad = $fechaHoraNovedad;

        return $this;
    }

    /**
     * Get fechaHoraNovedad
     *
     * @return \DateTime
     */
    public function getFechaHoraNovedad()
    {
        return $this->fechaHoraNovedad;
    }

    /**
     * Set moduloAfectado
     *
     * @param string $moduloAfectado
     *
     * @return Novedades
     */
    public function setModuloAfectado($moduloAfectado)
    {
        $this->moduloAfectado = $moduloAfectado;

        return $this;
    }

    /**
     * Get moduloAfectado
     *
     * @return string
     */
    public function getModuloAfectado()
    {
        return $this->moduloAfectado;
    }

    /**
     * Set identificadorData
     *
     * @param string $identificadorData
     *
     * @return Novedades
     */
    public function setIdentificadorData($identificadorData)
    {
        $this->identificadorData = $identificadorData;

        return $this;
    }

    /**
     * Get identificadorData
     *
     * @return string
     */
    public function getIdentificadorData()
    {
        return $this->identificadorData;
    }

    /**
     * Set anteriorData
     *
     * @param string $anteriorData
     *
     * @return Novedades
     */
    public function setAnteriorData($anteriorData)
    {
        $this->anteriorData = $anteriorData;

        return $this;
    }

    /**
     * Get anteriorData
     *
     * @return string
     */
    public function getAnteriorData()
    {
        return $this->anteriorData;
    }

    /**
     * Set observaciones
     *
     * @param string $observacionesNovedad
     *
     * @return Novedades
     */
    public function setObservacionesNovedad($observacionesNovedad)
    {
        $this->observacionesNovedad = $observacionesNovedad;

        return $this;
    }

    /**
     * Get observacionesNovedad
     *
     * @return string
     */
    public function getObservacionesNovedad()
    {
        return $this->observacionesNovedad;
    }

    /**
     * Set idUsuario
     *
     * @param \App\Entity\Usuarios $idUsuario
     *
     * @return Novedades
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
     * Set idTipoNovedad
     *
     * @param \App\Entity\TipoNovedades $idTipoNovedad
     *
     * @return Novedades
     */
    public function setIdTipoNovedad(\App\Entity\TipoNovedades $idTipoNovedad = null)
    {
        $this->idTipoNovedad = $idTipoNovedad;

        return $this;
    }

    /**
     * Get idTipoNovedad
     *
     * @return \App\Entity\TipoNovedades
     */
    public function getIdTipoNovedad()
    {
        return $this->idTipoNovedad;
    }

    /**
     * Set idEmpresa
     *
     * @param \App\Entity\Empresas $idEmpresa
     *
     * @return Novedades
     */
    public function setIdEmpresa(\App\Entity\Empresas $idEmpresa = null)
    {
        $this->idEmpresa = $idEmpresa;

        return $this;
    }

    /**
     * Get idEmpresa
     *
     * @return \App\Entity\idEmpresa
     */
    public function getIdEmpresa()
    {
        return $this->idEmpresa;
    }

    /*
    ** __toString()
    */
    public function __toString()
    {
      return $this->fechaHoraNovedad." | ".$this->moduloAfectado." | ".$this->observaciones;
    }

    /**
    *
    **/
    public function getArrayData()
    {
      return [
        $this->idNovedad,
        $this->txHash,
        $this->fechaHoraNovedad,
        $this->moduloAfectado,
        $this->identificadorData,
        $this->anteriorData,
        $this->observaciones,
        $this->idUsuario,
        $this->idTipoNovedad,
        $this->idEmpresa
      ];
    }
}
