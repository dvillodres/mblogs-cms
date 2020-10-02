<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Category;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

// Importamos las clases relativas a respuestas y peticiones HTTP
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

// Importamos las clases para los tipos utilizados en el formulario
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

// Importamos la clase Constraints para realizar validaciones sobre el formulario
use Symfony\Component\Validator\Constraints as Assert;

class PostController extends AbstractController {

    public function index(){
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }


    /* Renderizamos una vista con 8 posts basandonos en
       el número de pagina pasado como parámetro. */

    public function getPosts($page){

        $post_repo = $this->getDoctrine()->getRepository(Post::class);
        $max_page = ceil($post_repo->count(array())/8);
        $posts = $post_repo->findBy(array(),
                                    array('id' => 'ASC'),
                                    8,
                                    8 * ($page - 1));

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'max_page' => $max_page,
            'current_page' => $page
        ]);
    }

    /*
        Este método se encarga de crear un nuevo registro en la tabla post
        o de actualizar uno existente. Recibe como parametro una request
        con los datos del post a crear/actualizar, el objeto security para poder
        acceder al usuario actual y un objeto post instanciado a null por defecto.
        Con el objeto post comprabamos si hay que crear un registro nuevo o actualizar
        este.
    */

    public function createPost(Request $request,Security $security, Post $post = NULL){
       
        if ($post == NULL) {
            $post = new Post();
            $action = 'create';
            $title = 'New Post';
        }else {
            $action = 'update';
            $title = 'Update Post';           
        }

        $category_repo = $this->getDoctrine()->getRepository(Category::class);
        $post_repo = $this->getDoctrine()->getRepository(Post::class);
        $categories = $category_repo->findAll();

        if ($request->request->has('title')) {

            $slug = $request->request->get('slug');
            $slug_exist = $post_repo->findBy(['slug' => $slug]);

            if ($action == 'update') {
                $slug_exist = false;
            }

            if ($slug_exist) {
                return $this->render('post/create.html.twig', [
                    'categories' => $categories,
                    'slug_exist' => 'This post slug already exist.'
                ]);                
            } else {

                $data = $request->request;

                $category_repo = $this->getDoctrine()->getRepository(Category::class);
                $category = $category_repo->findOneBy(['id' => $data->get('category')]);

                $post
                    ->setTitle($data->get('title'))
                    ->setSlug($data->get('slug'))
                    ->setCategory($category)
                    ->setContent($data->get('content'))
                    ->setMetatitle($data->get('meta-title'))
                    ->setMetadescription($data->get('meta-description'))
                    
                    ->setActive(1)
                    ->setAuthor($security->getUser());

                    if ($action == 'create') {
                       $post
                        ->setCreateDate(new \Datetime ('now'))
                        ->setPublicDate(new \Datetime ('now'))
                        ->setImage("");
                    }
                    
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($post);
                    $em->flush();

                    return $this->RedirectToRoute('upload-post-image', ['slug' => $post->getSlug()]);
            }
        }

        return $this->render('post/create.html.twig', [
            'categories' => $categories,
            'post' => $post,
            'action' => $action,
            'title' => $title
        ]);
    }

    /* Función destinada a subir imagenes al servidor */

    public function imageUpload(Request $request, Post $post){
        
        // Creamos el formulario

        $form = $this->createFormBuilder()
            ->add('image', FileType::class, ['label' => 'Pick a Image', ])
            ->add('save', SubmitType::class,['label' => 'Upload Image'])
            ->getForm();

        // Comprobamos la solicitud

        $form->handleRequest($request);

        // So el formuario se ha registrado y es valido procesamos la información para subir la image
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Recuperamos el archivo            
            $image = $form->get('image')->getData();

            // Revisamos la extensión y creamos el nombre del archivo
            $imageName = $post->getSlug() . '.' . $image->guessExtension();

            // Movemos el archivo donde queremos que esté
            $image->move('assets/img/posts', $imageName);

            // Actualizamos el registro en la bbdd con el nombre de la imagen correcto
            $em = $this->getDoctrine()->getManager();
            $post->setImage($imageName);
            $em->persist($post);
            $em->flush();
            return $this->RedirectToRoute('posts');

        } else {

            // Si aún no se ha enviado el formulario devolvemos la vista
            return $this->render('post/upload-image.html.twig', [
                'title' => 'Upload a feature image for ' . $post->getTitle(),
                'form' => $form->createView(),
                'post' => $post
            ]);   

        }


    }


    /*
        Recibe un objeto de tipo Post por parámetro
        en el caso de estar activo lo desactiva y 
        viceversa. Redirige a la vista posts.
    */

    public function activePost(Post $post){
        
        $em = $this->getDoctrine()->getManager();

        if ($post->getActive() == true) {

            $post->setActive(0);
            $em->persist($post);
            $em->flush();
        
        } else {

            $post->setActive(1);
            $em->persist($post);
            $em->flush();
        }

        return $this->RedirectToRoute('posts');
    }

    /*
        Recibe un objeto de tipo Post por parámetro
         y lo elimina de la bbdd.
    */

    public function deletePost(Post $post){
        
        $em = $this->getDoctrine()->getManager();

            $em->remove($post);
            $em->flush();

        return $this->RedirectToRoute('posts');
    }

}
