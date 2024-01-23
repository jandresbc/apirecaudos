<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Permisos
 *
 * @ORM\Table(name="permisos", indexes={@ORM\Index(name="id_pagina", columns={"id_pagina"}), @ORM\Index(name="id_grupo_usuario", columns={"id_grupo_usuario"}), @ORM\Index(name="id_empresa", columns={"id_empresa"})})
 * @ORM\Entity(repositoryClass="App\Repository\PermisosRepository")
 */
class Permisos
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_permiso", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPermiso;

    /**
     * @var \Paginas
     *
     * @ORM\ManyToOne(targetEntity="Paginas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_pagina", referencedColumnName="id_pagina")
     * })
     */
    private $idPagina;

    /**
     * @var \GruposUsuarios
     *
     * @ORM\ManyToOne(targetEntity="GruposUsuarios")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_grupo_usuario", referencedColumnName="id_grupo_usuario")
     * })
     */
    private $idGrupoUsuario;

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
     * Get idPermiso
     *
     * @return integer
     */
    public function getIdPermiso()
    {
        return $this->idPermiso;
    }

    /**
     * Set idPagina
     *
     * @param \App\Entity\Paginas $idPagina
     *
     * @return Permisos
     */
    public function setIdPagina(\App\Entity\Paginas $idPagina = null)
    {
        $this->idPagina = $idPagina;

        return $this;
    }

    /**
     * Get idPagina
     *
     * @return \App\Entity\Paginas
     */
    public function getIdPagina()
    {
        return $this->idPagina;
    }

    /**
     * Set idGrupoUsuario
     *
     * @param \App\Entity\GruposUsuarios $idGrupoUsuario
     *
     * @return Permisos
     */
    public function setIdGrupoUsuario(\App\Entity\GruposUsuarios $idGrupoUsuario = null)
    {
        $this->idGrupoUsuario = $idGrupoUsuario;

        return $this;
    }

    /**
     * Get idGrupoUsuario
     *
     * @return \App\Entity\GruposUsuarios
     */
    public function getIdGrupoUsuario()
    {
        return $this->idGrupoUsuario;
    }

    /**
     * Set idEmpresa
     *
     * @param \App\Entity\Empresas $idEmpresa
     *
     * @return Permisos
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
