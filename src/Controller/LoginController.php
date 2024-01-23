<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

use Overblog\GraphQLBundle\Error\UserError;
use App\EventListener\CustomErrorHandler;
use GraphQL\Error\FormattedError;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $main = $this->get("main");
        if($request->isMethod("POST")){
            //Consultar información del Usuario que se desea logear.
            $infUs = $main->getInfoUser($request->request->get("user"),$em);
            if(count($infUs) > 0){
                $empsedeage = $em->getRepository("App:EmpresasSedesAgencias")->findBy([
                    "idSedeAgencia"=>$infUs[0]->getIdSedeAgencia()->getIdSedeAgencia()
                ]);
            }else{
                $response = [
                    "code" => 401,
                    "error" => true,
                    "message" => "401 Unauthorized: Acceso Denegado. Usuario no encontrado. Por favor revise su ID y contraseña y vuelva a intentarlo. Si sigue sin poder Iniciar Sesión es posible que su usuario, Sede o Agencia esten Inactivas, para resolver este problema contacte al Administrador del Sistema."
                ];
                return new JsonResponse($response);
            }
        }else{
            $response = [
                "code" => 405,
                "error" => true,
                "message" => "405 Method Not Allowed: Método de envio de datos no soportado. Método permitido: 'POST'"
            ];
            return new JsonResponse($response);
        }
        $conf = $main->ConfigappAction($empsedeage[0]->getIdEmpresa()->getIdEmpresa());
        $parametros = json_decode($conf->getContent());
        $host = explode(":",$request->headers->get("host"))[0];
        $response = [];
        $main->logRequest("loginAPI",[$request->request->get("user"),$request->request->get("pass")],$empsedeage[0]->getIdEmpresa()->getIdEmpresa());
        
        $EnabledIPs = explode(",",$parametros->enabledIPs);
        
        if(in_array($host,$EnabledIPs)){
            if($request->isMethod("POST")){
                $user = $request->request->get("user");
                $pass = $request->request->get("pass");
                
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
                            "data"=>[
                                "idUsuario"=>$infUs[0]->getIdUsuario(),
                                "idCaja"=>$caja[0]->getIdCaja(),
                                "nitEmpresa"=>$caja[0]->getIdEmpresaSedeAgencia()->getIdEmpresa()->getNit(),
                                "nombreCompleto"=>$infUs[0]->getNombreCompleto()
                            ]
                        ];

                        $token = $this->get('lexik_jwt_authentication.encoder')->encode([
                            'data' => $response
                        ]);
                        
                        $response["_token"] = "Bearer ".$token;
                    }else{
                        $response = [
                            "code" => 401,
                            "error" => true,
                            "message" => "401 Unauthorized: Ocurrio un error al iniciar sesión en el sistema. Verifique su ID y contraseña e intente nuevamente."
                        ];
                    }
                }else{
                    $response = [
                        "code" => 401,
                        "error" => true,
                        "message" => "401 Unauthorized: Hay un error al iniciar sesion. Por favor revise su ID y contraseña y vuelva a intentarlo. Si sigue sin poder Iniciar Sesión es posible que su usuario, Sede o Agencia esten Inactivas, para resolver este problema contacte al Administrador del Sistema."                    
                    ];
                }
            }else{
                $response = [
                    "code" => 405,
                    "error" => true,
                    "message" => "405 Method Not Allowed: Método de envio de datos no soportado. Método permitido: 'POST'"
                ];
            }
        }else{
            $response = [
                "code" => 403,
                "error" => true,
                "message" => "Forbidden: Su dirección IP no esta autorizada a conectarse a este servicio."
            ];
        }
        
        return new JsonResponse($response);
    }
}