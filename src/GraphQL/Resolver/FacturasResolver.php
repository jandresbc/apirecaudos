<?php

namespace App\GraphQL\Resolver;

use App\Controller\ServicesController;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Overblog\GraphQLBundle\Error\UserError;
use Overblog\GraphQLBundle\Error\UserWarning;

class FacturasResolver extends AbstractController implements ResolverInterface, AliasedInterface {

    private $em;
    private $headers;
    private $request;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        //$this->headers = getallheaders();
        $this->headers = $_REQUEST;
        $this->request = Request::createFromGlobals();
    }

    public function resolve(Argument $args)
    {
        $main = $this->get("main");
        $empresa = $this->em->getRepository("App:Empresas")->findBy([
            "nit"=>$args["nitEmpresa"]
        ]);
        $conf = $main->ConfigappAction($empresa[0]->getIdEmpresa());
        $parametros = json_decode($conf->getContent());
        $host = $this->request->headers->get("host");
        $Authorization = $this->request->query->get("Authorization");
        
        $main->logRequest("consultaFacturasAPI",[$args["nroFactura"],$args["valorFactura"],$args["nitEmpresa"]],$empresa[0]->getIdEmpresa());
        
        $EnabledIPs = explode(",",$parametros->enabledIPs);
        
        if(in_array($host,$EnabledIPs)){
            if($Authorization != ''){
                $now = new \DateTime("now",new \DateTimeZone("America/Bogota"));
                $Authorization = explode("Bearer ",$Authorization)[1];
                
                try{
                    $data = $this->get('lexik_jwt_authentication.encoder')->decode($Authorization);
                }catch(\Exception $er){         
                    $facturas[0] = [
                        "code"=>401,
                        "message"=>$er->getMessage(),
                        "error"=>true,
                        "idFactura"=>"",
                        "idEmpresa"=>"",
                        "nroFactura"=>""
                    ];
                    return $facturas;
                }                
                
                if(isset($args['valorFactura']) && $args['valorFactura'] > 0){
                    $facturas = $this->em->getRepository('App:Facturas')->findBy([
                        "nroFactura"=>$args['nroFactura'],
                        "valorFactura"=>$args['valorFactura'],
                        "idEmpresa"=>$empresa[0]->getIdEmpresa(),
                        "periodoActual"=>1//La factura debe estar habilitada dentro del periodo de facturación actual.
                    ]);
                }else{
                    $facturas = $this->em->getRepository('App:Facturas')->findBy([
                        "nroFactura"=>$args['nroFactura'],
                        "idEmpresa"=>$empresa[0]->getIdEmpresa(),
                        "periodoActual"=>1//La factura debe estar habilitada dentro del periodo de facturación actual.
                    ]);
                }
                
                if(count($facturas) > 0){
                    foreach($facturas as $index=>$valor){
                        //Validación fecha de vencimiento de la factura.
                        if($now > $valor->getFechaVencimiento()){
                            $facturas[$index] = [
                                "code"=>406,
                                "message"=>"La factura ".$valor->getNroFactura()." se encuentra vencida.",
                                "error"=>true,
                                "idFactura"=>"",
                                "idEmpresa"=>"",
                                "nroFactura"=>""
                            ];
                        }
                        
                        //Buscar si la factura ya tiene un pago registrado.
                        $pagos = $this->em->getRepository("App:Pagos")->findBy([
                            "idFactura"=>$valor->getIdFactura()
                        ]);

                        if(count($pagos) == 0){//Retorna la información de la factura si no hay un pago registrado.
                            $fechaVenc = $valor->getFechaVencimiento()->format("Y-m-d H:i:s");
                            $valor->setFechaVencimiento($fechaVenc);
                            $valor->code = 200;
                            $valor->message = "Done";
                            $valor->error = false;
                        }else{
                            $facturas[$index] = [
                                "code"=>406,
                                "message"=>"La factura ".$valor->getNroFactura()." ya tiene un pago registrado en el sistema.",
                                "error"=>true,
                                "idFactura"=>"null",
                                "idEmpresa"=>"null",
                                "nroFactura"=>"null"
                            ];
                        }
                    }//Fin de recorrer facturas
                    return $facturas;
                }else{
                    $facturas[0] = [
                        "code"=>406,
                        "message"=>"No se encuentra la factura ha consultar. Las posibles causas es que esta factura haya sido desactivada para su recaudo o ya haya sido pagada en otro punto de recaudo.",
                        "error"=>true,
                        "idFactura"=>"",
                        "idEmpresa"=>"",
                        "nroFactura"=>""
                    ];
                    return $facturas; 
                }
            }else{
                $facturas[0] = [
                    "code"=>400,
                    //"message"=>"El Header de la Petición debe contener el atributo 'Authorization' con el token de autorización.",
                    "message"=>"La Petición debe contener el atributo 'Authorization' con el token de autorización.",
                    "error"=>true,
                    "idFactura"=>"",
                    "idEmpresa"=>"",
                    "nroFactura"=>""
                ];
                return $facturas;
            }
        }else{
            $facturas[0] = [
                "code"=>403,
                "message"=>"Su dirección IP(".$host.") no esta autorizada a conectarse a este servicio.",
                "error"=>true,
                "idFactura"=>"",
                "idEmpresa"=>"",
                "nroFactura"=>""
            ];
            return $facturas;
        }
    }

    public static function getAliases()
    {
        return [
            'resolve' => 'Facturas'
        ];
    }
}