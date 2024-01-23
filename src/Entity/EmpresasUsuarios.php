<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmpresasUsuarios
 *
 * @ORM\Table(name="empresas_usuarios", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="id_empresa", columns={"id_empresa"})})
 * @ORM\Entity
 */
class EmpresasUsuarios
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_empresa_usuario", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEmpresaUsuario;

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


}
