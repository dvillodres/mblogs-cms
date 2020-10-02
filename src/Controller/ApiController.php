<?php

namespace App\Controller;

// Importo las entidades que voy a utilizar
use App\Entity\Post;
use App\Entity\Site;
use App\Entity\Category;
use App\Entity\Comment;

/* Importo las dependencias que voy a neccesitar */
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Query\ResultSetMapping;

class ApiController extends AbstractController{

	// Devuelve los datos de un sitio en base a su apikey

	public function getSiteData(Site $site = NULL){
    
    	// Se comprueba que llegue la apikey
		if ($site == NULL){
			$respuesta = array(
				'response' => ['400' => 'Bad Request']
			);

			return new JsonResponse($respuesta);
		}

		// Se comprueba que el sitio esté activo
		if ($site->getActive() == 0){
			$respuesta = array(
				'response' => ['401' => 'Unauthorized']
			);

			return new JsonResponse($respuesta);
		}

		$siteData= array();
		$siteData['id'] = $site->getId();
		$siteData['name'] = $site->getName();
		$siteData['metaTitle'] = $site->getMetaTitle();
		$siteData['metaDescription'] = $site->getMetaDescription();
		$siteData['email'] = $site->getEmail();
		$siteData['googleId'] = $site->getGoogleAnalyticsId();
		
		$response = array(
			'response' => ['200' => 'Ok'],
			'data' => $siteData
		);
	
		return new JsonResponse($response);
	}

	// Devuelve todas las categorías para un sitio en base a su apikey	

	public function getSiteCategories(Site $site = NULL){
    

		if ($site == NULL){

			$respuesta = array(
				'response' => ['400' => 'Bad Request']
			);

			return new JsonResponse($respuesta);

		}

		if ($site->getActive() == 0){
			$respuesta = array(
				'response' => ['403' => 'Inactive site']
			);

			return new JsonResponse($respuesta);
		}

        $category_repo = $this->getDoctrine()->getRepository(Category::Class);
        $categories = $category_repo->findBy(['site' => $site , 'active' => 1]);
		$categoriesArray = array();

		foreach ($categories as $category) {
			$categoryData= array();
			$categoryData['id'] = $category->getId();
			$categoryData['name'] = $category->getName();
			$categoryData['metaTitle'] = $category->getMetaTitle();
			$categoryData['metaDescription'] = $category->getMetaDescription();
			$categoryData['slug'] = $category->getSlug();
			$categoriesArray[] = $categoryData;
		}

		
		$response = array(
			'response' => ['200' => 'Ok'],
			'data' => $categoriesArray
		);
		
		return new JsonResponse($response);
	}

	// Devuelve todos los posts de un sitio en base a su apikey

	public function getPosts(Site $site = NULL){


		if ($site == NULL){
			$respuesta = array(
				'response' => ['400' => 'Bad Request']
			);

			return new JsonResponse($respuesta);
		}

		if ( $site->getActive() == 0){
			$respuesta = array(
				'response' => ['401' => 'Unauthorized']
			);

			return new JsonResponse($respuesta);
		}
		

		// Para esta consulta que es un poco más compleja, la escribo directamente en SQL
		$sql = "SELECT post.id, post.title, post.content, post.slug, post.image, categories.name as category, post.active FROM post INNER JOIN categories ON categories.id = post.category_id INNER JOIN sites ON categories.site_id = sites.id WHERE post.active = 1 AND sites.active = 1 AND categories.active = 1 AND apikey = '" . $site->getApikey() . "'";
		$connection = $this->getDoctrine()->getConnection();
		$prepare = $connection->prepare($sql);
		$prepare->execute();
		$posts = $prepare->fetchAll();


		$postsArray = array();
		foreach ($posts as $post) {

			$postData = array();

			if ($post['active'] == 1) {
				$postData['id'] = $post['id'];
				$postData['title'] = $post['title'];
				$postData['content'] = $post['content'];
				$postData['category'] = $post['category'];
				$postData['slug'] = $post['slug'];
				$postData['image'] = $post['image'];
			}

			$postsArray[] = $postData; 

		}

		$response = array(
			'response' => ['200' => 'Ok'],
			'data' => $postsArray
		);
		return new JsonResponse($response);
	}

	// Devuelve x posts de un sitio en base a su apikey y a la cantidad pasada

	public function getLastsPosts(Site $site = NULL, $quantity){


		if ($site == NULL){
			$respuesta = array(
				'response' => ['400' => 'Bad Request']
			);

			return new JsonResponse($respuesta);
		}

		if ( $site->getActive() == 0){
			$respuesta = array(
				'response' => ['401' => 'Unauthorized']
			);

			return new JsonResponse($respuesta);
		}


		// Para esta consulta que es un poco más compleja, la escribo directamente en SQL
		$sql = "SELECT post.id, post.title, post.content, post.slug, post.image, post.meta_title, post.meta_description, categories.name as category, post.active FROM post INNER JOIN categories ON categories.id = post.category_id INNER JOIN sites ON categories.site_id = sites.id WHERE sites.active = 1 AND post.active = 1 AND categories.active = 1 AND apikey = '" . $site->getApikey() . "' ORDER BY post.id DESC LIMIT " . $quantity;
		$connection = $this->getDoctrine()->getConnection();
		$prepare = $connection->prepare($sql);
		$prepare->execute();
		$posts = $prepare->fetchAll();


		$postsArray = array();
		foreach ($posts as $post) {

			$postData = array();

			if ($post['active'] == 1) {
				$postData['id'] = $post['id'];
				$postData['title'] = $post['title'];
				$postData['metaTitle'] = $post['meta_title'];
				$postData['description'] = $post['meta_description'];
				$postData['content'] = $post['content'];
				$postData['category'] = $post['category'];
				$postData['slug'] = $post['slug'];
				$postData['image'] = $post['image'];
			}

			$postsArray[] = $postData; 

		}

		$response = array(
			'response' => ['200' => 'Ok'],
			'data' => $postsArray
		);
		return new JsonResponse($response);
	}

	// Devuelve todos los posts de una categoría en base al slug de la categoría y la apikey del sitio

	public function getPostsByCategory(Site $site = NULL, $categorySlug){

        $post_repo = $this->getDoctrine()->getRepository(Post::Class);
        $category_repo = $this->getDoctrine()->getRepository(Category::Class);

        $category = $category_repo->findOneBy(['slug' => $categorySlug]);


		if ($site == NULL || $category == NULL){
			$respuesta = array(
				'response' => ['400' => 'Bad Request']
			);

			return new JsonResponse($respuesta);
		}


        $posts = $post_repo->findBy(
                    ['category' => $category,
                     'active' => 1
                	]
                );

		$postsArray = array();
		foreach ($posts as $post) {
			$postData = array();
			$postData['id'] = $post->getId();
			$postData['title'] = $post->getTitle();
			$postData['metaTitle'] = $post->getMetaTitle();
			$postData['description'] = $post->getMetaDescription();
			$postData['content'] = $post->getContent();
			$postData['category'] = $post->getCategory();
			$postData['slug'] = $post->getSlug();
			$postData['image'] = $post->getImage();
			$postsArray[] = $postData; 
		}


		$response = array(
			'response' => ['200' => 'Ok'],
			'data' => $postsArray
		);
		return new JsonResponse($response);
	}

	// Obtiene un post en base al slug del post y la apikey del sitio

	public function getPostBySlug(Site $site = NULL, $postSlug){
    
        $post_repo = $this->getDoctrine()->getRepository(Post::Class);
        $post = $post_repo->findOneBy(['slug' => $postSlug, 'active' => 1]);


		if ($site == NULL || $post == NULL || $post->getCategory()->getActive() == 0){
			$respuesta = array(
				'response' => ['400' => 'Bad Request']
			);

			return new JsonResponse($respuesta);
		}




		if ($site->getActive() == 0){
			$respuesta = array(
				'response' => ['401' => 'Unauthorized']
			);

			return new JsonResponse($respuesta);
		}
		
		$postsArray = array();

		$postData = array();
		$postData['id'] = $post->getId();
		$postData['title'] = $post->getTitle();
		$postData['content'] = $post->getContent();
		$postData['categoryName'] = $post->getCategory()->getName();
		$postData['categorySlug'] = $post->getCategory()->getSlug();
		$postData['slug'] = $post->getSlug();
		$postData['image'] = $post->getImage();
		$postsArray[] = $postData; 


		$response = array(
			'response' => ['200' => 'Ok'],
			'data' => $postsArray
		);
		return new JsonResponse($response);
	}

// Obtiene una categoría en base al slug del post y la apikey del sitio

	public function getCategoryBySlug(Site $site = NULL, $categorySlug){
    
        $category_repo = $this->getDoctrine()->getRepository(Category::Class);
        $category = $category_repo->findOneBy(['slug' => $categorySlug, 'active' => 1]);


		if ($site == NULL || $category == NULL){
			$respuesta = array(
				'response' => ['400' => 'Bad Request']
			);

			return new JsonResponse($respuesta);
		}

		if ($site->getActive() == 0){
			$respuesta = array(
				'response' => ['401' => 'Unauthorized']
			);

			return new JsonResponse($respuesta);
		}
		
		$categoriesArray = array();

			$categoryData= array();
			$categoryData['id'] = $category->getId();
			$categoryData['name'] = $category->getName();
			$categoryData['metaTitle'] = $category->getMetaTitle();
			$categoryData['metaDescription'] = $category->getMetaDescription();
			$categoryData['slug'] = $category->getSlug();
			$categoriesArray[] = $categoryData; 


		$response = array(
			'response' => ['200' => 'Ok'],
			'data' => $categoriesArray
		);
		return new JsonResponse($response);
	}


	// Obtiene los comentarios de un post enn base a la api key y el slug del post

	public function getCommnetsByPostSlug(Site $site = NULL, $postSlug){
    
        $post_repo = $this->getDoctrine()->getRepository(Post::Class);
        $post = $post_repo->findOneBy(['slug' => $postSlug]);


		if ($site == NULL || $post == NULL){
			$respuesta = array(
				'response' => ['400' => 'Bad Request']
			);

			return new JsonResponse($respuesta);
		}

		if ($site->getActive() == 0){
			$respuesta = array(
				'response' => ['401' => 'Unauthorized']
			);

			return new JsonResponse($respuesta);
		}
    
        $comment_repo = $this->getDoctrine()->getRepository(Comment::Class);
        $comments = $comment_repo->findBy(['post' => $post]);

		$commentsArray = array();

		foreach ($comments as $comment) {
			$commentData = array();
			$commentData['id'] = $comment->getId();
			$commentData['content'] = $comment->getContent();
			$commentData['email'] = $comment->getEmail();
			$commentData['name'] = $comment->getName();
			$commentData['create_date'] = $comment->getCreateDate();
			$commentsArray[] = $commentData; 
		}

		$response = array(
			'response' => ['200' => 'Ok'],
			'data' => $commentsArray
		);
		return new JsonResponse($response);
	}

	// Recibe los datos de un comentario y crea el registro en la bbdd

	public function createComment(Request $request){
		
		$post_repo = $this->getDoctrine()->getRepository(Post::class);

		if ($post_repo->findOneBy(['id' => $request->request->get('postId')])) {
					
			$post = $post_repo->findOneBy(['id' => $request->request->get('postId')]);

			$em = $this->getDoctrine()->getManager();

			$comment = new Comment();

			$comment
				->setActive(0)
				->setContent($request->request->get('content'))
				->setEmail($request->request->get('email'))
				->setName($request->request->get('name'))
				->setPost($post)
				->setCreateDate(new \Datetime('now'));

			$em->persist($comment);
			$em->flush();

			$response = array(
				'response' => ['200' => 'Ok'],
			);
			return new JsonResponse($response);
		}




		$respuesta = array(
			'response' => ['400' => 'Bad Request']
		);

		return new JsonResponse($respuesta);
	}
}


