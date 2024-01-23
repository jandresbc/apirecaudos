<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Paginas
 *
 * @ORM\Table(name="paginas")
 * @ORM\Entity
 */
class Paginas
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_pagina", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPagina;

    /**
     * @var string
     *
     * @ORM\Column(name="pagina", type="string", length=255, nullable=true)
     */
    private $pagina;



    /**
     * Get idPagina
     *
     * @return integer
     */
    public function getIdPagina()
    {
        return $this->idPagina;
    }

    /**
     * Set pagina
     *
     * @param string $pagina
     *
     * @return Paginas
     */
    public function setPagina($pagina)
    {
        $this->pagina = $pagina;

        return $this;
    }

    /**
     * Get pagina
     *
     * @return string
     */
    public function getPagina()
    {
        return $this->pagina;
    }
}
