<?php

namespace App\GraphQL\Resolver;

use App\Controller\ServicesController;

use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class EmpresasResolver implements ResolverInterface, AliasedInterface {

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function resolve(Argument $args)
    {
        $main = new ServicesController($this->em);
        
        $empresas = $this->em->getRepository('App:Empresas')->findBy([
            "idEmpresa"=>$args["idEmpresa"]
        ]);
        
        if(is_null($empresas)){
            $empresas = [];
            return $empresas;
        }else{
            return $empresas[0];
        }
    }

    public static function getAliases()
    {
        return [
            'resolve' => 'Empresas'
        ];
    }
}