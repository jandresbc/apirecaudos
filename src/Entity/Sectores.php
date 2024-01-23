<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sectores
 *
 * @ORM\Table(name="sectores")
 * @ORM\Entity
 */
class Sectores
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_sector", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idSector;

    /**
     * @var string
     *
     * @ORM\Column(name="sector", type="string", length=255, nullable=false)
     */
    private $sector;


}
