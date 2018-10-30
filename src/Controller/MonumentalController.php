<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Monument;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class MonumentalController extends AbstractController {

	private function elasticRequest($method, $queryURL, $json=null) {
		$message = "";
		$message .= "elasticsearch\n";
		$client = new GuzzleClient();
		$result = null;

		try {
			$result = $client->request($method, "http://elasticsearch:9200$queryURL", $json ? [ 'json' => $json ] : []);
			$statusCode = $result->getStatusCode();
			$message .= "$statusCode\n";
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

	/**
	* @Route("/", name="index")
	*/
	public function index() {
		$message = $this->elasticRequest('POST', '/monumental/building/1', ["title" => "first title"]);
		$message .= $this->elasticRequest('GET', '/monumental/building/1');
		$message .= $this->elasticRequest('DELETE', '/monumental/building/1');
		return $this->render('monumental/index.html.twig', [
			'controller_name' => 'MonumentalController',
			'message' => $message,
			]);
	}

	/**
	* @Route("/add_monument", name="add_monument")
	*/
	public function add_monument(Request $request) {
		$monument = new Monument();

		$formbuilder = $this->createFormBuilder($monument);
		$formbuilder->add('name', TextType::class, array('attr' => array('class' => 'form-control')));
		$formbuilder->add('save', SubmitType::class, array('label' => 'Create', 'attr' => array('class' => 'btn btn-primary mt-3')));
		$form = $formbuilder->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$monument = $form->getData();
			//TODO: save in elasticsearch

			return $this->redirectToRoute("index");
		}

		return $this->render("monumental/new.html.twig", [
			'form' => $form->createView(),
		]);
	}
}
