<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Monument;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class MonumentalController extends AbstractController {

	private function elasticRequest($method, $queryURL="", $json=null) {
		$json_string = $json ? json_encode($json) : "";
		$message = "\nelasticsearch \"$method\" request: $queryURL\n body: $json_string";
		$client = new GuzzleClient();
		$result = null;

		try {
			$result = $client->request($method, "http://elasticsearch:9200$queryURL", $json ? [ 'json' => $json ] : []);
			$statusCode = $result->getStatusCode();
			$message .= "$statusCode\n";
			$message .= $result->getBody();
		} catch (RequestException $e) {
			$message .= Psr7\str($e->getRequest());
			$message .= "\n";
			if ($e->hasResponse()) {
				$message .= Psr7\str($e->getResponse());
				$message .= "\n";
			}
		}
		return $message;
	}

	private function elasticMonumentalRequest($method, $queryURL="", $json=null) {
		if (strlen($queryURL) > 0 && substr($queryURL, 0, 1) !== "/") $queryURL = "/$queryURL";

		return $this->elasticRequest($method, '/monumental/building' . $queryURL, $json);
	}

	private function elasticPost($queryURL="", $json=null) {
		return $this->elasticMonumentalRequest('POST', $queryURL, $json);
	}

	private function elasticGet($queryURL="", $json=null) {
		return $this->elasticMonumentalRequest('GET', $queryURL, $json);
	}

	private function elasticDelete($queryURL="", $json=null) {
		return $this->elasticMonumentalRequest('DELETE', $queryURL, $json);
	}

	/**
	* @Route("/", name="index")
	*/
	public function index() {
		$message = "";
		$message .= $this->elasticPost('1', ["title" => "first title"]);
		$message .= $this->elasticRequest('GET', '/monumental/building/1');
		$message .= $this->elasticRequest('DELETE', '/monumental/building/1');

		return $this->render('monumental/index.html.twig', [
			'controller_name' => 'MonumentalController',
			'message' => $message,
			]);
	}

	/**
	* @Route("/all_monuments", name="all_monuments")
	*/
	public function all_monuments() {
//		$message = $this->elasticRequest('GET', '/monumental/building'
	}

	/**
	* @Route("/add_monument", name="add_monument")
	*/
	public function add_monument(Request $request) {
		$monument = new Monument();

		$form = $this->createFormBuilder($monument)
			->add('name', TextType::class, array('attr' => array('class' => 'form-control'), 'label' => "Monument Name"))
			->add('location', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('date', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('height', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('unesco_status', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('builder', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('purpose', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('condition', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('major_event', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('tags', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('images', FileType::class, array('required' => false, 'label' => "Image"))

			->add('save', SubmitType::class, array('label' => 'Create', 'attr' => array('class' => 'btn btn-primary mt-3 btn-block')))
			->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$monument = $form->getData();

			$b64_images = "";
			$filename = $form['images']->getData();
//			$message = "file is $file\n";
			if ($filename) {
				$file_contents = file_get_contents($filename);
				$b64_images = $file_contents === false ? "" : base64_encode($file_contents);
			}
			$message = "b64file is \"$b64_images\"\n";

//			echo "<img src=\"data:image/png;base64,$b64_images\">";

			$message = $this->elasticPost('2', [
				'name' => $monument->getName(),
				'images' => $b64_images,
			]);
//			$message .= $this->elasticRequest('GET', '/monumental/_search?pretty=true&stored_fields=');
//			$message .= "\n<br>KHARA";
			//TODO: save in elasticsearch
//			$message .= $this->elasticRequest('GET', '/monumental/building/2');
//			$message .= "\n<br>ZIFT";

		//	return $this->redirectToRoute("index");
			return $this->render('monumental/index.html.twig', [
				'controller_name' => 'MonumentalController',
				'message' => $message,
				]);
		}

		return $this->render("monumental/new.html.twig", [
			'form' => $form->createView(),
		]);
	}
}
