<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Divipola
 *
 * @ORM\Table(name="divipola")
 * @ORM\Entity
 */
class Divipola
{
    /**
     * @var integer
     *
     * @ORM\Column(name="divipola", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $divipola;

    /**
     * @var integer
     *
     * @ORM\Column(name="cod_depto", type="integer", nullable=true)
     */
    private $codDepto;

    /**
     * @var integer
     *
     * @ORM\Column(name="cod_mpio", type="integer", nullable=true)
     */
    private $codMpio;

    /**
     * @var string
     *
     * @ORM\Column(name="depto", type="string", length=30, nullable=true)
     */
    private $depto;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_poblad", type="string", length=30, nullable=true)
     */
    private $nomPoblad;



    /**
     * Get divipola
     *
     * @return integer
     */
    public function getDivipola()
    {
        return $this->divipola;
    }

    /**
     * Set codDepto
     *
     * @param integer $codDepto
     *
     * @return Divipola
     */
    public function setCodDepto($codDepto)
    {
        $this->codDepto = $codDepto;

        return $this;
    }

    /**
     * Get codDepto
     *
     * @return integer
     */
    public function getCodDepto()
    {
        return $this->codDepto;
    }

    /**
     * Set codMpio
     *
     * @param integer $codMpio
     *
     * @return Divipola
     */
    public function setCodMpio($codMpio)
    {
        $this->codMpio = $codMpio;

        return $this;
    }

    /**
     * Get codMpio
     *
     * @return integer
     */
    public function getCodMpio()
    {
        return $this->codMpio;
    }

    /**
     * Set depto
     *
     * @param string $depto
     *
     * @return Divipola
     */
    public function setDepto($depto)
    {
        $this->depto = $depto;

        return $this;
    }

    /**
     * Get depto
     *
     * @return string
     */
    public function getDepto()
    {
        return $this->depto;
    }

    /**
     * Set nomPoblad
     *
     * @param string $nomPoblad
     *
     * @return Divipola
     */
    public function setNomPoblad($nomPoblad)
    {
        $this->nomPoblad = $nomPoblad;

        return $this;
    }

    /**
     * Get nomPoblad
     *
     * @return string
     */
    public function getNomPoblad()
    {
        return $this->nomPoblad;
    }

    /*
    * __toString()
    */
    public function __toString()
    {
      return $this->nomPoblad;
    }
}
