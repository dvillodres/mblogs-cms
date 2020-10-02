<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DocsController extends AbstractController
{

	/*
	 * Renderizamos una vista, en este caso no hay interacción con la bbdd
     */
    public function index()
    {
        return $this->render('docs/index.html.twig', [
        ]);
    }


}
