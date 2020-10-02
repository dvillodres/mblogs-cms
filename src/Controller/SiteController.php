<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Category;
use App\Entity\Site;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SiteController extends AbstractController{

    /*
     * Renderizamos una vista con los sites, renderizamos 8 por página
     * Recibimos como parámetro la página que hay que renderizar
     */

    public function getSites($page){

        $site_repo = $this->getDoctrine()->getRepository(Site::class);
        $max_page = ceil($site_repo->count(array())/6);
        $sites = $site_repo->findBy(array(),
                                    array('id' => 'ASC'),
                                    8,
                                    8 * ($page - 1));
        return $this->render('site/index.html.twig', [
            'sites' => $sites,
            'max_page' => $max_page,
            'current_page' => $page,
        ]);
    }


    /*
        Este método se encarga de crear un nuevo registro en la tabla sites
        o de actualizar uno existente.
    */

    public function createSite(Request $request, Site $site = NULL){

        $action = 'update';
        $title = 'Update Site';

        if ($site == NULL) {
            $site = new Site();
            $action = 'create';
            $title = 'New Site';
        }

        if ($request->request->has('name')) {
            $data = $request->request;
            $site
                ->setName($data->get('name'))
                ->setMetaTitle($data->get('meta_title'))
                ->setMetaDescription($data->get('meta_description'))
                ->setEmail($data->get('email'))
                ->setApikey($data->get('apikey'))
                ->setGoogleAnalyticsId($data->get('google_analytics_id'))
                ->setDomain($data->get('domain'))
                ->setActive(1);

            $em = $this->getDoctrine()->getManager();
            $em->persist($site);
            $em->flush();
            return $this->RedirectToRoute('sites');
        }

        return $this->render('site/create-site.html.twig', [
            'site' => $site,
            'action' =>  $action,
            'title' => $title
        ]);
    }

    /*
        Recibe un objeto de tipo Post por parámetro
        en el caso de estar activo lo desactiva y 
        viceversa. Redirige a la vista posts.
    */

    public function activeSite(Site $site){
        
        $em = $this->getDoctrine()->getManager();

        if ($site->getActive() == true) {

            $site->setActive(0);
            $em->persist($site);
            $em->flush();
        
        } else {

            $site->setActive(1);
            $em->persist($site);
            $em->flush();
        }

        return $this->RedirectToRoute('sites');
    }

}
