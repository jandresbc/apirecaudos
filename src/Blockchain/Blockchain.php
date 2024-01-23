<?php
/*
* @autor: Julio Andres Barrera Carvajal - devstudio.me
* @Description: Clases para implementar blockchain - 2018
*/

namespace App\Blockchain;

// No olvides incluir los namespaces necesarios
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Blockchain\Block;
use App\Entity\Auditoria;

class Blockchain{

    public $chain = [];
    private $em = null;
    private $now = null;

    public function __construct(EntityManager $entityManager) {
        $this->chain = [$this->createGenesisBlock()];
        $this->em = $entityManager;
        $this->now = new \DateTime("now", new \DateTimeZone("America/Bogota"));
    }

    private function createGenesisBlock() {
        return new Block(["Genesis block"]);
    }

    public function getLatestBlock() {
        $len = count($this->chain);
        return $this->chain[$len - 1];
    }

    public function addBlock(Block $newBlock) {
        //Se agrega a la cadena la fecha y hora del registro de la cadena.
        $newBlock->data["fechaHora"] = $this->now->format("Y-m-d H:i:s");

        $newBlock->previousHash = $this->getLatestBlock()->hash;
        $newBlock->hash = $newBlock->calculateHash();
        array_push($this->chain,$newBlock);
    }

    public function isChainValid() {
        $valid = ["status"=>1,"mensaje"=>"chain valid"];

        for($i = 1; $i < count($this->chain);$i++){
          $currentBlock = $this->chain[$i];
          $previousBlock = $this->chain[$i - 1];

          if ($currentBlock->hash !== $currentBlock->calculateHash()) {
              $valid = ["status"=>0,"index"=>$i,"mensaje"=>"chain not valid"];
              break;
          }

          if ($currentBlock->previousHash !== $previousBlock->hash) {
              $valid = ["status"=>0,"index"=>$i,"mensaje"=>"chain not valid"];
              break;
          }
        }

        return new JsonResponse($valid);
    }

    //Registra la cadena actual en la tabla Auditoria.
    //$addData es un arreglo para pasar los parametros a registrar.
    public function registerChain($idEmpresa = null, $idSedeAgencia = null, $idUsuario = null){//$addData = []
      $session = new Session();
      //if( sizeof($addData) > 0 ){
        //Se agrega a la cadena la fecha y hora del registro de la cadena.
        //$addData["fechaHora"] = $this->now->format("Y-m-d H:i:s");

        $idsedeagencia = $idSedeAgencia != null ? $idSedeAgencia : $session->get("idSedeAgencia");

        $idusuario = $idUsuario != null ? $idUsuario : $session->get("idUsuario");

        $filtros = $idEmpresa != null ? ["idSedeAgencia"=>$idsedeagencia,"idEmpresa"=>$idEmpresa] : ["idSedeAgencia"=>$idsedeagencia];

        $ESA = $this->em->getRepository("App:EmpresasSedesAgencias")->findBy($filtros);

        foreach ($ESA as $idE => $empresa) {
          $genesis = $this->em->getRepository("App:Auditoria")->findBy([
            "bxfrom"=>"Init Genesis",
            "accion"=>"Init Genesis",
            "tabla"=>"Init Genesis",
            "idDatos"=>"Init Genesis",
            "idUsuario"=>$idusuario,
            "idEmpresa"=>$empresa->getIdEmpresa()
          ]);

          if(count($genesis) > 0){
            //Elimina el genesis para que no lo guarde nuevamente en la bd.
            unset($this->chain[0]);

            $historialAuditoria = $this->em
            ->createQuery("
              SELECT A FROM App:Auditoria A
              WHERE A.idAuditoria = (SELECT MAX(Au.idAuditoria) id
              FROM App:Auditoria Au WHERE Au.idUsuario = :idUsu AND Au.idEmpresa = :idEmp)
            ")->setParameter("idUsu",$idusuario)
            ->setParameter("idEmp",$empresa->getIdEmpresa())->getResult();

            $this->chain[1]->previousHash = $historialAuditoria[0]->getBxTo();
          }

          //Inicia la transacción.
          $this->em->getConnection()->beginTransaction();

          try{
            foreach ($this->chain as $key => $value) {
                  $auditoria = new Auditoria();

                  if (!isset($value->data["data"])){
                    $value->data = [
                      "accion" => "Init Genesis",
                      "tabla" => "Init Genesis",
                      "id_datos" => "Init Genesis",
                      "data"=>["Genesis block"]
                    ];
                  }

                  $value->data["data"] = is_array($value->data["data"]) ? implode("~",$value->data["data"]) : $value->data["data"];

                  $TxHash = [
                    $value->previousHash,
                    $value->hash,
                    $this->now->format("Y-m-d H:i:s"),
                    $value->data["accion"],
                    $value->data["tabla"],
                    $value->data["id_datos"],
                    implode("~",$value->data),
                    $idusuario,
                    $empresa->getIdEmpresa()->getIdEmpresa()
                  ];

                  $auditoria->setDatos(implode("~",$value->data));

                  $TxHash256 = hash("sha256",implode("¬",$TxHash));

                  $auditoria->setTxhash($TxHash256);
                  $auditoria->setBxFrom($value->previousHash);
                  $auditoria->setBxTo($value->hash);
                  $auditoria->setFechaHora(new \DateTime($this->now->format("Y-m-d H:i:s")));
                  $auditoria->setAccion($value->data["accion"]);
                  $auditoria->setTabla($value->data["tabla"]);
                  $auditoria->setIdDatos($value->data["id_datos"]);

                  $usuario = $this->em->getRepository("App:Usuarios")->findBy(["idUsuario"=>$idusuario]);

                  $auditoria->setIdUsuario($usuario[0]);
                  $auditoria->setIdEmpresa($empresa->getIdEmpresa());

                  $this->em->persist($auditoria);
                  $this->em->flush($auditoria);
            }

            //Registra la transacción.
            $this->em->getConnection()->commit();
          }catch(Exception $e){
            $this->em->getConnection()->rollback();
             throw $e;
          }
        }
        $this->chain = [];//Resetea la cadena después de ser registrada en la tabla auditoria.
      //}

      return new jsonResponse(["status"=>1,"mensaje"=>"Se registró la cadena correctamente."]);
    }

    //Trae la cadena que tiene registrado un usuario.
    public function getChain($idAuditoria = "",$offset = 0, $limit = 1000,$idUsuario = null){
       $session = new Session();
       $chain = [];
       $idUsuario = $idUsuario = null ? $session-get('idUsuario') : $idUsuario;

       $ESA = $this->em->getRepository("App:EmpresasSedesAgencias")->findBy(["idSedeAgencia"=>$session->get("idSedeAgencia")]);

       foreach ($ESA as $key => $value) {
         $empresa = $value->getIdEmpresa()->getRazonSocial();
         $auditoria = $this->em->getRepository("App:Auditoria")->createQueryBuilder("A")
         ->where("A.idUsuario = :idUsu")
         ->setParameter("idUsu",$idUsuario)
         ->andWhere("A.idEmpresa = :idEmp")
         ->setParameter("idEmp",$value->getIdEmpresa()->getIdEmpresa());
         //->orderBy("A.idAuditoria","DESC");

         if($idAuditoria != '' && $idAuditoria != null){
           $auditoria->andWhere("A.idAuditoria = :idAud")
           ->setParameter("idAud",$idAuditoria);
         }else{
           $auditoria->setFirstResult( $offset )
           ->setMaxResults( $limit );
         }

         $auditoria = $auditoria->getQuery()->getResult();

         if($idAuditoria != '' && $idAuditoria != null){
           $chain = $auditoria;
         }else{
           $chain[$empresa] = $auditoria;
           $this->chain = [];
           foreach ($chain[$empresa] as $key => $value) {
             $newBlock = new Block(explode("~",$value->getDatos()));
             $newBlock->hash = $value->getBxTo();
             $newBlock->previousHash = $value->getBxFrom();
             array_push($this->chain,$newBlock);
           }
         }
       }

       return $chain;
    }

    //Valida la integridad de los datos registrados en la tabla auditoria.
    public function validateTxHash($idAuditoria,$idUsuario = null){
      $auditoria = $this->getChain($idAuditoria,null,null, $idUsuario);

      $TxHash = [
        $auditoria[0]->getBxFrom(),
        $auditoria[0]->getBxTo(),
        $auditoria[0]->getFechaHora()->format("Y-m-d H:i:s"),
        $auditoria[0]->getAccion(),
        $auditoria[0]->getTabla(),
        $auditoria[0]->getIdDatos(),
        $auditoria[0]->getDatos(),
        $auditoria[0]->getIdUsuario()->getIdUsuario(),
        $auditoria[0]->getIdEmpresa()->getIdEmpresa()
      ];

      $TxHash256 = hash("sha256",implode("¬",$TxHash));

      if($TxHash256 == $auditoria[0]->getTxhash()){
        return "valid";
      }else{
        return "no valid";
      }
    }
}
