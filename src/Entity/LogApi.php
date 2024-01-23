<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LogApi
 *
 * @ORM\Table(name="log_api", indexes={@ORM\Index(name="id", columns={"id"}), @ORM\Index(name="id_empresa", columns={"id_empresa"})})
 * @ORM\Entity
 */
class LogApi
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="host", type="string", length=255, nullable=true)
     */
    private $host;

    /**
     * @var string|null
     *
     * @ORM\Column(name="service", type="string", length=255, nullable=true)
     */
    private $service;

    /**
     * @var string|null
     *
     * @ORM\Column(name="header", type="string", length=255, nullable=true)
     */
    private $header;

    /**
     * @var string|null
     *
     * @ORM\Column(name="method", type="string", length=255, nullable=true)
     */
    private $method;

    /**
     * @var string|null
     *
     * @ORM\Column(name="arguments", type="string", length=255, nullable=true)
     */
    private $arguments;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="fecha_hora_peticion", type="datetime", nullable=true)
     */
    private $fechaHoraPeticion;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set host
     *
     * @param string $host
     *
     * @return LogApi
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set service
     *
     * @param string $service
     *
     * @return LogApi
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set header
     *
     * @param string $header
     *
     * @return LogApi
     */
    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get header
     *
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Set method
     *
     * @param string $method
     *
     * @return LogApi
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set arguments
     *
     * @param string $arguments
     *
     * @return LogApi
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Get arguments
     *
     * @return string
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Set fechaHoraPeticion
     *
     * @param \DateTime $fechaHoraPeticion
     *
     * @return LogApi
     */
    public function setFechaHoraPeticion($fechaHoraPeticion)
    {
        $this->fechaHoraPeticion = $fechaHoraPeticion;

        return $this;
    }

    /**
     * Get fechaHoraPeticion
     *
     * @return \DateTime
     */
    public function getFechaHoraPeticion()
    {
        return $this->fechaHoraPeticion;
    }

    /**
     * Set idEmpresa
     *
     * @param \App\Entity\Empresas $idEmpresa
     *
     * @return LogApi
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
}
