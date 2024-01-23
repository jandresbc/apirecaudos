<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TipoNovedades
 *
 * @ORM\Table(name="tipo_novedades", indexes={@ORM\Index(name="id_tipo_novedad", columns={"id_tipo_novedad"})})
 * @ORM\Entity
 */
class TipoNovedades
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_tipo_novedad", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTipoNovedad;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo_novedad", type="string", length=255, nullable=true)
     */
    private $tipoNovedad;



    /**
     * Get idTipoNovedad
     *
     * @return integer
     */
    public function getIdTipoNovedad()
    {
        return $this->idTipoNovedad;
    }

    /**
     * Set tipoNovedad
     *
     * @param string $tipoNovedad
     *
     * @return TipoNovedades
     */
    public function setTipoNovedad($tipoNovedad)
    {
        $this->tipoNovedad = $tipoNovedad;

        return $this;
    }

    /**
     * Get tipoNovedad
     *
     * @return string
     */
    public function getTipoNovedad()
    {
        return $this->tipoNovedad;
    }

    /*
    ** __toString()
    */
    public function __toString()
    {
      return $this->tipoNovedad;
    }

    /**
    *
    **/
    public function getArrayData()
    {
      return [
        $this->idTipoNovedad,
        $this->tipoNovedad
      ];
    }
}
