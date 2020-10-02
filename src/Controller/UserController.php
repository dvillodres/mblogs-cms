<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Role;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Security;
// Importamos las clases relativas a respuestas y peticiones HTTP
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{

    /*
     * Este método renderiza una vista con un formulario de login y además
     * se encarga de comprobar que el login sea correcto y generar la sesión
     * se apoya en un provider para tal cosa.
     */
  public function login(AuthenticationUtils $authenticationUtils, Security $security){

        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $users = $user_repo->findAll();

        if (sizeof($users) <= 0) {
                return $this->render('user/index.html.twig', [
            ]);
        }

        $user = $security->getUser();

        if ($user) {
                    return $this->RedirectToRoute('posts');
        }

    	$error = $authenticationUtils->getLastAuthenticationError();
    	$lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'error' => $error,
            'lastUsername' => $lastUsername
        ]);
    }
    /*
        Hace un nuevo registro en la bbdd en base a los datos
        recibidos en la petición.
    */
    public function createUser(UserPasswordEncoderInterface $encoder, Request $request){

        if ($request->request) {
            
            $em = $this->getDoctrine()->getManager();

            $user = new User();
            
            $role_repository = $this->getDoctrine()->getRepository(Role::class);
            $role = $role_repository->findOneBy(['role' => 'ROLE_ADMIN']);
            
            $user
                ->setNick($request->get('nick'))
                ->setEmail($request->get('email'))
                ->setPass($request->get('password'))
                ->setRole($role)
                ->setCreateDate(new \Datetime('now'))
                ->setActive(1);

            $encodedPw = $encoder->encodePassword($user, $user->getPassword());
            $user->setPass($encodedPw);

            $em->persist($user);
            $em->flush();
        }

        return $this->RedirectToRoute('login');

    }
    /*
     * Actualizamos un registro de la tabla user en la bbdd, en este caso solo la contraseña
    */
    public function  editUser(UserPasswordEncoderInterface $encoder, Request $request, Security $security){

        $user = $security->getUser();

        if ($request->request->has('newpassword')) {
            $encodedPw = $encoder->encodePassword($user, $request->request->get('newpassword'));
            $user->setPass($encodedPw);

            $em = $this->getdoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->render('user/edit.html.twig', [
                'password_changed' => 'The password has been changed.'
            ]);
        }

        return $this->render('user/edit.html.twig', [
        ]);
    }

}
