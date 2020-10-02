<?php

namespace App\Controller;
use App\Entity\Post;
use App\Entity\Site;
use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
// Importamos las clases relativas a respuestas y peticiones HTTP
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
class CategoryController extends AbstractController{


    /* Renderizamos una vista con 6 categorías basandonos en
       el número de pagina pasado como parámetro. */

    public function getCategories($page, $message){

        $categories_repo = $this->getDoctrine()->getRepository(Category::class);
        $sites_repo = $this->getDoctrine()->getRepository(Site::class);
        $max_page = ceil($categories_repo->count(array())/6);
        $categories = $categories_repo->findBy(array(),
                                    array('id' => 'DESC'),
                                    6,
                                    6 * ($page - 1));

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
            'max_page' => $max_page,
            'current_page' => $page,
            'sites' => $sites_repo->findAll(),
            'message' => $message
        ]);
    }

    /*
        Hace un nuevo registro en la bbdd en base a los datos
        recibidos en la petición.
    */
    public function createCategory(Request $request){

        if ($request->request) {

            $em = $this->getDoctrine()->getManager();
            $category_repo = $this->getDoctrine()->getRepository(Category::class);
            $site_repo = $this->getDoctrine()->getRepository(Site::class);
            $slug_exist = $category_repo->findBy(['slug' => $request->get('slug')]);
            
            if (!$slug_exist) {
                $category = new Category();
                $site = $site_repo->findOneBy(['id' => $request->get('site')]);
                $category
                    ->setName($request->get('name'))
                    ->setSlug($request->get('slug'))
                    ->setMetaTitle($request->get('metatitle'))
                    ->setMetaDescription($request->get('metadescription'))
                    ->setActive(1)
                    ->setCreateDate(new \DateTime('now'))
                    ->setSite($site);
                $em->persist($category);
                $em->flush();

                return $this->RedirectToRoute('categories');
            }
            
            return $this->RedirectToRoute('categories', ['message' => 'This slug already exist.']);

        }

    }

    /*
     * Actualizamos un registro de la tabla category en la bbdd
    */

    public function updateCategory(Request $request, Category $category){
            $em = $this->getDoctrine()->getManager();
            $site_repo = $this->getDoctrine()->getRepository(Site::class);

        if ($request->isMethod('POST')) {

                $site = $site_repo->findOneBy(['id' => $request->get('site')]);
                $category
                    ->setName($request->get('name'))
                    ->setSlug($request->get('slug'))
                    ->setMetaTitle($request->get('metatitle'))
                    ->setMetaDescription($request->get('metadescription'))
                    ->setActive(1)
                    ->setCreateDate(new \DateTime('now'))
                    ->setSite($site);
                $em->persist($category);
                $em->flush();

                return $this->RedirectToRoute('categories');
        }


        return $this->render('category/update.html.twig', [
            'sites' => $site_repo->findAll(),
            'category' => $category
        ]);

    }

    /*
     * Actualizamos el campo active de un registro de la tabla category
     * Si esta a 0 lo ponemos a 1 y viceversa.   
     */

    public function activeCategory(Category $category){
        
        $em = $this->getDoctrine()->getManager();

        if ($category->getActive() == true) {

            $category->setActive(0);
            $em->persist($category);
            $em->flush();
        
        } else {

            $category->setActive(1);
            $em->persist($category);
            $em->flush();
        }

        return $this->RedirectToRoute('categories');
    }



}
