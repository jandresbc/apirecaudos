<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GruposUsuarios
 *
 * @ORM\Table(name="grupos_usuarios")
 * @ORM\Entity
 */
class GruposUsuarios
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_grupo_usuario", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idGrupoUsuario;

    /**
     * @var string
     *
     * @ORM\Column(name="grupo_usuario", type="string", length=255, nullable=true)
     */
    private $grupoUsuario;



    /**
     * Get idGrupoUsuario
     *
     * @return integer
     */
    public function getIdGrupoUsuario()
    {
        return $this->idGrupoUsuario;
    }

    /**
     * Set grupoUsuario
     *
     * @param string $grupoUsuario
     *
     * @return GruposUsuarios
     */
    public function setGrupoUsuario($grupoUsuario)
    {
        $this->grupoUsuario = $grupoUsuario;

        return $this;
    }

    /**
     * Get grupoUsuario
     *
     * @return string
     */
    public function getGrupoUsuario()
    {
        return $this->grupoUsuario;
    }

    /*
    * __toString();
    */
    public function __toString()
    {
      return $this->getGrupoUsuario();
    }
}
