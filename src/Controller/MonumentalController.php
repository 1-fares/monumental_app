<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\Monument;
use App\Elastic\ElasticClient as ES;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class MonumentalController extends AbstractController {

	/**
	* @Route("/", name="index")
	*/
	public function index() {
		$message = "";
		$message .= ES::elasticPost('1', ["title" => "first title"])['status'];
		$message .= ES::elasticGet('1')['status'];
		$message .= ES::elasticDelete('1')['status'];

		return $this->render('monumental/index.html.twig', [
			'message' => $message,
			]);
	}

	/**
	* @Route("/all_monuments", name="all_monuments")
	*/
	public function all_monuments() {
		$message = "";
		$result = ES::elasticGet('_search?pretty=true',
		       	true ? null :(
				['query' => ['match_all' => [''=>'']],
				'stored_fields' => []])
			);
//		$message .= $result['status'];
//		$message .= "\n";
//		$message .= json_encode($result['body']);
		$body = $result['body'];
//		$message .= "\nTotal hits: ";
		$hits = $body['hits']['total'];
//		$message .= $hits;
//		$message .= "\nIndices: ";
		$hits = $body['hits']['hits'];
		foreach ($hits as $hits_key => $hit) {
//			$message .= "\n\tid: " . $hit['_id'];
//			$message .= "\n\tname:" . $hit['_source']['name'];
			if (!isset($hit['_source']['images'])) {
				$hit['_source']['images'] = "";
				$hits[$hits_key] = $hit;
//			} else {
//				$message .= "\n\timages:" . $hit['_source']['images'];
			}
		}

		return $this->render('monumental/all.html.twig', [
			'message' => $message,
			'hits' => $hits,
			]);
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

			if ($filename) {
				$file_contents = file_get_contents($filename);
				$b64_images = $file_contents === false ? "" : base64_encode($file_contents);
			}
			$message = "b64file is \"$b64_images\"\n";

//			echo "<img src=\"data:image/png;base64,$b64_images\">";

			$message = ES::elasticPost('2', [
				'name' => $monument->getName(),
				'images' => $b64_images,
			])['status'];
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
