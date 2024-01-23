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

class PagosResolver extends AbstractController implements ResolverInterface, AliasedInterface {

    private $em;
    private $headers;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        //$this->headers = getallheaders();
        $this->headers = $_REQUEST;
        $this->request = Request::createFromGlobals();
    }

    public function resolve(Argument $args)
    {
        ini_set('max_execution_time', 300);//5 minutos
        ini_set("memory_limit","512M");
        $main = $this->get("main");
        
        //Para la paginación
        $pag = isset($args["pag"]) ? $args["pag"] : 1;
        $limite = isset($args["limite"]) ? $args["limite"] : 1000;
        $limiteInicio = $pag == 1 ? 0 : ($pag-1) * $limite;
        $totalPaginas = 0;
        //Fin

        $empresa = $this->em->getRepository("App:Empresas")->findBy([
            "nit"=>$args["nitEmpresa"]
        ]);
        $conf = $main->ConfigappAction($empresa[0]->getIdEmpresa());
        $parametros = json_decode($conf->getContent());
        $host = $this->request->headers->get("host");
        $Authorization = $this->request->query->get("Authorization");
        
        $main->logRequest("consultaFacturasAPI",[$args["nitEmpresa"],$args["anioFacturado"],$args["mesFacturado"]],$empresa[0]->getIdEmpresa());
        
        $EnabledIPs = explode(",",$parametros->enabledIPs);
        
        if(in_array($host,$EnabledIPs)){
            if($Authorization != ''){
                $now = new \DateTime("now",new \DateTimeZone("America/Bogota"));
                $Authorization = explode("Bearer ",$Authorization)[1];
                
                try{
                    $data = $this->get('lexik_jwt_authentication.encoder')->decode($Authorization);
                }catch(\Exception $er){
                    $response[0] = [
                        "code"=>401,
                        "message"=>$er->getMessage(),
                        "error"=>true
                    ];
                    return $response;
                }
                
                $pagos = $this->em->getRepository('App:Pagos')
                ->createQueryBuilder("P")
                ->select([
                    "P.idPago,
                    P.vlrPago,
                    P.saldo,
                    P.fechaHoraPago,
                    P.banco,
                    P.fechaConsignacion,
                    P.nroConsignacion,
                    P.nroCheque,
                    P.observaciones,
                    M.metodoPago,
                    TP.tipoPago,
                    F.idFactura,
                    T.nroTransaccion,
                    T.codigoSeguridad"
                ])
                ->JOIN("App:Transacciones","T","WITH","P.idTransaccion=T.idTransaccion")
                ->JOIN("App:Facturas","F","WITH","P.idFactura=F.idFactura")
                ->JOIN("App:MetodosPago","M","WITH","P.idMetodoPago=M.idMetodoPago")
                ->JOIN("App:TipoPagos","TP","WITH","TP.idTipoPago=P.idTipoPago")
                ->where("F.idEmpresa = :idEmp")
                ->andWhere("P.isDeleted = 0")
                ->setParameter("idEmp",$empresa[0]->getIdEmpresa());

                //Filtros
                if(isset($args["anioFacturado"])){
                    $pagos->andWhere("F.anioFacturado = :anio")
                    ->setParameter("anio",$args["anioFacturado"]);
                }

                if(isset($args["mesFacturado"])){
                    $pagos->andWhere("F.mesFacturado = :mes")
                    ->setParameter("mes",$args["mesFacturado"]);
                }

                if(isset($args["fechaInicio"]) && isset($args["fechaFinal"])){
                    $pagos->andWhere("DATE(P.fechaHoraPago) >= :inicio")
                    ->andWhere("DATE(P.fechaHoraPago) <= :fin")
                    ->setParameter("inicio",$args["fechaInicio"])
                    ->setParameter("fin",$args["fechaFinal"]);
                }

                if(isset($args["nroTransaccion"])){
                    $pagos->andWhere("T.nroTransaccion = :nroTrans")
                    ->setParameter("nroTrans",$args["nroTransaccion"]);
                }

                if(isset($args["nroFactura"])){
                    $pagos->andWhere("F.nroFactura = :nro")
                    ->setParameter("nro",$args["nroFactura"]);
                }

                if(isset($args["matricula"])){
                    $pagos->andWhere("F.matricula = :niu")
                    ->setParameter("niu",$args["matricula"]);
                }

                if(isset($args["orden"])){
                    $pagos->orderBy("P.fechaHoraPago",strtoupper($args["orden"]));
                }

                //Para el conteo de paginación. No se le aplica limite si existe.
                $RegistrosPagosTotales = $pagos->getQuery()->getArrayResult();
                
                //Limites para la paginación
                $pagos->setFirstResult($limiteInicio);
                $pagos->setMaxResults($limite);

                //Fin Filtros
                
                $payments = $pagos->getQuery()->getArrayResult();

                if(count($payments) > 0){
                    $totalPaginas = ceil((count($RegistrosPagosTotales) / $limite));
                    $totalRegistros = count($RegistrosPagosTotales);
                    foreach($payments as $index=>$valor){
                        $payments[$index]["code"] = 200;
                        $payments[$index]["message"] = "Done";
                        $payments[$index]["error"] = false;
                        $fact = $this->em->getRepository("App:Facturas")->findBy([
                            "idFactura"=>$valor["idFactura"]
                        ]);
                        $payments[$index]["fechaHoraPago"] = $valor["fechaHoraPago"]->format("Y-m-d H:i:s");
                        $payments[$index]["valorPago"] = $valor["vlrPago"];
                        $payments[$index]["saldo"] = $valor["saldo"];
                        if(!is_null($valor["fechaConsignacion"])){
                            $payments[$index]["fechaConsignacion"] = $valor["fechaConsignacion"]->format("Y-m-d H:i:s");
                        }
                        
                        $payments[$index]["facturas"] = $fact;

                        //Paginación.
                        $payments[$index]["registro"] = ($index+1)+$limiteInicio;
                        $payments[$index]["totalRegistros"] = $totalRegistros;
                        $payments[$index]["pagina"] = $pag;
                        $payments[$index]["totalPaginas"] = $totalPaginas;
                    }
                    
                    return $payments;
                }else{
                    $response[0] = [
                        "code"=>406,
                        "message"=>"No hay pagos registrados en el sistema de acuerdo a los filtros de búsqueda.",
                        "error"=>true
                    ];
                    return $response;
                }
            }else{
                $response[0] = [
                    "code"=>400,
                    //"message"=>"El Header de la Petición debe contener el atributo 'Authorization' con el token de autorización.",
                    "message"=>"La Petición debe contener el atributo 'Authorization' con el token de autorización.",
                    "error"=>true
                ];
                return $response;
            }
        }else{
            $response[0] = [
                "code"=>403,
                "message"=>"Su dirección IP(".$host.") no esta autorizada a conectarse a este servicio.",
                "error"=>true
            ];
            return $response;
        }
    }

    public static function getAliases()
    {
        return [
            'resolve' => 'Pagos'
        ];
    }
}