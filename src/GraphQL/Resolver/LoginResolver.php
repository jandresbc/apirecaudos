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

class LoginResolver extends AbstractController implements ResolverInterface, AliasedInterface {

    private $em;
    private $headers;
    private $request;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->headers = getallheaders();
        $this->request = Request::createFromGlobals();
    }

    public function resolve(Argument $args)
    {
        $em = $this->getDoctrine()->getManager();
        $main = $this->get("main");
        
        if($this->request->isMethod("POST")){
            //Consultar información del Usuario que se desea logear.
            $infUs = $main->getInfoUser($args["user"],$em);
            if(count($infUs) > 0){
                $empsedeage = $em->getRepository("App:EmpresasSedesAgencias")->findBy([
                    "idSedeAgencia"=>$infUs[0]->getIdSedeAgencia()->getIdSedeAgencia()
                ]);
            }else{
                $response = [
                    "message"=>"Acceso Denegado. Usuario no encontrado. Por favor revise su ID y contraseña y vuelva a intentarlo. Si sigue sin poder Iniciar Sesión es posible que su usuario, Sede o Agencia esten Inactivas, para resolver este problema contacte al Administrador del Sistema.",
                    "code"=>401,
                    "error"=>true
                ];
                return $response;
            }
        }else{
            $response = [
                "message"=>"Método de envio de datos no soportado. Método permitido: 'POST'.",
                "code"=>405,
                "error"=>true
            ];
            return $response;
        }
        $conf = $main->ConfigappAction($empsedeage[0]->getIdEmpresa()->getIdEmpresa());
        $parametros = json_decode($conf->getContent());
        $host = explode(":",$this->headers["Host"])[0];
        $response = [];
        $main->logRequest("loginAPI",[$args["user"],$args["pass"]],$empsedeage[0]->getIdEmpresa()->getIdEmpresa());
        
        $EnabledIPs = explode(",",$parametros->enabledIPs);
        
        if(in_array($host,$EnabledIPs)){
            if($this->request->isMethod("POST")){
                $user = $args["user"];
                $pass = $args["pass"];
                
                if(count($infUs) > 0){
                    //Encripta el password recibido por el request
                    //desde el formulario de inicio de sesion.
                    $password = $main->encryptPass($pass);
                    
                    if(trim($password) === trim($infUs[0]->getContrasena()) && trim($user) === trim($infUs[0]->getIdentificacion())){
                        //Se consulta las cajas que tiene registrada el usuario que se está autenticando.
                        $caja = $em->getRepository("App:Cajas")
                        ->createQueryBuilder("C")
                        ->where("C.idUsuario = :idUs")
                        ->orderBy("C.idCaja","DESC")
                        ->setParameter("idUs",$infUs[0]->getIdUsuario())
                        ->getQuery()->getResult();

                        $response = [
                            "message"=>"Autenticado",
                            "code"=>200,
                            "error"=>false,
                            "idUsuario"=>$infUs[0]->getIdUsuario(),
                            "idCaja"=>$caja[0]->getIdCaja(),
                            "nitEmpresa"=>$caja[0]->getIdEmpresaSedeAgencia()->getIdEmpresa()->getNit(),
                            "nombreCompleto"=>$infUs[0]->getNombreCompleto()
                        ];

                        $token = $this->get('lexik_jwt_authentication.encoder')->encode([
                            'data' => $response
                        ]);
                        
                        $response["_token"] = "Bearer ".$token;
                    }else{
                        $response = [
                            "code" => 401,
                            "error" => true,
                            "message" => "Ocurrio un error al iniciar sesión en el sistema. Verifique su ID y contraseña e intente nuevamente."
                        ];
                    }
                }else{
                    $response = [
                        "code" => 401,
                        "error" => true,
                        "message" => "Hay un error al iniciar sesion. Por favor revise su ID y contraseña y vuelva a intentarlo. Si sigue sin poder Iniciar Sesión es posible que su usuario, Sede o Agencia esten Inactivas, para resolver este problema contacte al Administrador del Sistema."                    
                    ];
                }
            }else{
                $response = [
                    "code" => 405,
                    "error" => true,
                    "message" => "Método de envio de datos no soportado. Método permitido: 'POST'"
                ];
            }
        }else{
            $response = [
                "code" => 403,
                "error" => true,
                "message" => "403 Forbidden: Su dirección IP(".$host.") no esta autorizada a conectarse a este servicio."
            ];
        }
        
        return $response;
    }

    public static function getAliases()
    {
        return [
            'resolve' => 'Login'
        ];
    }
}