<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AdminLoginType;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class LoginAdminController extends AbstractController
{
    private $userEmail;
    private $userPassword;
    private $credentials;

    /**
     * @Route("/admin", name="adminLogin")
     */
    public function index(Request $request): Response
    {
        $mensaje = $request->get('mensaje');
        
        if(empty($mensaje)){
            return $this->render('login_admin/index.html.twig');
        } else {
            return $this->render('login_admin/index.html.twig', ['mensaje' => $mensaje]);
        }
    }

    /**
     * @Route("/compruebaUser", name="compruebaUser")
     */
    public function loginAdmin(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $request->get('emailUser')]);
        $passwordUser = $user->getPassword();
        $this->userPassword = $request->get('passwordUser'); 

        if($passwordEncoder->isPasswordValid($user,$this->userPassword) && $user->getTypeUser() ){
            $request->getSession()->set(
                Security::LAST_USERNAME,
                $request->get('emailUser')
            );

            return $this->redirectToRoute('admin');
        }
        else {
            
        $mensaje = "El usuario introducido no tiene permisos para acceder a la zona de administración";

        //$this->index($request);

        return $this->redirectToRoute('adminLogin', ['mensaje' => $mensaje]);
        }
    }
}
