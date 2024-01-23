<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Parametros
 *
 * @ORM\Table(name="parametros", indexes={@ORM\Index(name="id_empresa", columns={"id_empresa"})})
 * @ORM\Entity
 */
class Parametros
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_parametros", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idParametros;

    /**
     * @var string
     *
     * @ORM\Column(name="url_logo_empresa", type="string", length=255, nullable=true)
     */
    private $urlLogoEmpresa;

    /**
     * @var string
     *
     * @ORM\Column(name="header_informes", type="string", length=255, nullable=true)
     */
    private $headerInformes;

    /**
     * @var integer
     *
     * @ORM\Column(name="porcentaje_meta_recaudo", type="integer", length=15, nullable=true)
     */
    private $porcentajeMetaRecaudo;

    /**
     * @var string
     *
     * @ORM\Column(name="enabledIPs", type="string", length=255, nullable=true)
     */
    private $enabledIPs;

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
     * Get idParametros
     *
     * @return integer
     */
    public function getIdParametros()
    {
        return $this->idParametros;
    }

    /**
     * Set urlLogoEmpresa
     *
     * @param string $urlLogoEmpresa
     *
     * @return Parametros
     */
    public function setUrlLogoEmpresa($urlLogoEmpresa)
    {
        $this->urlLogoEmpresa = $urlLogoEmpresa;

        return $this;
    }

    /**
     * Get urlLogoEmpresa
     *
     * @return string
     */
    public function getUrlLogoEmpresa()
    {
        return $this->urlLogoEmpresa;
    }

    /**
     * Set headerInformes
     *
     * @param string $headerInformes
     *
     * @return Parametros
     */
    public function setHeaderInformes($headerInformes)
    {
        $this->headerInformes = $headerInformes;

        return $this;
    }

    /**
     * Get headerInformes
     *
     * @return string
     */
    public function getHeaderInformes()
    {
        return $this->headerInformes;
    }

    /**
     * Set porcentajeMetaRecaudo
     *
     * @param integer $porcentajeMetaRecaudo
     *
     * @return Parametros
     */
    public function setPorcentajeMetaRecaudo($porcentajeMetaRecaudo)
    {
        $this->porcentajeMetaRecaudo = $porcentajeMetaRecaudo;

        return $this;
    }

    /**
     * Get porcentajeMetaRecaudo
     *
     * @return integer
     */
    public function getPorcentajeMetaRecaudo()
    {
        return $this->porcentajeMetaRecaudo;
    }

    /**
     * Set enabledIPs
     *
     * @param string $enabledIPs
     *
     * @return Parametros
     */
    public function setEnabledIPs($enabledIPs)
    {
        $this->enabledIPs = $enabledIPs;

        return $this;
    }

    /**
     * Get enabledIPs
     *
     * @return string
     */
    public function getEnabledIPs()
    {
        return $this->enabledIPs;
    }

    /**
     * Set idEmpresa
     *
     * @param \App\Entity\Empresas $idEmpresa
     *
     * @return Parametros
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
        $this->idParametros,
        $this->urlLogoEmpresa,
        $this->headerInformes,
        $this->porcentajeMetaRecaudo,
        $this->idEmpresa
      ];
    }
}
