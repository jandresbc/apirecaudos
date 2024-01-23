<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Transacciones;
use App\Entity\Pagos;
use App\Entity\LogApi;

//Auditoria.
//use App\Blockchain\Block;
//use App\Blockchain\Blockchain;

class ServicesController extends Controller
{

    /**
     *
     * @var EntityManager
     */
    protected $em;
    private $_tokenpass = "QWe89..//&&";

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getInfoUser($nombreUsuario,$em){
        $session = new Session();
        //Obtiene la información del usuario que sea un usuario Inactivo
        //y que el funcionario este activo para poder obtener su información.

        //JOIN App:EmpresasSedesAgencias ESA WITH U.idEmpresaSedeAgencia = ESA.idEmpresaSedeAgencia
        //JOIN App:Empresas E WITH ESA.idEmpresa = E.idEmpresa
        $query = $em->createQuery(
          "SELECT U, GU
          FROM App:Usuarios U
          JOIN App:GruposUsuarios GU WITH U.idGrupoUsuario = GU.idGrupoUsuario
          JOIN App:SedesAgencias SA WITH U.idSedeAgencia = SA.idSedeAgencia
          JOIN App:Agencias A WITH A.idAgencia = SA.idAgencia
          WHERE U.identificacion = :nombreUs
          AND GU.grupoUsuario <> 'Inactivo'
          AND U.activo <> 0
          AND A.inactiva = 0
          AND SA.inactiva = 0
          ORDER BY U.identificacion ASC"
        )->setParameter('nombreUs',$nombreUsuario);

        return $products = $query->getResult();
    }

    //Función que sirve para validar si existe la relación
    //entre un cliente y un contrato.
    public function encryptPass($pass)
    {
        $encrypt = md5($this->_tokenpass.$pass.$this->_tokenpass);

        return $encrypt;
    }

    //Retorna la configuración del sistema.
    public function ConfigappAction($idEmpresa = null)
    {
        //Session
        $session = new Session();
        $json = "";

        if($idEmpresa != null){
          $conf = $this->em->getRepository("App:Parametros")->findBy(
            array("idEmpresa"=>$idEmpresa)
          );

          if(count($conf) > 0){
            $json = array(
              "status" => 1,
              "urllogoempresa" => $conf[0]->getUrlLogoEmpresa(),
              "nombreEmpresa" => $conf[0]->getIdEmpresa()->getRazonSocial(),
              "headerInformes" => $conf[0]->getHeaderInformes(),
              "porcentajeMetaRecaudo"=>$conf[0]->getPorcentajeMetaRecaudo(),
              "enabledIPs"=>$conf[0]->getEnabledIPs()
            );
          }else{//Cuando no hay ningún registro en la bd de un abono de un contrato
            $json = array(
              "status" => 0,
              "error" => "<b>NO</b> hay una configuración registrada en el sistema para la actual empresa."
            );
          }

          return new Response(json_encode($json));
        }else{
          $conf = [];
          $idEmp = $this->em->getRepository("App:EmpresasSedesAgencias")->findBy([
            "idSedeAgencia"=>$session->get("idSedeAgencia")
          ]);

          foreach($idEmp as $value){
            $config = $this->em->getRepository("App:Parametros")->findBy(
              array("idEmpresa"=>$value->getIdEmpresa()->getIdEmpresa())
            );

            array_push($conf,$config[0]);
          }

          return $conf;
        }


    }

    //Services de facturación.
    public function getFacturaAction(Request $request,$nroFactura,$idCaja){
      //Instancias
      $em = $this->em;
      $session = $request->getSession();
      $valorFactura = $request->query->get("valorFactura");
      $blockchain = new Blockchain($em);

      //Con la caja que se inicio session se consulta la empresa a la cual
      //pertenece para así consultar su facturacion.
      $caja = $em->getRepository("App:Cajas")
      ->findBy(array("idCaja"=>$idCaja));

      $json = [];
      $filtrosFacturas = [
        "nroFactura"=>$nroFactura,
        "idEmpresa"=>$caja[0]->getIdEmpresaSedeAgencia()->getIdEmpresa()->getIdEmpresa(),
        "periodoActual"=>1//Solo buscar facturas que sean del periodo de facturación Actual.
      ];

      //Filtros consulta factura.
      if($valorFactura != '' && $valorFactura != null){
        $filtrosFacturas["valorFactura"]=$valorFactura;
      }else if($valorFactura == '' || $valorFactura == null){

      }

      $factura = $em->getRepository("App:Facturas")->findBy(
        $filtrosFacturas
      );

      if(count($factura) > 0){
        unset($filtrosFacturas['valorFactura']);
        $facturasNroFactura = $em->getRepository("App:Facturas")->findBy(
          $filtrosFacturas
        );
        foreach ($factura as $key => $value) {
          $rt = null;
          if($value->getIsAbono() == 1){ //La factura es un Abono.(Parcial, Avance)
            $rt = $this->getFacturaAbonoActual($facturasNroFactura,$value);
            if($rt === true){
              //Auditoria agrega un bloque a la cadena.
              $dataAud = [
                "accion"=>"Select",
                "tabla"=>"facturas",
                "id_datos"=>$value->getIdFactura(),
                "data"=>$filtrosFacturas
              ];

              $blockchain->addBlock(new Block($dataAud));

              $json[0] = array(
                "status" => 1,
                "nroFactura" => $value->getNroFactura(),
                "fechaVencimiento" => $value->getFechaVencimiento()->format("Y/m/d H:i:s"),
                "nombreUsuario" => $value->getNombreUsuario(),
                "valorFactura" => $value->getValorFactura(),
                "concepto" => $value->getConcepto()
              );
              break;
            }else if($rt === false){ //Hay otra factura próxima a vencerse.
              $json[0] = array(
                "status" => 0,
                "error" => "<b>No se puede continuar</b>. Porque hay otra factura próxima a vencerce."
              );
              break;
            }
          }else if($value->getIsAbono() == 0){ //Facturas que no son abonos.(Total)
            //Auditoria agrega un bloque a la cadena.
            $dataAud = [
              "accion"=>"Select",
              "tabla"=>"facturas",
              "id_datos"=>$value->getIdFactura(),
              "data"=>$filtrosFacturas
            ];

            $blockchain->addBlock(new Block($dataAud));

            $json[$key] = array(
              "status" => 1,
              "nroFactura" => $value->getNroFactura(),
              "fechaVencimiento" => $value->getFechaVencimiento()->format("Y/m/d H:i:s"),
              "nombreUsuario" => $value->getNombreUsuario(),
              "valorFactura" => $value->getValorFactura(),
              "concepto" => $value->getConcepto()
            );
          }
        }

        $blockchain->registerChain();
      }else{
        $json = array(
          "status" => 0,
          "error" => "La factura <b>NO</b> se encuentra registrada en el sistema o es posible que tenga un pago registrado. Para saber si hay un pago registrado de esta factura, diríjase al menú Consultas/Transacciones y digite el número de la factura; si hay un pago registrado mostrará la información de ese pago."
        );
      }

      return new JsonResponse($json);
    }

    // Obtiene la factura que es Abono con la fecha de vencimiento próxima a vencerse.
    public function getFacturaAbonoActual($datos,$facturaActual){
      $diferencias = [];
      $now = new \DateTime("now",new \DateTimeZone('America/Bogota'));
      $diffFacturaActual = $now->diff($facturaActual->getFechaVencimiento());

      foreach ($datos as $key => $value) {
        if($value->getIsAbono() == 1){//Solo continuo si es un abono.
          $diff = $now->diff($value->getFechaVencimiento());
          array_push($diferencias,$diff->format('%R%a'));
        }
      }
      //Se ordena de menor a mayor.
      sort($diferencias);

      if($diffFacturaActual->format('%R%a') == $diferencias[0]){
        return true;
      }else{
        return false;
      }
    }

    //Servicio de guardado de las transacciones y sus pagos.
    public function saveTransactionAction(Request $request){
        //Instancias
        $em = $this->em;
        $session = new Session();
        $blockchain = new Blockchain($em);
        $transacciones = new Transacciones();
        $response = null;

        $content = $request->getContent();

        //decodifico el string json y lo convierto en un
        //array asociativo con la segundo parametro de json_decode.
        $json = json_decode($content,true);

        //consulta el usuario con idUsuario de la session.
        $usuarioActual = $em->getRepository("App:Usuarios")
        ->findBy(array("idUsuario"=>$session->get('idUsuario')));

        //consulta la caja con el idCaja enviado desde $http.
        //y el usuario actual.
        $cajaUsuario = $em->getRepository("App:Cajas")
        ->findBy(array(
          "idUsuario"=>$session->get('idUsuario'),
          "idCaja"=>$json['idCajaActual']
        ));

        //Consulta el id de la EmpresasSedesAgencias de acuerdo
        //al idEmpresa de la Session y el idSedeAgencia del Usuario Actual.
        $empSedesAgencias = $em->getRepository("App:EmpresasSedesAgencias")
        ->findBy(array(
          "idEmpresaSedeAgencia"=>$cajaUsuario[0]->getIdEmpresaSedeAgencia()
        ));
        //Validación NroConsignación.
        //El registo del pago y transacción puede continuar ya que la consignación
        //es única y no existe en la bd. Si existe esta variable cambia a
        //false y retorna un error.
        // $hasconsignacion = true;
        //
        // if(isset($json['threads']['pagos']['nroConsignacion'])){
          //Validación para determinar que no exista el mismo nro de consignación
          //ya registrada en la base de datos.
        //   $hasConsigFactura = $em->getRepository("App:Pagos")
        //   ->createQueryBuilder("P")
        //   ->join("App:Facturas","F","WITH","F.idFactura = P.idFactura")
        //   ->where("P.nroConsignacion = :nroConsig")
        //   ->andWhere("F.idEmpresa = :idEmp")
        //   ->andWhere("F.periodoActual = 0")
        //   ->setParameter("nroConsig",$json['threads']['pagos']["nroConsignacion"])
        //   ->setParameter("idEmp",$cajaUsuario[0]->getIdEmpresaSedeAgencia()->getIdEmpresa()->getIdEmpresa())
        //   ->getQuery()->getResult();
        //
        //   if(count($hasConsigFactura) > 0){
        //     $hasconsignacion = false;
        //   }
        // }
        //Fin Validación.

        // if($hasconsignacion == true){
          //Inicia la transacción.
          $em->getConnection()->beginTransaction();

          try{
            $codigoSeguridad = $this->getCodeSecurity(array(
              "fechaTransaccion"=>$json['fechaTransaccion'],
              "totalTransaccion"=>$json['threads']['pagos']['totalAPagar'],
              "idUsuario"=>trim($session->get('idUsuario')),
              "idCaja"=>$json['idCajaActual'],
              "idEmpresaSedeAgencia" => trim($empSedesAgencias[0]->getIdEmpresaSedeAgencia())
            ));

            $codigoTransaccion = $this->getNroTransaction(7);

            $transacciones->setNroTransaccion($codigoTransaccion);
            $transacciones->setFechaHoraTransaccion(new \Datetime($json['fechaTransaccion']));
            $transacciones->setCodigoSeguridad($codigoSeguridad);
            $transacciones->setTotalTransaccion($json['threads']['pagos']['totalAPagar']);
            $transacciones->setIdUsuario($usuarioActual[0]);
            $transacciones->setIdCaja($cajaUsuario[0]);
            $transacciones->setIdEmpresaSedeAgencia($empSedesAgencias[0]);

            $em->persist($transacciones);
            $em->flush($transacciones);

            //Auditoria agrega un bloque a la cadena.
            $dataAudTrans = [
              "accion"=>"Insert",
              "tabla"=>"transacciones",
              "id_datos"=>$transacciones->getIdTransaccion(),
              "data"=>$transacciones->getArrayData()
            ];

            $blockchain->addBlock(new Block($dataAudTrans));

            foreach ($json['threads']['pagos']['facturas'] as $key => $value) {
              $pagos = new Pagos();

              $pagos->setFechaHoraPago(new \Datetime($json['fechaTransaccion']));
              $pagos->setVlrPago($value['valorFactura']);

              //consulta de la factura.
              $factura = $em->getRepository("App:Facturas")
              ->findBy(array(
                "nroFactura"=>$value['nroFactura'],
                "valorFactura"=>$value['valorFactura'],
                "idEmpresa"=>$cajaUsuario[0]->getIdEmpresaSedeAgencia()->getIdEmpresa()->getIdEmpresa(),
                "periodoActual"=>1//Que esté dentro del periodo de facturación
              ));

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
                $PagosSaldo = $em->getRepository("App:Pagos")
                ->findBy(
                  array(
                    "idFactura" => $facturaAnt[$len]->getIdFactura()
                  )
                );

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
                  $tipoPago = $em->getRepository("App:TipoPagos")->findBy(
                    array("idTipoPago"=>2)
                  );
                  //Setea el id del tipo de pago.
                  $pagos->setIdTipoPago($tipoPago[0]);
                }else if($saldo < 0){//Negativo
                  //El tipo de pago es un Avance

                  //Busca el tipo pago con el id 3 = Avance
                  $tipoPago = $em->getRepository("App:TipoPagos")->findBy(
                    array("idTipoPago"=>3)
                  );
                  //Setea el id del tipo de pago.
                  $pagos->setIdTipoPago($tipoPago[0]);
                }else if($saldo == 0){//Si es 0, es que se hizo un pago completo. se setea como total.
                  //Busca el tipo pago con el id 1 = Total
                  $tipoPago = $em->getRepository("App:TipoPagos")->findBy(
                    array("idTipoPago"=>1)
                  );
                  //Setea el id del tipo de pago.
                  $pagos->setIdTipoPago($tipoPago[0]);
                }
              }else{
                //Busca el tipo pago con el id 1 = Total
                $tipoPago = $em->getRepository("App:TipoPagos")->findBy(
                  array("idTipoPago"=>1)
                );
                //Setea el id del tipo de pago.
                $pagos->setIdTipoPago($tipoPago[0]);
              }//Fin Abonos y Saldos

              $pagos->setIdFactura($factura[0]);

              //consulta de la transaccion recien guardada.
              $transaccionAnt = $em->getRepository("App:Transacciones")
              ->findBy(array(
                "idTransaccion"=>$transacciones->getIdTransaccion(),
                "fechaHoraTransaccion"=>new \Datetime($json['fechaTransaccion']),
                "idUsuario"=>$session->get('idUsuario'),
                "idEmpresaSedeAgencia" => $empSedesAgencias[0]->getIdEmpresaSedeAgencia()
              ));

              $pagos->setIdTransaccion($transaccionAnt[0]);

              if(isset($json['threads']['pagos']['banco'])){
                $pagos->setBanco($json['threads']['pagos']['banco']);
              }

              if(isset($json['threads']['pagos']['fechaConsignacion'])){
                $pagos->setFechaConsignacion(new \Datetime($json['threads']['pagos']['fechaConsignacion'],new \DateTimeZone('America/Bogota')));
              }

              if(isset($json['threads']['pagos']['nroConsignacion'])){
                $pagos->setNroConsignacion($json['threads']['pagos']['nroConsignacion']);
              }

              if(isset($json['threads']['pagos']['nroCheque'])){
                $pagos->setNroCheque($json['threads']['pagos']['nroCheque']);
              }

              //consulta el metodo de pago para ser seteado.
              $metodoPago = $em->getRepository("App:MetodosPago")
              ->findBy(array(
                "metodoPago"=>ucwords($json['threads']['pagos']['metodoPago'])
              ));

              $pagos->setIdMetodoPago($metodoPago[0]);

              //Setea las observaciones en el pagos si estas existen.
              if(isset($json["threads"]["pagos"]["observaciones"])){
                $pagos->setObservaciones($json["threads"]["pagos"]["observaciones"]);
              }

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

            }//Foreach de facturas para registrar los pagos.

            //Registra la transacción.
            $em->getConnection()->commit();

            //Registra los bloques de la cadena en la bd.
            $blockchain->registerChain();

            //Genera el recibo de Pago.
            $pagosRegistrados = $em->getRepository("App:Pagos")
            ->createQueryBuilder("P")
            ->JOIN("App:Transacciones","T","WITH","P.idTransaccion = T.idTransaccion")
            ->JOIN("App:Facturas","F","WITH","P.idFactura = F.idFactura")
            ->where("T.idTransaccion = :idTran")
            ->andWhere("P.isDeleted = 0")
            ->setParameter("idTran",$transaccionAnt[0]->getIdTransaccion())
            ->getQuery()->getResult();

            if(!isset($json["pagosMasivos"])){
              $recibo = $this->renderView("@App/recaudos/reciboTransaccion.html.twig",[
                "codigoTransaccion" => $codigoTransaccion,
                "codigoSeguridad" => $codigoSeguridad,
                "transaccionActual" => $transaccionAnt,
                "pagos" => $pagosRegistrados
              ]);
            }
            //Fin generacion Recibo de Pago.

            //Auditoria agrega un bloque a la cadena.
            $blockchain2 = new Blockchain($em);
            $arrayIDPagos = [];
            $pagosReg = [];
            foreach ($pagosRegistrados as $key => $value) {
              array_push($arrayIDPagos,$value->getIdPago());
              array_push($pagosReg,[
                "nroFactura"=>$value->getIdFactura()->getNroFactura(),
                "codigoTransaccion"=>$value->getIdTransaccion()->getNroTransaccion()
              ]);
            }
            $dataAudPagosRecibo = [
              "accion"=>"Select",
              "tabla"=>"pagos",
              "id_datos"=>"Se generó recibos de pago de los IDs: ".implode(",",$arrayIDPagos),
              "data"=>[$transaccionAnt[0]->getIdTransaccion()]
            ];

            $blockchain2->addBlock(new Block($dataAudPagosRecibo));
            $blockchain2->registerChain();

            if(!isset($json["pagosMasivos"])){
              return new Response(
                json_encode(array(
                  "status"=>"Done",
                  "recibo"=>$recibo
                ))
              );
            }else{
              return new Response(
                json_encode(array(
                  "status"=>"Done",
                  "pagos"=>$pagosReg
                ))
              );
            }
          }catch(Exception $e){
            $blockchain->chain = [];//Reinicializa la cadena en caso de error.
            $em->getConnection()->rollback();
             throw $e;
          }
        // }else{
        //   return new Response(
        //     json_encode(array(
        //       "status"=>0,
        //       "error"=>"El número de la consignación <b>YA</b> se encuentra registrada en el sistema."
        //     ))
        //   );
        // }
    }

    //Obtiene el recibo de consignación de una transacción.
    public function getReciboAction($idTransaccion){
      $em = $this->getDoctrine()->getManager();

      $pagos = $em->getRepository("App:Pagos")
      ->createQueryBuilder("P")
      ->JOIN("App:Transacciones","T","WITH","P.idTransaccion = T.idTransaccion")
      ->JOIN("App:Facturas","F","WITH","P.idFactura = F.idFactura")
      ->where("T.idTransaccion = :idTran")
      ->andWhere("P.isDeleted = 0")//Trae los pagos activos. Que no han sido eliminados.
      ->setParameter("idTran",$idTransaccion)
      ->getQuery()->getResult();

      $transaccionAnt = $em->getRepository("App:Transacciones")
      ->findBy(array(
        "idTransaccion" => $idTransaccion
      ));

      $recibo = $this->renderView("@App/recaudos/reciboTransaccion.html.twig",array(
        "codigoTransaccion" => $transaccionAnt[0]->getNroTransaccion(),
        "codigoSeguridad" => $transaccionAnt[0]->getCodigoSeguridad(),
        "transaccionActual" => $transaccionAnt,
        "pagos" => $pagos
      ));

      return new Response($recibo);
    }

    public function getNroTransaction($longitud) {
       $key = '';
       $pattern = md5(time()).'1234567890abcdefghijklmnopqrstuvwxyz'.uniqid();
       $max = strlen($pattern)-1;
       for($i=0;$i < $longitud;$i++) $key .= $pattern{mt_rand(0,$max)};
       return $key;
    }

    public function getCodeSecurity($arrayTransaction){
      $privateKey = $this->_tokenpass;
      $string = '';

      foreach ($arrayTransaction as $key => $value) {
        $string .= $value;
      }

      $string = $privateKey.$string.$privateKey;

      return sha1($string);
    }

    //Valida de acuerdo al id de la transacción
    //el número de la trasacción el cual será un número único
    //y validable con el que se podrá comprobar la transacción
    public function hasCodeSecurityAction($idTransaction){
      //Instancias
      $session = new Session();
      $em = $this->getDoctrine()->getManager();

      $transaccion = $em->getRepository("App:Transacciones")
      ->findBy(["idTransaccion"=>$idTransaction]);

      if(count($transaccion) > 0){
        $json = [];
        $response = $this->getCodeSecurity(
          array(
            "fechaTransaccion"=>$transaccion[0]->getFechaHoraTransaccion()->format("Y-m-d H:i:s"),
            "totalTransaccion"=>$transaccion[0]->getTotalTransaccion(),
            "idUsuario"=>trim($transaccion[0]->getIdUsuario()->getIdUsuario()),
            "idCaja"=>$transaccion[0]->getIdCaja()->getIdCaja(),
            "idEmpresaSedeAgencia" => trim($transaccion[0]->getIdEmpresaSedeAgencia()->getIdEmpresaSedeAgencia())
          )
        );

        if($response == $transaccion[0]->getCodigoSeguridad()){
          $json = [
            "status"=>1,
            "mensaje"=>"¡Transacción Verificada!",
            "codigoSeguridad"=>$response
          ];
        }else if($response != $transaccion[0]->getCodigoSeguridad()){
          $json = [
            "status"=>0,
            "mensaje"=>"¡La Transacción no superó el proceso de Verificación!",
            "codigoSeguridad"=>$response
          ];
        }

        return new JsonResponse($json);
      }else{
        return new JsonResponse(
          [
            "status"=>0,
            "error"=>"El ID de la transacción no está registrada en al base de datos."
          ]
        );
      }
    }

    //Función para generar el número de documento
    //en el cierre de caja.
    public function getNroDocument($idCaja,$em){
      $session = new Session();
      $nro = null;
      $consecutivo = 0;
      $conseDocumento = '';
      $em = ($em == null || $em == '') ? $this->em : $em;

      $caja = $em->getRepository("App:Cajas")
      ->findBy(array("idCaja"=>$idCaja));

      $factura = $em->getRepository("App:Facturas")
      ->findBy(array(
          "idEmpresa"=>$caja[0]->getIdEmpresaSedeAgencia()->getIdEmpresa()->getIdEmpresa(),
          "periodoActual"=>1//Solo buscar facturas que sean del periodo de facturación Actual.
      ));

      if(count($factura) > 0){
          $anio = $factura[0]->getAnioFacturado();
          $mes = $factura[0]->getMesFacturado() <= 9 ? "0".$factura[0]->getMesFacturado() : $factura[0]->getMesFacturado();
      }else{
          $fechaNow = new \DateTime("now",new \DateTimeZone('America/Bogota'));
          $anio = $fechaNow->format("Y");
          $month = $fechaNow->format("m")-1;
          $mes = $month <= 9 ? "0".$month : $month;
      }
      $nro = $anio.".".$mes.".";

      $idAgencia = $em->getRepository("App:Cajas")
      ->createQueryBuilder("C")
      ->select("SA")
      ->join("App:EmpresasSedesAgencias","ESA","WITH","ESA.idEmpresaSedeAgencia = C.idEmpresaSedeAgencia")
      ->join("App:SedesAgencias","SA","WITH","SA.idSedeAgencia = ESA.idSedeAgencia")
      ->where("C.idCaja = :idCaja")
      ->andWhere("ESA.idEmpresa = :idEmp")
      ->andWhere("C.idUsuario = :idUsu")
      ->setParameter("idCaja",$idCaja)
      ->setParameter("idUsu",$session->get('idUsuario'))
      ->setParameter("idEmp",$caja[0]->getIdEmpresaSedeAgencia()->getIdEmpresa()->getIdEmpresa())
      ->getQuery()->getResult();

      //Código de la Sede de la Agencia Recaudadora
      $idAgen = $idAgencia[0]->getIdSedeAgencia();
      $nro .= $idAgen <= 9 ? "0".$idAgen : $idAgen;

      $dql = "SELECT CDC.nroDocumento FROM App:CierresDeCajas CDC WHERE CDC.idCierreCaja = (SELECT MAX(CDC2.idCierreCaja) as documento FROM App:CierresDeCajas CDC2)";
      /*WHERE CDC.idCaja = :idCaja";*/

      $cierres = $em->createQuery($dql)
      //->setParameter("idCaja",$idCaja)
      ->getResult();

      if(isset($cierres[0]) && $cierres[0]['nroDocumento'] != '' && $cierres[0]['nroDocumento'] != null){
        $cons = explode(".",$cierres[0]['nroDocumento']);
        $conseDocumento .= ($cons[(count($cons)-1)]+1);
      }else{
        $conseDocumento .= ($consecutivo+1);
      }

      $conseDocumento = $conseDocumento <= 9 ? "0".$conseDocumento : $conseDocumento;
      $nro = $nro.".".$conseDocumento;

      return $nro;
    }

    //Calcula el total recaudado en una determinada caja.
    public function totalRecaudado($idCaja,$em){
      $session = new Session();
      $totalRecaudo = 0;
      //consulta de la transaccion por el idCaja para determinar
      //el total de las transacciones realizadas para efectuar el cierre de caja.
      $pagos = $em->getRepository("App:Pagos")
      ->createQueryBuilder("P")
      ->JOIN("App:Transacciones","T","WITH","P.idTransaccion = T.idTransaccion")
      ->where("P.isDeleted = 0")//El Pago esta Activo.
      ->andWhere("T.isClosed = 0")
      ->andWhere("T.idUsuario = :idUs")
      ->andWhere("T.idCaja = :idCaja")
      ->setParameter("idUs",$session->get('idUsuario'))
      ->setParameter("idCaja",$idCaja)
      ->getQuery()->getResult();

      foreach ($pagos as $key => $value) {
        $totalRecaudo += $value->getVlrPago();
      }

      return $totalRecaudo;
    }

    //Calcula el número de facturas que están sin cierre de caja.
    public function totalFacturasCierre($idCaja,$em){
      $session = new Session();

      //Genera el recibo de Pago.
      $pagos = $em->getRepository("App:Pagos")
      ->createQueryBuilder("P")
      ->JOIN("App:Transacciones","T","WITH","P.idTransaccion = T.idTransaccion")
      ->JOIN("App:Facturas","F","WITH","P.idFactura = F.idFactura")
      ->where("T.idUsuario = :idUsu")
      ->andWhere("P.isDeleted = 0")
      ->andWhere("T.idCaja = :idCaja")
      ->andWhere("T.isClosed = 0")
      ->setParameter("idUsu",$session->get('idUsuario'))
      ->setParameter("idCaja",$idCaja)
      ->getQuery()->getResult();

      return count($pagos);
    }

    //valida que una consignación no esté registrada
    public function hasConsignacionAction(Request $request){
      $session = new Session();
      $em = $this->em;
      $nroConsignacion = $request->getContent();
      $jsonConsignacion = json_decode($nroConsignacion,true);

      $cajaActual = $em->getRepository("App:Cajas")
      ->findBy(array(
        "idCaja"=>$jsonConsignacion['idCaja'],
        "idUsuario"=>$session->get("idUsuario")
      ));

      //Consulta el id de la EmpresasSedesAgencias de acuerdo
      //al idEmpresaSedeAgencia de la $cajaActual
      $empSedesAgencias = $em->getRepository("App:EmpresasSedesAgencias")
      ->findBy(array(
        "idEmpresaSedeAgencia"=>$cajaActual[0]->getIdEmpresaSedeAgencia()
      ));

      //Validación NroConsignación.

      //Validación para determinar que no exista el mismo nro de consignación
      //ya registrada en la base de datos.
      $hasConsigFactura = $em->getRepository("App:Pagos")
      ->createQueryBuilder("P")
      ->join("App:Facturas","F","WITH","F.idFactura = P.idFactura")
      ->where("P.nroConsignacion = :nroConsig")
      ->andWhere("F.idEmpresa = :idEmp")
      ->andWhere("F.periodoActual = 0")
      ->setParameter("nroConsig",$jsonConsignacion["nroConsignacion"])
      ->setParameter("idEmp",$empSedesAgencias[0]->getIdEmpresa()->getIdEmpresa())
      ->getQuery()->getResult();

      if(count($hasConsigFactura) > 0){
        $response = array(
          "status"=>0,
          "error"=>"El número de la consignación <b>YA</b> se encuentra registrada en el sistema."
        );
      }else{
        $response = array(
          "status"=>1,
          "nroConsignacion"=>$jsonConsignacion
        );
      }
      //Fin Validación.

      return new Response(
        json_encode($response)
      );
    }

    //Services de facturación.
    public function getInfoFacturaAction(Request $request){
      //Instancias
      $em = $this->em;
      $session = $request->getSession();
      $rol = $session->get("rol");
      $method = $request->query->get("method");

      if($method == 'GET'){
        $nroFactura = $request->query->get("nroFactura");
        $niu = $request->query->get("niu");
      }
      /*else if($method == 'POST'){
        $nroFactura = $request->request->get("App_crucedecuentas")['nroFactura'];
        $niu = $request->request->get("App_crucedecuentas")['niu'];
      }*/

      //EmpresasSedesAgencias
      $idempsede = $em->getRepository("App:EmpresasSedesAgencias")
      ->findBy(array(
        "idSedeAgencia"=>$session->get("idSedeAgencia")
      ));

      $json = [];
      $filtros = [];

      //Filtrar para que no retorne facturas que ya hayan sido pagadas.
      $factura = $em->getRepository("App:Facturas")
      ->createQueryBuilder("F")
      ->where("F.periodoActual = 1");

      if($rol == 'Administrador' || $rol == 'Auditor'){ //Administrador,Auditor
        foreach ($idempsede as $key => $value) {
          $factura->orWhere("F.idEmpresa = :idEmp".$key)
          ->setParameter("idEmp".$key,$value->getIdEmpresa()->getIdEmpresa());
        }
      }

      if($nroFactura != '' && $niu != ''){
        $factura->andWhere("F.nroFactura = :numFact")
        ->andWhere("F.matricula = :niu")
        ->setParameter("numFact",$nroFactura)
        ->setParameter("niu",$niu);
      }else if($nroFactura != ''){
        $factura->andWhere("F.nroFactura = :numFact")
        ->setParameter("numFact",$nroFactura);
      }else if($niu != ''){
        $factura->andWhere("F.matricula = :niu")
        ->setParameter("niu",$niu);
      }

      if(count($factura->getQuery()->getResult()) > 0){
        $factura = $factura->getQuery()->getResult();
        foreach ($factura as $key => $value) {
          $json[$key] = array(
            "status" => 1,
            "idFactura" => $value->getIdFactura(),
            "nroFactura" => $value->getNroFactura(),
            "fechaVencimiento" => $value->getFechaVencimiento()->format("Y/m/d H:i:s"),
            "nombreUsuario" => $value->getNombreUsuario(),
            "valorFactura" => $value->getValorFactura(),
            "concepto" => $value->getConcepto(),
            "mesesAtrasados" => $value->getMesesAtrasados()
          );
        }
      }else{
        $json = array(
          "status" => 0,
          "error" => "<b>NO</b> se encuentran registros de facturas, de acuerdo al criterio de búsqueda, en el sistema."
        );
      }

      return new JsonResponse($json);
    }

    //Función que permite traer todo el total recaudado a la fechaActual
    //Dentro del periodo de facturación actual. Tambien obtiene información de cartera.
    public function getPagosAction(Request $request){
      //Variables
      $em = $this->em;
      $session = new Session();

      if($session->get("auth") == 1){//Valida si hay una sesion activa en el navegador.
        ini_set("max_execution_time", "1200");//20 Min.
        ini_set('memory_limit', '1024M');

        $rol = $session->get('rol');
        $now = new \Datetime('now', new \DateTimeZone('America/Bogota'));
        $UserActive = 0;
        $nroTotalFacturado = 0;
        $nroTotalPagos = 0;
        $nroTotalCartera = 0;

        $mes = $session->get("mesPeriodoActual");
        $año = $session->get("anioPeriodoActual");
        $mesFacturado = $request->query->get("mes") == null ? $mes : trim($request->query->get("mes"));
        $anioFacturado = $request->query->get("anio") == null ? $año : trim($request->query->get("anio"));
        $isAjax = $request->query->get("isAjax") == null ? null : $request->query->get("isAjax");
        $hasCartera = $request->query->get("cartera");

        //Trae las EmpresasSedesAgencias por medio de la Sede del Agencia a la cual el usuario activo pertenece.
        $idemp = $em->getRepository("App:EmpresasSedesAgencias")->findBy(["idSedeAgencia"=>$session->get('idSedeAgencia')]);

        //Calculo del total Facturado. Facturación del Periodo Actual.
        $queryFacturacion = "Select * From facturas Where facturas.mes_facturado = ".$mesFacturado." and facturas.anio_facturado = ".$anioFacturado;

        $queryPagos = "
            SELECT
            pagos.fecha_hora_pago, pagos.vlr_pago, pagos.saldo, pagos.nro_consignacion,
            pagos.fecha_consignacion, facturas.nro_factura, facturas.matricula,
            facturas.nombre_usuario, facturas.concepto, facturas.valor_factura, facturas.fecha_vencimiento,
            empresas.id_empresa, empresas.razon_social, sedes_agencias.nombre_sede, divipola.nom_poblad,
            agencias.nombre_agencia, tipo_pagos.tipo_pago, metodos_pago.metodo_pago
            FROM pagos
            INNER JOIN transacciones ON pagos.id_transaccion = transacciones.id_transaccion
            INNER JOIN facturas ON pagos.id_factura = facturas.id_factura
            INNER JOIN empresas_sedes_agencias ON transacciones.id_empresa_sede_agencia = empresas_sedes_agencias.id_empresa_sede_agencia
            INNER JOIN sedes_agencias ON empresas_sedes_agencias.id_sede_agencia = sedes_agencias.id_sede_agencia
            INNER JOIN empresas ON facturas.id_empresa = empresas.id_empresa AND empresas_sedes_agencias.id_empresa = empresas.id_empresa
            INNER JOIN divipola ON sedes_agencias.id_divipola = divipola.divipola
            INNER JOIN agencias ON sedes_agencias.id_agencia = agencias.id_agencia
            INNER JOIN tipo_pagos ON pagos.id_tipo_pago = tipo_pagos.id_tipo_pago
            INNER JOIN metodos_pago ON pagos.id_metodo_pago = metodos_pago.id_metodo_pago
            WHERE pagos.is_deleted = 0 and
            facturas.mes_facturado = ".$mesFacturado." and
            facturas.anio_facturado = ".$anioFacturado."
        ";

        if($rol == 'Cajero'){//Como cajero se muestran los pagos que el recepcionó
          $queryPagos .= " and transacciones.id_usuario = ".$session->get('idUsuario');
          //se recorre las empresas registradas en el sistema de las cuales se administra los recuados.
          foreach ($idemp as $key => $val){
            $queryPagos .= " or empresas_sedes_agencias.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();

            $queryPagos .= " and empresas_sedes_agencias.id_sede_agencia = ".$val->getIdSedeAgencia()->getIdSedeAgencia();
          }
        }else if($rol == 'Administrador Agencias'){//Como admin. de agencias se muestran todos los pagos recaudados de su agencia,
          // de todas sus sedes.
          $queryPagos .= " and agencias.id_agencia = ".$idemp[0]->getIdSedeAgencia()->getIdAgencia()->getIdAgencia();
          //se recorre las empresas registradas en el sistema de las cuales se administra los recuados.
          foreach ($idemp as $key => $val) {
            $queryPagos .= " or empresas_sedes_agencias.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();
          }
        }else if($rol == "Auditor" || $rol == "Administrador"){//Para roles Administrador, Auditor.
          //se recorre las empresas registradas en el sistema de las cuales se administra los recuados.
          foreach ($idemp as $key => $val) {
            //Solo se permite ver pagos realizados hasta la fecha de cada empresa.
            $queryPagos .= " and empresas_sedes_agencias.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();

            //Para Total Facturación.
            $queryFacturacion .= " and facturas.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();
          }
        }

        //Ejecuta la consulta raw para obtener el total facturado.
        $facturacion = $this->rawQueryDoctrine($queryFacturacion." AND facturas.is_abono = 0 ORDER By facturas.id_factura DESC");
        //$nroTotalFacturado = count($facturacion);
        //Fin Total Facturado - Facturación

        //Pagos
        $queryPagos .= " ORDER BY pagos.fecha_hora_pago DESC";
        $pagosRegistred = $this->rawQueryDoctrine($queryPagos);
        $nroTotalPagos = count($pagosRegistred);
        //Fin raw pagos


        if($nroTotalPagos > 0){//Si hay pagos.
          $totalPagos = 0;
          $totalFacturado = 0;
          $totalCartera = 0;
          $pagosxAgencias = [];
          $pagosEmpresas = [];
          $cartera = [];

          //Pagos Registrados
          foreach ($pagosRegistred as $key => $value) {
            $totalPagos += $value["vlr_pago"];//Total Pagos por todas las agencias de todas la empresa en el sistema.
            $empresa = $value["razon_social"];

            $agencia = $value["nombre_agencia"];

            $keysAgencias = array_keys($pagosxAgencias);

            if(in_array($agencia,$keysAgencias)){
              if( $isAjax != null && $isAjax === "true" ) {
                $pagosxAgencias[$agencia]["totalRecaudo"] += $value["vlr_pago"];
                $pagosxAgencias[$agencia]["totalFacturasRecaudadas"] += 1;
              }else if( $isAjax == null ) {
                $pagosxAgencias[$agencia][] = [
                  "nroFactura" => $value["nro_factura"],
                  "niu" => $value["matricula"],
                  "vlrPago" => $value["vlr_pago"],
                  "saldo" => $value["saldo"],
                  "fechaHoraPago" => $value["fecha_hora_pago"],
                  "tipoPago" => $value["tipo_pago"],
                  "metodoPago" => $value["metodo_pago"],
                  "nroConsignacion" => $value["nro_consignacion"],
                  "fechaConsignacion" => $value["fecha_consignacion"],
                  "Municipio" => $value["nom_poblad"]
                ];
              }
            }else{
              if( $isAjax != null && $isAjax === "true" ) {
                $pagosxAgencias[$agencia] = [
                  "totalRecaudo" => $value["vlr_pago"],
                  "totalFacturasRecaudadas" => 1
                ];
              }else if( $isAjax == null ){
                $pagosxAgencias[$agencia][0] = [
                  "nroFactura" => $value["nro_factura"],
                  "niu" => $value["matricula"],
                  "vlrPago" => $value["vlr_pago"],
                  "saldo" => $value["saldo"],
                  "fechaHoraPago" => $value["fecha_hora_pago"],
                  "tipoPago" => $value["tipo_pago"],
                  "metodoPago" => $value["metodo_pago"],
                  "nroConsignacion" => $value["nro_consignacion"],
                  "fechaConsignacion" => $value["fecha_consignacion"],
                  "Municipio" => $value["nom_poblad"]
                ];
              }
            }

            if($key == 0){
              //Usuarios Cajeros Activos en el sistema de recaudos.
              $queryDQLUsuarios = "Select U From App:Usuarios U
              JOIN App:EmpresasSedesAgencias ESA WITH ESA.idSedeAgencia = U.idSedeAgencia
              JOIN App:GruposUsuarios GU WITH GU.idGrupoUsuario = U.idGrupoUsuario
              Where U.auth = 1 AND GU.grupoUsuario = :grupo AND ESA.idEmpresa = :idEmp";

              $usuariosActivos = $em->createQuery($queryDQLUsuarios)
              ->setParameter("grupo","Cajero")->setParameter("idEmp",$value["id_empresa"])->getArrayResult();

              if(count($usuariosActivos) > 0){
                $UserActive = count($usuariosActivos);
              }
              //Fin Cajeros Activos.
            }

            $pagosEmpresas[$empresa] = [
              "pagosxAgencias" => $pagosxAgencias,
              "totalRecaudoGeneralCartera" => $totalPagos,
              "UsuariosActivos" => $UserActive,
              //"nroTotalFacturado" => $nroTotalFacturado,
              //"nroTotalPagos" => $nroTotalPagos
            ];
          }
          //Fin Pagos Registrados.

          //Facturación - Calculo del totalFacturado.
          $queryTotal = str_replace("Select * ","Select sum(valor_factura) as totalFacturado ",$queryFacturacion);
          $totalFact = $this->rawQueryDoctrine($queryTotal." AND facturas.is_abono = 0 LIMIT 1");
          $totalFacturado = $totalFact[0]["totalFacturado"];
          //Fin totalFacturado.

          if(isset($hasCartera) && $hasCartera != '' && $hasCartera === "true"){
            $cartera = [];
            //1. totalCartera de facturas NO pagas.
            $razonSocialEmp = [];
            $queryCartera = str_replace("Select * ","Select sum(facturas.valor_factura) as totalCartera ",$queryFacturacion);
            $queryCartera = $queryCartera."
            AND facturas.is_abono = 0 and facturas.nro_factura not in (Select fact.nro_factura from pagos
            INNER JOIN facturas as fact ON pagos.id_factura=fact.id_factura where fact.mes_facturado = ".$mesFacturado."
            and fact.anio_facturado = ".$anioFacturado." and pagos.is_deleted = 0";

            foreach ($idemp as $key => $val) {
              //Para Total Facturación.
              $queryCartera .= " and fact.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();
              array_push($razonSocialEmp,$val->getIdEmpresa()->getRazonSocial());
            }

            $queryCartera .= ") LIMIT 1";
            $resultQueryCartera = $this->rawQueryDoctrine($queryCartera);

            if( $isAjax == null ){
              //datos Cartera.
              $queryDatosCartera = str_replace("Select sum(facturas.valor_factura) as totalCartera ","Select nro_factura as nroFactura, matricula as niu, valor_factura as valorFactura, CONCAT(mes_facturado,'~',anio_facturado) as mesAnioFacturado, meses_atrasados as mesesAtrasados ",$queryCartera);
              $queryDatosCartera = str_replace(") LIMIT 1",")",$queryDatosCartera);

              //Calculo totalCartera de saldos de facturas pagadas pero con saldo por pagar. Saldo Anterior. Detallado.
              $queryCarteraSaldos1 = "Select facturas.nro_factura as nroFactura,facturas.matricula as niu,pagos.saldo as valorFactura,CONCAT(facturas.mes_facturado,'~',facturas.anio_facturado) as mesAnioFacturado, facturas.meses_atrasados as mesesAtrasados from pagos INNER JOIN facturas ON pagos.id_factura=facturas.id_factura where facturas.mes_facturado = ".$mesFacturado."
and facturas.anio_facturado = ".$anioFacturado;

              foreach ($idemp as $key => $val) {
                //Para Total Facturación.
                $queryCarteraSaldos1 .= " and facturas.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();
              }

              $queryCarteraSaldos1 .= " and pagos.saldo > 0 and pagos.saldo is not null and pagos.is_deleted = 0";

              $queryDatosCartera .= " ORDER By facturas.id_factura DESC";

              $resultSaldosCartera1 = $this->rawQueryDoctrine($queryCarteraSaldos1);
              $cartera = $this->rawQueryDoctrine($queryDatosCartera);

            }

            //2. Calculo totalCartera de saldos de facturas pagadas pero con saldo por pagar. Saldo Anterior.
            $queryCarteraSaldos = "Select sum(saldo) as totalPagosParciales from pagos INNER JOIN facturas ON pagos.id_factura=facturas.id_factura where facturas.mes_facturado = ".$mesFacturado."
and facturas.anio_facturado = ".$anioFacturado;

            foreach ($idemp as $key => $val) {
              //Para Total Facturación.
              $queryCarteraSaldos .= " and facturas.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();
            }
            $queryCarteraSaldos .= " and pagos.saldo > 0 and pagos.saldo is not null and pagos.is_deleted = 0 LIMIT 1";
            $resultSaldosCartera = $this->rawQueryDoctrine($queryCarteraSaldos);

            //3. Cálculo del total de Avances que se recibieron en los pagos.
            $queryAvances = "Select sum(pagos.saldo) as totalAvances from pagos INNER JOIN facturas ON pagos.id_factura=facturas.id_factura where facturas.mes_facturado = ".$mesFacturado."
and facturas.anio_facturado = ".$anioFacturado;

            foreach ($idemp as $key => $val) {
              //Para Total Facturación.
              $queryAvances .= " and facturas.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();
            }
            $queryAvances .= " and pagos.saldo < 0 and pagos.saldo is not null and pagos.is_deleted = 0 LIMIT 1";
            $resultAvances = $this->rawQueryDoctrine($queryAvances);

            //4. Calculo de totalCartera.
            $totalCartera = $resultQueryCartera[0]["totalCartera"] + $resultSaldosCartera[0]["totalPagosParciales"];

            //5. Calculo totalRealRecaudoGeneral, al totalRecaudoGeneralCartera se le resta los valores de los avances recibidos.
            // Se suma porque los valores registrados como avances tienen signo negativo.
            $totalRealRecaudoGeneral = $totalPagos + $resultAvances[0]["totalAvances"];

            //Validación solo para la empresa de energia del putumayo.
            //Con el NIU se identifica de que municipio es la matricula del usuario.
            if($session->get("nitAgencia") == '846000241-8'){
              $MunNIU = [1=>"Mocoa",2=>"Villagarzón",3=>"Puerto Guzmán",4=>"Orito",5=>"Piamonte",6=>"Santa Rosa"];
              foreach ($cartera as $key => $value) {
                $firstLetter = substr($value['niu'],0,1);
                $cartera[$key] = array_merge(["Municipio" => $MunNIU[$firstLetter]], $cartera[$key]);
              }
            }

            foreach ($razonSocialEmp as $key => $value) {
              $pagosEmpresas[$value]["PagosGeneral"] = $totalPagos;
              $pagosEmpresas[$value]["totalRecaudoGeneralCartera"] = $totalRealRecaudoGeneral;
              $pagosEmpresas[$value] = array_merge(["totalCartera" => $totalCartera], $pagosEmpresas[$value]);
              $pagosEmpresas[$value] = array_merge( ["totalFacturado" => $totalFacturado], $pagosEmpresas[$value]);
              if( $isAjax == null ){
                $pagosEmpresas[$value] = array_merge( ["cartera" => $cartera], $pagosEmpresas[$value]);
                $pagosEmpresas[$value] = array_merge( ["saldosCartera" => $resultSaldosCartera1], $pagosEmpresas[$value]);
              }
            }
          }//Fin validación si se desea traer información de cartera.


          $returnPago = array(
            "status" => 1,
            "pagosEmpresas" => $pagosEmpresas
          );
        }else{
          $returnPago = array(
            "status" => 0,
            "error" => "No hay pagos y/o registros en este periodo."
          );
        }

        return new JsonResponse($returnPago);
      }else{
        return $this->redirectToRoute("error",
          array('codigo'=>'100')
        );
      }//Fin validación de autenticación

    }

    //Ejecuta las sentencias de raw query en doctrine.
    public function rawQueryDoctrine($query,$params = [],$type = "select"){
      $em = $this->em;
      $db = $em->getConnection();
      $return = "";

      $stmt = $db->prepare($query);
      $rowAffected = $stmt->execute($params);

      if($type == "select"){
        $return = $stmt->fetchAll();
      }else{
        $return = $rowAffected;
      }

      return $return;
    }

    //Función que consulta todos los periodos registrados en facturas
    //de las empresas del usuario actual.
    public function getPeriodosAction(){
      //Variables
      $em = $this->em;
      $session = new Session();

      if($session->get("auth") == 1){//Valida si hay una sesion activa en el navegador.
        $idemp = $em->getRepository("App:EmpresasSedesAgencias")->findBy(["idSedeAgencia"=>$session->get("idSedeAgencia")]);
        $query = "Select DISTINCT mes_facturado, anio_facturado From facturas where ";
        $len = count($idemp);

        $meses = [1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre"];

        foreach ($idemp as $key => $value) {
          if($key < ($len-1)){
            $query .= " id_empresa = ".$value->getIdEmpresa()->getIdEmpresa()." OR ";
          }else if($key == ($len-1)){
            $query .= " id_empresa = ".$value->getIdEmpresa()->getIdEmpresa();
          }
        }

        $facturas = $this->rawQueryDoctrine($query);

        foreach ($facturas as $k => $v) {
          $facturas[$k]["text_mes_facturado"] = $meses[$v["mes_facturado"]];
        }

        return new JsonResponse($facturas);
      }else{
        return $this->redirectToRoute("error",
          array('codigo'=>'100')
        );
      }//Fin validación de autenticación
    }

    public function PermisosAction(Request $request,$idGrupoUsuario,$idEmpresa,$accion)
    {
      //Variables
      $em = $this->em;
      $session = new Session();

      if($session->get("auth") == 1){//Valida si hay una sesion activa en el navegador.
        $json = null;
        if($accion == "select"){
          $json = $this->selectPermisos($idGrupoUsuario,$idEmpresa);
        }else if($accion == "insert"){
          $idPagina = $request->query->get("idPagina");
          $sql = "INSERT INTO permisos (id_pagina,id_grupo_usuario,id_empresa) VALUES (".$idPagina.",".$idGrupoUsuario.",".$idEmpresa.")";
          $this->rawQueryDoctrine($sql,null,"insert");

          $json = $this->selectPermisos($idGrupoUsuario,$idEmpresa);
        }else if($accion == "delete"){
          $idPagina = $request->query->get("idPagina");
          $sql = "DELETE FROM permisos WHERE id_pagina = ".$idPagina." AND id_grupo_usuario = ".$idGrupoUsuario." AND id_empresa = ".$idEmpresa;
          $this->rawQueryDoctrine($sql,null,"delete");

          $json = $this->selectPermisos($idGrupoUsuario,$idEmpresa);
        }

        return new JsonResponse($json);
      }else{
        return $this->redirectToRoute("error",
          array('codigo'=>'100')
        );
      }//Fin validación de autenticación
    }

    private function selectPermisos($idGrupoUsuario,$idEmpresa){
      $json = ["conacceso"=>[],"sinacceso"=>[]];

      //Permisos del grupo de usuario.
      $sqlConPermiso = "SELECT paginas.id_pagina, paginas.pagina FROM permisos
      INNER JOIN paginas ON permisos.id_pagina = paginas.id_pagina
      INNER JOIN grupos_usuarios ON permisos.id_grupo_usuario = grupos_usuarios.id_grupo_usuario
      WHERE
      grupos_usuarios.id_grupo_usuario = ".$idGrupoUsuario." AND permisos.id_empresa = ".$idEmpresa." ORDER BY pagina ASC";
      $conPermiso = $this->rawQueryDoctrine($sqlConPermiso);

      //Paginas de las cuales no tiene permiso el grupo de usuario.
      $newSqlSubSelect = str_replace("SELECT paginas.id_pagina, paginas.pagina FROM","SELECT paginas.id_pagina FROM ",$sqlConPermiso);
      $sqlSinPermiso = "SELECT paginas.id_pagina, paginas.pagina
      FROM paginas where id_pagina not in ( ".$newSqlSubSelect.") ORDER BY pagina ASC";
      $sinPermiso = $this->rawQueryDoctrine($sqlSinPermiso);

      $json["conacceso"] = $conPermiso;
      $json["sinacceso"] = $sinPermiso;

      return $json;
    }

    //Función pago obtener la infomacion de un pago, en base a un nro de factura.
    public function getInfoPagosAction($nroFactura)
    {
      $em = $this->em;
      $session = new Session();

      if($session->get('auth') == 1){
        $queryPagos = "
          SELECT
          pagos.id_pago,
          pagos.fecha_hora_pago, pagos.vlr_pago, pagos.saldo, pagos.nro_consignacion, pagos.banco, pagos.nro_cheque, pagos.observaciones,
          pagos.fecha_consignacion, facturas.nro_factura, facturas.matricula, facturas.nombre_usuario,
          facturas.concepto, facturas.valor_factura, facturas.mes_facturado, facturas.anio_facturado, empresas.id_empresa,
          empresas.razon_social, sedes_agencias.nombre_sede, divipola.nom_poblad, agencias.nombre_agencia,
          tipo_pagos.tipo_pago, metodos_pago.id_metodo_pago, metodos_pago.metodo_pago FROM pagos
          INNER JOIN transacciones ON pagos.id_transaccion = transacciones.id_transaccion
          INNER JOIN facturas ON pagos.id_factura = facturas.id_factura
          INNER JOIN empresas_sedes_agencias ON transacciones.id_empresa_sede_agencia = empresas_sedes_agencias.id_empresa_sede_agencia
          INNER JOIN sedes_agencias ON empresas_sedes_agencias.id_sede_agencia = sedes_agencias.id_sede_agencia
          INNER JOIN empresas ON facturas.id_empresa = empresas.id_empresa AND empresas_sedes_agencias.id_empresa = empresas.id_empresa
          INNER JOIN divipola ON sedes_agencias.id_divipola = divipola.divipola
          INNER JOIN agencias ON sedes_agencias.id_agencia = agencias.id_agencia
          INNER JOIN tipo_pagos ON pagos.id_tipo_pago = tipo_pagos.id_tipo_pago
          INNER JOIN metodos_pago ON pagos.id_metodo_pago = metodos_pago.id_metodo_pago
          WHERE facturas.nro_factura = ".$nroFactura." and pagos.is_deleted = 0
        ";

        //Trae las EmpresasSedesAgencias por medio de la Sede del Agencia a la cual el usuario activo pertenece.
        $idemp = $em->getRepository("App:EmpresasSedesAgencias")->findBy(["idSedeAgencia"=>$session->get('idSedeAgencia')]);

        foreach ($idemp as $key => $val) {
          if($key == 0){
            $queryPagos .= " and empresas_sedes_agencias.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();
          }else{
            $queryPagos .= " or empresas_sedes_agencias.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();
          }
        }

        $queryPagos .= " ORDER By pagos.fecha_hora_pago DESC";

        $pagos = $this->rawQueryDoctrine($queryPagos);

        if(count($pagos) > 0){
          $return = $pagos;
        }else if($pagos != true){
          $return = ["status"=>false,"error"=>"Pago de esta referencia: ".$nroFactura.", no ha sido encontrado. Es posible que el pago haya sido eliminado con una novedad o no exista un pago de esta referencia."];
        }

        return new JsonResponse($return);
      }else{
        return new JsonResponse([
          "status"=>0,
          "error"=>"No puede acceder a este módulo. Para resolver este problema, autentíquese con sus credenciales e inténtelo nuevamente."
        ]);
      }
    }

    //Realiza el proceso de eliminar un pago cuando se ha cometido un error.
    //Cuando se radica una Novedad.
    public function deleteRegisterAction(Request $request,$idRegister,$module)
    {
      $em = $this->em;
      $session = new Session();

      if($session->get('auth') == 1){
        //Auditoria.
        $blockchain = new Blockchain($em);
        $observacionesNovedad = "";

        $content = $request->getContent();
        $json = json_decode($content,true);

        foreach ($json as $key => $value) {
          if($key == 'observaciones_novedad'){
            $observacionesNovedad = $value;
          }
        }

        //Proceso para modificar un registro de pago.
        if($module == 'pagos'){
          $queryDeletePago = "UPDATE ".$module." INNER JOIN facturas ON pagos.id_factura = facturas.id_factura SET ".$module.".is_deleted = 1, facturas.periodo_actual = 1
          WHERE pagos.id_pago = ".$idRegister;
        }else if($module == 'cierres de cajas'){

        }

        //Trae las EmpresasSedesAgencias por medio de la Sede del Agencia a la cual el usuario activo pertenece.
        $idemp = $em->getRepository("App:EmpresasSedesAgencias")->findBy(["idSedeAgencia"=>$session->get('idSedeAgencia')]);

        foreach ($idemp as $key => $val) {
          if($key == 0){
            $queryDeletePago .= " and facturas.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();
          }else{
            $queryDeletePago .= " or facturas.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();
          }
        }

        //Proceso para registrar la novedad.
        $campos = [
          "tx_hash",
          "fecha_hora_novedad",
          "modulo_afectado",
          "identificador_data",
          "anterior_data",
          "observaciones_novedad",
          "id_usuario",
          "id_tipo_novedad",
          "id_empresa"
        ];
        $fields = implode(",",$campos);

        $now = new \Datetime("now",new \DateTimeZone('America/Bogota'));
        $tipoNovedad = $this->rawQueryDoctrine("Select id_tipo_novedad from tipo_novedades where tipo_novedad like '%Eliminar%'");
        if($module == 'pagos'){
          $anteriorData = $this->rawQueryDoctrine("Select * from pagos where id_pago = ".$idRegister);
        }else if($module == 'cierres de cajas'){
          $anteriorData = $this->rawQueryDoctrine("Select * from cierres_de_cajas where id_cierre_de_caja = ".$idRegister);
        }

        $dataAnterior = implode("~",$anteriorData[0]);

        //Calculo del codigo de seguridad del registro.
        $hash = $this->getCodeSecurity([
          $now->format("Y-m-d H:i:s"),
          $module,
          $idRegister,
          $dataAnterior,
          $observacionesNovedad,
          $session->get("idUsuario"),
          $tipoNovedad[0]["id_tipo_novedad"],
          $idemp[0]->getIdEmpresa()->getIdEmpresa()
        ]);

        $valoresNovedad = [
          "'".$hash."'",
          "'".$now->format("Y-m-d H:i:s")."'",
          "'".$module."'",
          $idRegister,
          "'".$dataAnterior."'",
          "'".$observacionesNovedad."'",
          $session->get("idUsuario"),
          $tipoNovedad[0]["id_tipo_novedad"],
          $idemp[0]->getIdEmpresa()->getIdEmpresa()
        ];
        $valuesNovedad = implode(",",$valoresNovedad);

        $queryNovedades = "Insert Into novedades (".$fields.") Values (".$valuesNovedad.")";
        // Inserta la Novedad.
        $noved = $this->rawQueryDoctrine($queryNovedades,null,"insert");
        //Fin Registrar Novedad.

        // Auditoria Novedad
        $novedadRegistrada = $this->rawQueryDoctrine("Select * from novedades where tx_hash = '".$hash."'");
        $dataAudNovedad = [
          "accion"=>"Insert",
          "tabla"=>"novedades",
          "id_datos"=>$novedadRegistrada[0]["id_novedad"],
          "data"=>$valoresNovedad
        ];

        $blockchain->addBlock(new Block($dataAudNovedad));

        if($noved == true){// Cuando se insertó la novedad correctamente en la bd.
          $deletePago = $this->rawQueryDoctrine($queryDeletePago,null,"update");

          //Auditoria Modulo Afectado.
          $dataAudModuloAfectado = [
            "accion"=>"Update",
            "tabla"=>$module.",facturas",
            "id_datos"=>$idRegister,
            "data"=>["is_deleted = 1","periodo_actual = 1"]//La data que se afectó con el update.
          ];

          $blockchain->addBlock(new Block($dataAudModuloAfectado));

          //Proceso de validación de relaciones de los modulos.
          if($module == 'pagos'){
            //Modificar la Transacción.
            $dataRegisterModule = $em->getRepository("App:Pagos")->findBy(["idPago"=>$idRegister]);
            $transMod = $em->getRepository("App:Transacciones")->findBy(["idTransaccion"=>$dataRegisterModule[0]->getIdTransaccion()->getIdTransaccion()]);

            $transactionsPagos = $em->getRepository("App:Pagos")->findBy(["idTransaccion"=>$dataRegisterModule[0]->getIdTransaccion()->getIdTransaccion()]);
            if(count($transactionsPagos)>1){
              $totalTransaccion = 0;
              foreach ($transactionsPagos as $key => $value) {
                if($value->getIsDeleted() == 0){
                  $totalTransaccion += $value->getVlrPago();
                }
              }
              //Cálculo del código de seguridad de la transacción.
              $newCode = $this->getCodeSecurity(array(
                "fechaTransaccion"=>$transMod[0]->getFechaHoraTransaccion()->format("Y-m-d H:i:s"),
                "totalTransaccion"=>$totalTransaccion,
                "idUsuario"=>trim($transMod[0]->getIdUsuario()->getIdUsuario()),
                "idCaja"=>$transMod[0]->getIdCaja()->getIdCaja(),
                "idEmpresaSedeAgencia"=>trim($transMod[0]->getIdEmpresaSedeAgencia()->getIdEmpresaSedeAgencia())
              ));

              $transMod[0]->setCodigoSeguridad($newCode);
              $transMod[0]->setTotalTransaccion($totalTransaccion);
              $em->flush($transMod);//Modifica el registro de la transacción.
              //Fin código de seguridad.

              //Auditoria transacción Afectada.
              $dataAudModTotalTrans = [
                "accion"=>"Update",
                "tabla"=>"transacciones",
                "id_datos"=>$transMod[0]->getIdTransaccion(),
                "data"=>["codigo_seguridad = ".$newCode,"total_transaccion = ".$totalTransaccion]//La data que se afectó con el update.
              ];

              $blockchain->addBlock(new Block($dataAudModTotalTrans));
            }
            //Fin de Modificar la Transacción.

            //Modificar los cierres de cajas.
            $cierresTrans = $em->getRepository("App:CierresDeCajasTransacciones")
            ->findBy(["idTransaccion"=>$transMod[0]->getIdTransaccion()]);

            if(count($cierresTrans)>0){
              $totalCierre = 0;
              $newTotalColillas = 0;

              //Todas las transacciones del cierre de caja.
              $TransCierres = $em->getRepository("App:CierresDeCajasTransacciones")
              ->findBy(["idCierreDeCaja"=>$cierresTrans[0]->getIdCierreDeCaja()->getIdCierreCaja()]);
              foreach ($TransCierres as $k => $v) {
                $pagosxTrans = $em->getRepository("App:Pagos")->findBy(["idTransaccion"=>$v->getIdTransaccion()->getIdTransaccion()]);
                foreach ($pagosxTrans as $key => $value) {
                  if($value->getIsDeleted() == 0){
                    $newTotalColillas++;
                  }
                }
              }

              $cierre = $em->getRepository("App:CierresDeCajas")
              ->findBy(["idCierreCaja"=>$cierresTrans[0]->getIdCierreDeCaja()->getIdCierreCaja()]);
              //Al total del cierre le resto el valor del pago que ha sido eliminado, para luego ser actualizado el cierre.
              $totalCierre = $cierre[0]->getTotalRecaudoCaja()-$dataRegisterModule[0]->getVlrPago();

              //Modifica el cierre de caja siempre y cuando el totalCierre y totalColillas sean > 0
              if($totalCierre > 0 && $newTotalColillas > 0){
                $cierre[0]->setTotalRecaudoCaja($totalCierre);
                $cierre[0]->setVlrEnCaja($totalCierre);
                $cierre[0]->setDiferenciaCierre(0);//Cuadra el cierre para que no haya diferencia de cierre de caja.
                $cierre[0]->setTotalColillas($newTotalColillas);
                //Actualiza el registro del cierre de caja.
                $em->flush($cierre);

                //Auditoria cierre de caja Afectado.
                $dataAudModTotalRecaudoCaja = [
                  "accion"=>"Update",
                  "tabla"=>"cierres_de_cajas",
                  "id_datos"=>$cierre[0]->getIdCierreCaja(),
                  "data"=>["total_recaudo_caja = ".$totalCierre,"vlr_en_caja = ".$totalCierre,"diferencia_cierre = 0","total_colillas = ".$newTotalColillas]//La data que se afectó con el update.
                ];

                $blockchain->addBlock(new Block($dataAudModTotalRecaudoCaja));
              }
            }
            //Fin modificar los cierres de cajas.
          }else if($module == 'cierres de cajas'){

          }

          //Fin Proceso validacion de relaciones de los modulos

          //Registra los bloques de la auditoria en la bd.
          $blockchain->registerChain();
        }

        return new JsonResponse(["status"=>$deletePago]);
      }else{
        return new JsonResponse([
          "status"=>0,
          "error"=>"No puede acceder a este módulo. Para resolver este problema, autentíquese con sus credenciales e inténtelo nuevamente."
        ]);
      }
    }

    //Función para modificar los datos enviados por el request en una tabla
    //de la bd y crear un registro de esta novedad.
    public function editRegisterAction(Request $request,$idRegister,$module)
    {
      $em = $this->em;
      $session = new Session();

      if($session->get('auth') == 1){
        //Auditoria.
        $blockchain = new Blockchain($em);

        $content = $request->getContent();
        $json = json_decode($content,true);
        $values = [];
        $valuesModAud = [];
        $observacionesNovedad = "";

        foreach ($json as $key => $value) {
          if($key != 'datosModificar' && $key != 'observaciones_novedad' && $value != '' && $key != 'banco' && $key != "tiempo_consignacion" && $key != "fecha_consig" && $key != 'progress' && $key != 'progressDelete'){
            array_push($values,$module.".".$key."='".$value."'");
            array_push($valuesModAud,$value);
          }else if($key == 'banco'){
            array_push($values,$module.".".$key."='".ucwords($value)."'");
            array_push($valuesModAud,$value);
          }else if($key == 'observaciones_novedad'){
            $observacionesNovedad = $value;
          }
        }

        $valores = implode(",",$values);

        //Proceso para modificar un registro de pago.
        if($module == 'pagos'){
          $queryPagos = "UPDATE ".$module." INNER JOIN facturas ON pagos.id_factura = facturas.id_factura SET ".$valores."
          WHERE pagos.id_pago = ".$idRegister;
        }else if($module == 'cierres de cajas'){

        }

        //Trae las EmpresasSedesAgencias por medio de la Sede del Agencia a la cual el usuario activo pertenece.
        $idemp = $em->getRepository("App:EmpresasSedesAgencias")->findBy(["idSedeAgencia"=>$session->get('idSedeAgencia')]);

        foreach ($idemp as $key => $val) {
          if($key == 0){
            $queryPagos .= " and facturas.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();
          }else{
            $queryPagos .= " or facturas.id_empresa = ".$val->getIdEmpresa()->getIdEmpresa();
          }
        }
        //Fin proceso.

        //Proceso para registrar la novedad.
        $campos = [
          "tx_hash",
          "fecha_hora_novedad",
          "modulo_afectado",
          "identificador_data",
          "anterior_data",
          "observaciones_novedad",
          "id_usuario",
          "id_tipo_novedad",
          "id_empresa"
        ];
        $fields = implode(",",$campos);

        $now = new \Datetime("now",new \DateTimeZone('America/Bogota'));
        $tipoNovedad = $this->rawQueryDoctrine("Select id_tipo_novedad from tipo_novedades where tipo_novedad like '%Modificar%'");
        if($module == 'pagos'){
          $anteriorData = $this->rawQueryDoctrine("Select * from pagos where id_pago = ".$idRegister);
        }else if($module == 'cierres de cajas'){
          $anteriorData = $this->rawQueryDoctrine("Select * from cierres_de_cajas where id_cierre_de_caja = ".$idRegister);
        }

        $dataAnterior = implode("~",$anteriorData[0]);

        $hash = $this->getCodeSecurity([
          $now->format("Y-m-d H:i:s"),
          $module,
          $idRegister,
          $dataAnterior,
          $observacionesNovedad,
          $session->get("idUsuario"),
          $tipoNovedad[0]["id_tipo_novedad"],
          $idemp[0]->getIdEmpresa()->getIdEmpresa()
        ]);

        $valoresNovedad = [
          "'".$hash."'",
          "'".$now->format("Y-m-d H:i:s")."'",
          "'".$module."'",
          $idRegister,
          "'".$dataAnterior."'",
          "'".$observacionesNovedad."'",
          $session->get("idUsuario"),
          $tipoNovedad[0]["id_tipo_novedad"],
          $idemp[0]->getIdEmpresa()->getIdEmpresa()
        ];
        $valuesNovedad = implode(",",$valoresNovedad);

        $queryNovedades = "Insert Into novedades (".$fields.") Values (".$valuesNovedad.")";
        // Inserta la Novedad.
        $noved = $this->rawQueryDoctrine($queryNovedades,null,"insert");
        //Fin Registrar Novedad.

        // Auditoria Novedad
        $novedadRegistrada = $this->rawQueryDoctrine("Select * from novedades where tx_hash = '".$hash."'");
        $dataAudNovedad = [
          "accion"=>"Insert",
          "tabla"=>"novedades",
          "id_datos"=>$novedadRegistrada[0]["id_novedad"],
          "data"=>$valoresNovedad
        ];

        $blockchain->addBlock(new Block($dataAudNovedad));

        if($noved == true){// Cuando se insertó la novedad correctamente en la bd.
          //Inserta el pago.
          $pagos = $this->rawQueryDoctrine($queryPagos,null,"insert");

          //Auditoria Modulo Afectado.
          $dataAudModuloAfectado = [
            "accion"=>"Update",
            "tabla"=>$module,
            "id_datos"=>$idRegister,
            "data"=>$valuesModAud//La data que se afectó con el update.
          ];

          $blockchain->addBlock(new Block($dataAudModuloAfectado));
          $blockchain->registerChain();
        }

        return new JsonResponse(["status"=>$pagos]);
      }else{
        return new JsonResponse([
          "status"=>0,
          "error"=>"No puede acceder a este módulo. Para resolver este problema, autentíquese con sus credenciales e inténtelo nuevamente."
        ]);
      }
    }

    // Determina si el recaudo esta activo o se terminó.
    public function getStatusRecaudoAction()
    {
      $em = $this->em;
      $session = new Session();

      if($session->get('auth') == 1){
        //Auditoria.
        $blockchain = new Blockchain($em);
        $now = new \DateTime("now",new \DateTimeZone("America/Bogota"));

        $ESA = $em->getRepository("App:EmpresasSedesAgencias")
        ->findBy(["idSedeAgencia"=>$session->get("idSedeAgencia")]);

        $DQLperiodo = "Select max(F.mesFacturado) as mesFacturado, max(F.anioFacturado) as anioFacturado, max(F.fechaVencimiento) as fechaVencimiento,
         E.razonSocial From App:Facturas F JOIN App:Empresas E WITH E.idEmpresa = F.idEmpresa";

        foreach ($ESA as $key => $value) {
          if($key == 0){
            $DQLperiodo .= " and F.idEmpresa = ".$value->getIdEmpresa()->getIdEmpresa();
          }else{
            $DQLperiodo .= " or F.idEmpresa = ".$value->getIdEmpresa()->getIdEmpresa();
          }
        }

        $DQLperiodo .= " and F.periodoActual = 1 GROUP By E.razonSocial";

        $periodo = $em->createQuery($DQLperiodo)->getArrayResult();

        foreach ($periodo as $key => $value) {
          $fechaVenc = new \DateTime($value["fechaVencimiento"]);
          $diff = $fechaVenc->diff($now);

          if($now <= $fechaVenc){
            $periodo[$key]["status"] = "Recaudo Activo";
          }else if($now > $fechaVenc){
            $periodo[$key]["status"] = "Recaudo Finalizado";
          }
        }

        return new JsonResponse($periodo);
      }else{
        return new JsonResponse([
          "status"=>0,
          "error"=>"No puede acceder a este módulo. Para resolver este problema, autentíquese con sus credenciales e inténtelo nuevamente."
        ]);
      }
    }

    //Sistema de registro de log del uso de API de recaudos.
    public function logRequest($service = null,$args,$idEmpresa = 1)
    {
      $em = $this->em;
      $response = [];
      $headers = getallheaders();
      $method = $_SERVER['REQUEST_METHOD'];
      $now = new \DateTime("now",new \DateTimeZone("America/Bogota"));
      $empresa = $em->getRepository("App:Empresas")->findBy([
        "idEmpresa"=>$idEmpresa
      ]);

      $em->getConnection()->beginTransaction();
      
      try{
        $logApi = new LogApi();
        $logApi->setHost($headers["Host"]);
        $logApi->setService($service);
        $logApi->setHeader(json_encode($headers));
        $logApi->setMethod($method);
        $logApi->setArguments(implode(",",$args));
        $logApi->setFechaHoraPeticion($now);
        $logApi->setIdEmpresa($empresa[0]);

        $em->persist($logApi);
        $em->flush($logApi);
        
        $em->getConnection()->commit();

        $response = [
          "error"=>false,
          "message"=>"Log registrado correctamente"
        ];

      }catch(\Exception $er){
        $em->getConnection()->rollback();
        $response = [
          "error"=>true,
          "message"=>$er->getMessage()
        ];
      }
      
      return new JsonResponse($response);
    }

}
