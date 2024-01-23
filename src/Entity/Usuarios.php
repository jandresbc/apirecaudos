<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Usuarios
 *
 * @ORM\Table(name="usuarios", indexes={@ORM\Index(name="id_grupo_usuario", columns={"id_grupo_usuario"}), @ORM\Index(name="usuarios_ibfk_2", columns={"id_empresa_sede_agencia"})})
 * @ORM\Entity
 */
class Usuarios
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_usuario", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idUsuario;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre_completo", type="string", length=255, nullable=false)
     */
    private $nombreCompleto;

    /**
     * @var integer
     *
     * @ORM\Column(name="identificacion", type="integer", nullable=false)
     */
    private $identificacion;

    /**
     * @var string
     *
     * @ORM\Column(name="contrasena", type="string", length=255, nullable=false)
     */
    private $contrasena;

    /**
     * @var integer
     *
     * @ORM\Column(name="telefono", type="integer", nullable=true)
     */
    private $telefono;

    /**
     * @var integer
     *
     * @ORM\Column(name="activo", type="integer", nullable=true)
     */
    private $activo = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="auth", type="integer", nullable=true)
     */
    private $auth = '0';

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
     * @var \SedesAgencias
     *
     * @ORM\ManyToOne(targetEntity="SedesAgencias")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_sede_agencia", referencedColumnName="id_sede_agencia")
     * })
     */
    private $idSedeAgencia;



    /**
     * Set nombreCompleto
     *
     * @param string $nombreCompleto
     *
     * @return Usuarios
     */
    public function setNombreCompleto($nombreCompleto)
    {
        $this->nombreCompleto = $nombreCompleto;

        return $this;
    }

    /**
     * Get nombreCompleto
     *
     * @return string
     */
    public function getNombreCompleto()
    {
        return $this->nombreCompleto;
    }

    /**
     * Set identificacion
     *
     * @param integer $identificacion
     *
     * @return Usuarios
     */
    public function setIdentificacion($identificacion)
    {
        $this->identificacion = $identificacion;

        return $this;
    }

    /**
     * Get identificacion
     *
     * @return integer
     */
    public function getIdentificacion()
    {
        return $this->identificacion;
    }

    /**
     * Set contrasena
     *
     * @param string $contrasena
     *
     * @return Usuarios
     */
    public function setContrasena($contrasena)
    {
        $this->contrasena = $contrasena;

        return $this;
    }

    /**
     * Get contrasena
     *
     * @return string
     */
    public function getContrasena()
    {
        return $this->contrasena;
    }

    /**
     * Set telefono
     *
     * @param integer $telefono
     *
     * @return Usuarios
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get telefono
     *
     * @return integer
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set activo
     *
     * @param integer $activo
     *
     * @return Usuarios
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo
     *
     * @return integer
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set auth
     *
     * @param integer $auth
     *
     * @return Usuarios
     */
    public function setAuth($auth)
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * Get auth
     *
     * @return integer
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * Get idUsuario
     *
     * @return integer
     */
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

    /**
     * Set idGrupoUsuario
     *
     * @param \App\Entity\GruposUsuarios $idGrupoUsuario
     *
     * @return Usuarios
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
     * Set idSedeAgencia
     *
     * @param \App\Entity\SedesAgencias $idSedeAgencia
     *
     * @return Usuarios
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

    /*
    * __toString();
    */
    public function __toString()
    {
      return $this->getNombreCompleto();
    }

    /*
    *
    */
    public function getArrayData()
    {
      return [
        $this->idUsuario,
        $this->nombreCompleto,
        $this->identificacion,
        $this->contrasena,
        $this->telefono,
        $this->activo,
        $this->auth,
        $this->idSedeAgencia,
        $this->idGrupoUsuario
      ];
    }
}
