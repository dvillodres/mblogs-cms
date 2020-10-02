<?php

namespace App\Controller;

use App\Entity\Comment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController{

    /*
     * Renderizamos una vista con los comentarios, renderizamos 6 por página
     * Recibimos como parámetro la página que hay que renderizar
     */

    public function getComments($page){

        $comments_repo = $this->getDoctrine()->getRepository(Comment::class);
        $max_page = ceil($comments_repo->count(array())/6);
        $comments = $comments_repo->findBy(array(),
                                    array('id' => 'DESC'),
                                    6,
                                    6 * ($page - 1));

        return $this->render('comment/index.html.twig', [
            'max_page' => $max_page,
            'current_page' => $page,
            'comments' =>  $comments
        ]);
    }

    /*
     * Actualizamos el campo active de un registro de la tabla comment
     * Si esta a 0 lo ponemos a 1 y viceversa.   
     */

    public function activeComment(Comment $commnet){
        
        $em = $this->getDoctrine()->getManager();

        if ($commnet->getActive() == true) {

            $commnet->setActive(0);
            $em->persist($commnet);
            $em->flush();
        
        } else {

            $commnet->setActive(1);
            $em->persist($commnet);
            $em->flush();
        }

        return $this->RedirectToRoute('comments');
    }

}
