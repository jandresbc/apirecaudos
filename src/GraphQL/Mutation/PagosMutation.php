<?php

namespace App\GraphQL\Mutation;

use Symfony\Component\HttpFoundation\Request;

use App\Controller\ServicesController;
use App\Entity\Pagos;
use App\Entity\Transacciones;

use App\Blockchain\Block;
use App\Blockchain\Blockchain;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

use Overblog\GraphQLBundle\Error\UserError;
use Overblog\GraphQLBundle\Error\UserWarning;

class PagosMutation extends AbstractController implements MutationInterface, AliasedInterface {

    private $em;
    private $headers;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        //$this->headers = getallheaders();
        $this->headers = $_REQUEST;
        $this->request = Request::createFromGlobals();
    }

    public function registrarPago(Argument $args)
    {
        $em = $this->em;
        $main = new ServicesController($em);
        $blockchain = new Blockchain($em);

        $host = $this->request->headers->get("host");
        $Authorization = $this->request->query->get("Authorization");

        if($Authorization != ''){
            $facturas = explode(",",$args["idFacturas"]);
            $response = null;

            $Authorization = explode("Bearer ",$Authorization)[1];
            
            try{
                $data = $this->get('lexik_jwt_authentication.encoder')->decode($Authorization);
            }catch(\Exception $er){
                $response = [
                    "code"=>401,
                    "message"=>$er->getMessage(),
                    "error"=>true
                ];
                return $response;
            }
            
            $dataToken = $data["data"];
            
            //Información de la empresa a la que pertenece el usuario que esta logueado.
            $emp = $em->getRepository("App:Empresas")->findBy([
                "nit"=>$dataToken->nitEmpresa
            ]);
            
            $main->logRequest("registroPagosAPI",[$args["idFacturas"],$args["fechaHoraPago"],$args["idCaja"],$args["idUsuario"]],$emp[0]->getIdEmpresa());
            
            $conf = $main->ConfigappAction($emp[0]->getIdEmpresa());
            $parametros = json_decode($conf->getContent());
            $EnabledIPs = explode(",",$parametros->enabledIPs);
            
            $host = explode(":",$host)[0];
            
            if(in_array($host,$EnabledIPs)){
                $valorFacturas = 0;
                // Función de auditoria y rastreo del uso del API. En la librería ServicesController.php
                // print_r(json_encode($this->headers,JSON_FORCE_OBJECT));

                foreach ($facturas as $key => $value) {
                    //Información de la factura que se desea registrar el pago.
                    $fact = $em->getRepository("App:Facturas")->findBy([
                        "idFactura"=>$value,
                        "periodoActual"=>1//La factura debe estar habilitada dentro del periodo de facturación actual.
                    ]);
                    
                    if(count($fact) > 0){
                        $now = new \DateTime("now",new \DateTimeZone("America/Bogota"));
                        //Validación fecha de vencimiento de la factura.
                        if($now > $fact[0]->getFechaVencimiento()){
                            $response = [
                                "code"=>406,
                                "message"=>"La factura ".$fact[0]->getNroFactura()." se encuentra vencida.",
                                "error"=>true
                            ];
                            return $response;
                        }

                        $valorFacturas += $fact[0]->getValorFactura();

                        //Buscar si la factura ya tiene un pago registrado.
                        $pagos = $this->em->getRepository("App:Pagos")->findBy([
                            "idFactura"=>$fact[0]->getIdFactura()
                        ]);
                        
                        if(count($pagos) > 0){
                            $response = [
                                "code"=>406,
                                "message"=>"La factura ".$fact[0]->getNroFactura()." tiene un pago registrado en el sistema.",
                                "error"=>true
                            ];
                            return $response;
                        }
                    }else{
                        $response = [
                            "code"=>406,
                            "message"=>"No se encuentra la factura ha consultar. Las posibles causas es que esta factura haya sido desactivada para su recaudo o ya haya sido pagada en otro punto de recaudo.",
                            "error"=>true
                        ];
                        return $response; 
                    }
                }

                //consulta el usuario con idUsuario pasado en los argumentos.
                $usuarioActual = $em->getRepository("App:Usuarios")
                ->findBy(["idUsuario"=>$args["idUsuario"]]);

                //consulta la caja con el idCaja y el idUsuario enviado desde los argumentos de la petición
                $cajaUsuario = $em->getRepository("App:Cajas")->findBy([
                    "idUsuario"=>$usuarioActual[0]->getIdUsuario(),
                    "idCaja"=>$args['idCaja']
                ]);

                //Consulta el id de la EmpresasSedesAgencias
                $empSedesAgencias = $em->getRepository("App:EmpresasSedesAgencias")
                ->findBy([
                    "idEmpresaSedeAgencia"=>$cajaUsuario[0]->getIdEmpresaSedeAgencia()
                ]);
                
                $em->getConnection()->beginTransaction();

                try{
                    $facturasPagadas = [];
                    $transacciones = new Transacciones();
                    $codigoSeguridad = $main->getCodeSecurity([
                        "fechaTransaccion"=>$args['fechaHoraPago'],
                        "totalTransaccion"=>$valorFacturas,
                        "idUsuario"=>trim($usuarioActual[0]->getIdUsuario()),
                        "idCaja"=>$args['idCaja'],
                        "idEmpresaSedeAgencia" => trim($empSedesAgencias[0]->getIdEmpresaSedeAgencia())
                    ]);

                    $codigoTransaccion = $main->getNroTransaction(7);

                    $transacciones->setNroTransaccion($codigoTransaccion);
                    $transacciones->setFechaHoraTransaccion(new \Datetime($args['fechaHoraPago']));
                    $transacciones->setCodigoSeguridad($codigoSeguridad);
                    $transacciones->setTotalTransaccion($valorFacturas);
                    $transacciones->setIdUsuario($usuarioActual[0]);
                    $transacciones->setIdCaja($cajaUsuario[0]);
                    $transacciones->setIdEmpresaSedeAgencia($empSedesAgencias[0]);

                    $em->persist($transacciones);
                    $em->flush($transacciones);

                    // Auditoria agrega un bloque a la cadena.
                    $dataAudTrans = [
                        "accion"=>"Insert",
                        "tabla"=>"transacciones",
                        "id_datos"=>$transacciones->getIdTransaccion(),
                        "data"=>$transacciones->getArrayData()
                    ];

                    $blockchain->addBlock(new Block($dataAudTrans));
                    
                    //Recorre las facturas para guardar los pagos.
                    foreach ($facturas as $key => $value) {
                        $pagos = new Pagos();

                        //Información de la factura que se desea registrar el pago.
                        $factura = $em->getRepository("App:Facturas")->findBy([
                            "idFactura"=>$value,
                            "periodoActual"=>1//La factura debe estar habilitada dentro del periodo de facturación actual.
                        ]);
                        
                        $pagos->setFechaHoraPago(new \Datetime($args['fechaHoraPago']));
                        $pagos->setVlrPago($factura[0]->getValorFactura());

                        //Segunda consulta de la factura, pero por su número de
                        //matricula, para determinar si este pago es un abono a una
                        //factura y calcular su saldo.
                        $facturaMatriculaAnt = $em->getRepository("App:Facturas")
                        ->createQueryBuilder("F")
                        ->where("F.nroFactura = :nroFact")
                        ->andWhere("F.matricula = :matricula")
                        ->andWhere("F.idEmpresa = :idEmp")
                        ->andWhere("F.mesFacturado = :mesFact")
                        ->andWhere("F.anioFacturado = :anioFact")
                        ->setParameter("nroFact",$factura[0]->getNroFactura())
                        ->setParameter("matricula",$factura[0]->getMatricula())
                        ->setParameter("idEmp",$factura[0]->getIdEmpresa()->getIdEmpresa())
                        ->setParameter("mesFact",$factura[0]->getMesFacturado())
                        ->setParameter("anioFact",$factura[0]->getAnioFacturado())
                        ->getQuery();

                        $facturaAnt = $facturaMatriculaAnt->getResult();
                        $facturaAntArray = $facturaMatriculaAnt->getArrayResult();

                        //Existe la misma factura con otro valor en el sistema.
                        //Solo entra cuando se encuentra registros anteriores superiores a 1
                        //con lo que se comprueba que hay historial para calcular saldos.
                        if(count($facturaAnt) > 1){
                            $valorAnterior = 0;
                            $pos = null;
                            //Proceso para determinar la posicion de la facturaAnterior.
                            foreach ($facturaAntArray as $k => $v) {
                                if($factura[0]->getValorFactura() === $v['valorFactura']){
                                    $pos = $k;
                                }
                            }
                            $len = ($pos-1);
                            //Fin proceso de la posición de la facturaAnterior.

                            //Consulta del saldo anterior
                            $PagosSaldo = $em->getRepository("App:Pagos")->findBy([
                                "idFactura" => $facturaAnt[$len]->getIdFactura()
                            ]);

                            //Si existe un saldo anterior.
                            if(count($PagosSaldo) > 0){
                                $lenPagos = (count($PagosSaldo)-1);
                                if($PagosSaldo[$lenPagos]->getSaldo() > 0 || $PagosSaldo[$lenPagos]->getSaldo() != null){
                                    $valorAnterior = $PagosSaldo[$lenPagos]->getSaldo();
                                }
                            }else{
                                $valorAnterior = $facturaAnt[$len]->getValorFactura();
                            }

                            //A la factura anterior se le resta el valor de la factura actual.
                            $saldo = $valorAnterior - $factura[0]->getValorFactura();
                            $pagos->setSaldo($saldo);

                            //Valida positivos y negativos
                            if($saldo > 0){//Positivo
                                //El tipo de pago es un parcial-abono.

                                //Busca el tipo pago con el id 2 = Parcial - Abono
                                $tipoPago = $em->getRepository("App:TipoPagos")->findBy(["idTipoPago"=>2]);
                                //Setea el id del tipo de pago.
                                $pagos->setIdTipoPago($tipoPago[0]);
                            }else if($saldo < 0){//Negativo
                                //El tipo de pago es un Avance

                                //Busca el tipo pago con el id 3 = Avance
                                $tipoPago = $em->getRepository("App:TipoPagos")->findBy(["idTipoPago"=>3]);
                                //Setea el id del tipo de pago.
                                $pagos->setIdTipoPago($tipoPago[0]);
                            }else if($saldo == 0){//Si es 0, es que se hizo un pago completo. se setea como total.
                                //Busca el tipo pago con el id 1 = Total
                                $tipoPago = $em->getRepository("App:TipoPagos")->findBy(["idTipoPago"=>1]);
                                //Setea el id del tipo de pago.
                                $pagos->setIdTipoPago($tipoPago[0]);
                            }
                        }else{
                            //Busca el tipo pago con el id 1 = Total
                            $tipoPago = $em->getRepository("App:TipoPagos")->findBy(["idTipoPago"=>1]);
                            //Setea el id del tipo de pago.
                            $pagos->setIdTipoPago($tipoPago[0]);
                        }//Fin Abonos y Saldos

                        $pagos->setIdFactura($factura[0]);

                        $pagos->setIdTransaccion($transacciones);

                        //consulta el metodo de pago para ser seteado. Efectivo permitido solamente por el API.
                        $metodoPago = $em->getRepository("App:MetodosPago")
                        ->findBy(["idMetodoPago"=>1 /*ID Efectivo*/]);

                        $pagos->setIdMetodoPago($metodoPago[0]);

                        //Proceso de guardado.
                        $em->persist($pagos);
                        $em->flush($pagos);

                        //Auditoria agrega un bloque a la cadena.
                        $dataAudPagos = [
                            "accion"=>"Insert",
                            "tabla"=>"pagos",
                            "id_datos"=>$pagos->getIdPago(),
                            "data"=>$pagos->getArrayData()
                        ];

                        $blockchain->addBlock(new Block($dataAudPagos));

                        //Modifica la factura, para cambiar el periodo Actual
                        //a 0 una vez se registre los pagos de las facturas.
                        $factura[0]->setPeriodoActual(0);
                        $em->flush($factura[0]);

                        //Auditoria agrega un bloque a la cadena.
                        $dataAudFact = [
                            "accion"=>"Update",
                            "tabla"=>"facturas",
                            "id_datos"=>$factura[0]->getIdFactura(),
                            "data"=>["periodo_actual = 0"]
                        ];

                        $blockchain->addBlock(new Block($dataAudFact));

                        //Existe la misma factura con otro valor en el sistema.
                        if(count($facturaAnt) > 1){
                            //Modifica la facturaAnterior, para cambiar el periodo Actual
                            //a 0 una vez se registre los pagos de las facturas.
                            $facturaAnt[$len]->setPeriodoActual(0);
                            $em->flush($facturaAnt[$len]);

                            //Auditoria agrega un bloque a la cadena.
                            $dataAudFactAnt = [
                                "accion"=>"Update",
                                "tabla"=>"facturas",
                                "id_datos"=>$facturaAnt[$len]->getIdFactura(),
                                "data"=>["periodo_actual = 0"]
                            ];

                            $blockchain->addBlock(new Block($dataAudFactAnt));
                        }

                        //Se agrupan las facturas pagadas para que sean retornadas en el API.
                        array_push($facturasPagadas, $factura[0]);

                    }//Foreach de facturas para registrar los pagos.

                    //Registra la transacción y los pagos.
                    $em->getConnection()->commit();
                    
                    //Registra los bloques de la cadena en la bd.
                    $blockchain->registerChain(
                        $empSedesAgencias[0]->getIdEmpresa()->getIdEmpresa(),
                        $empSedesAgencias[0]->getIdSedeAgencia()->getIdSedeAgencia(),
                        $usuarioActual[0]->getIdUsuario()
                    );
                    
                    $response = [
                        "code"=>200,
                        "message"=>"Done",
                        "error"=>false,
                        "idPago"=>$pagos->getIdPago(),
                        "fechaHoraPago"=>$pagos->getFechaHoraPago()->format("Y-m-d H:i:s"),
                        "valorTotalPagado"=>$valorFacturas,
                        "codigoTransaccion"=>$codigoTransaccion,
                        "codigoSeguridad"=>$codigoSeguridad,
                        "facturasPagadas"=>$facturasPagadas
                    ];
                }catch(\Exception $e){
                    $blockchain->chain = [];//Reinicializa la cadena en caso de error.
                    $em->getConnection()->rollback();
                    
                    $response = [
                        "code" => 406,
                        "error" => true,
                        "message" => $e->getMessage()
                    ];
                    return $response;
                }
            }else{
                $response = [
                    "code" => 403,
                    "error" => true,
                    "message" => "Su dirección IP(".$host.") no esta autorizada a conectarse a este servicio."
                ];
            }
        }else{
            $response = [
                "code"=>400,
                //"message"=>"El Header de la Petición debe contener el atributo 'Authorization' con el token de autorización.",
                "message"=>"La Petición debe contener el atributo 'Authorization' con el token de autorización.",
                "error"=>true
            ];
        }

        return $response;
    }

    public static function getAliases()
    {
        return [
            'registrarPago' => 'registrarPago'
        ];
    }
}
