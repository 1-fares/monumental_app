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
//		$message .= ES::post('1', ["title" => "first title"])['status'];
//		$message .= ES::get('1')['status'];
//		$message .= ES::delete('1')['status'];

		return $this->render('monumental/index.html.twig', [
			'message' => $message,
			]);
	}

	/**
	* @Route("/all", name="all")
	*/
	public function all() {
		$message = "";
		$result = ES::get('_search');
//$message .= $result['status'];
//$message .= "\n";
//$message .= json_encode($result['body']);
		$body = $result['body'];

		return $this->render('monumental/all.html.twig', [
			'message' => $message,
			'hits' => $result['body']['hits']['hits'],
			]);
	}

	/**
	* @Route("/search", name="search")
	*/
	public function search(Request $request) {
		$message = "";
		//$message = var_export($request->request->all(), true);
		$message = 'Searched for: ' . $request->request->get('search_term');
		$search_term = $request->request->get('search_term');
		$result = ES::get('_search',
			['query' => ['multi_match' => ['query' => $search_term]]]
		);
		$body = $result['body'];

		return $this->render('monumental/all.html.twig', [
			'message' => $message,
			'hits' => $result['body']['hits']['hits'],
			]);
	}

	/**
	* @Route("/add", name="add")
	*/
	public function add(Request $request) {
		$monument = new Monument();

		$form = $this->createFormBuilder($monument)
			->add('name', TextType::class, array('attr' => array('class' => 'form-control'), 'label' => "Monument Name"))
			->add('description', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('location', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('date', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('height', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('unesco_status', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('builder', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('purpose', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('condition', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('major_event', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('tags', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('image', FileType::class, array('required' => false, 'label' => "Image"))

			->add('save', SubmitType::class, array('label' => 'Create', 'attr' => array('class' => 'btn btn-primary mt-3 btn-block')))
			->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$monument = $form->getData();

			$b64_image = "";
			$filename = $form['image']->getData();

			if ($filename) {
				$file_contents = file_get_contents($filename);
				$b64_image = $file_contents === false ? "" : base64_encode($file_contents);
			}
			$message = "b64file is \"$b64_image\"\n";

			$result = ES::post('', [
				'name' => $monument->getName(),
				'description' => $monument->getDescription(),
				'location' => $monument->getLocation(),
				'date' => $monument->getDate(),
				'height' => $monument->getHeight(),
				'unesco_status' => $monument->getUnescoStatus(),
				'builder' => $monument->getBuilder(),
				'purpose' => $monument->getPurpose(),
				'condition' => $monument->getCondition(),
				'major_event' => $monument->getMajorEvent(),
				'tags' => $monument->getTags(),
				'image' => $b64_image,
			]);
			$message = $result['status'];

			return $this->redirect('/view/' . $result['body']['_id']);
/*			return $this->render('monumental/index.html.twig', [
				'message' => $message,
			]);*/
		}

		return $this->render("monumental/new.html.twig", [
			'form' => $form->createView(),
		]);
	}

	/**
	* @Route("/view/{id}", name="view")
	*/
	public function view(Request $request, $id) {
		$message = '';
		$result = ES::get($id);

		return $this->render('monumental/view.html.twig', [
			'message' => $message,
			'monument' => $result['body']['_source'],
			'id' => $id,
			]);
	}


	/**
	* @Route("/edit/{id}", name="edit")
	*/
	public function edit(Request $request, $id) {
		$message = '';
		$result = ES::get($id);
		$src = $result['body']['_source'];
		$src['id'] = $id; // because it has it as '_id', and it's useful to have it without the underscore

		$monument = new Monument();
		$monument->setValuesFromArray($src);

		$formBuilder = $this->createFormBuilder($monument)
			->add('name', TextType::class, array('attr' => array('class' => 'form-control'), 'label' => "Monument Name"))
			->add('description', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('location', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('date', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('height', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('unesco_status', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('builder', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('purpose', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('condition', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('major_event', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('tags', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
			->add('image', FileType::class, array('required' => false, 'label' => "Image"))

			->add('save', SubmitType::class, array('label' => 'Save', 'attr' => array('class' => 'btn btn-primary mt-3 btn-block')));

		$form = $formBuilder->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$monument = $form->getData();

			$values = [
				'name' => $monument->getName(),
				'description' => $monument->getDescription(),
				'location' => $monument->getLocation(),
				'date' => $monument->getDate(),
				'height' => $monument->getHeight(),
				'unesco_status' => $monument->getUnescoStatus(),
				'builder' => $monument->getBuilder(),
				'purpose' => $monument->getPurpose(),
				'condition' => $monument->getCondition(),
				'major_event' => $monument->getMajorEvent(),
				'tags' => $monument->getTags(),
			];

			// special image handling due to need to encode
			$b64_image = "";
			$filename = $form['image']->getData();
			if ($filename) {
				$file_contents = file_get_contents($filename);
				$b64_image = $file_contents === false ? "" : base64_encode($file_contents);
				$values['image'] = $b64_image;
			}
			$message = "b64file is \"$b64_image\"\n";

			$message = ES::update($id, $values)['status'];

			return $this->redirect("/view/$id");
		}

		return $this->render('monumental/edit.html.twig', [
			'message' => $message,
			'form' => $form->createView(),
			'referer' => $request->headers->get('referer'),
			]);
	}

	/**
	* @Route("/delete/{id}", name="delete")
	*/
	public function delete(Request $request, $id) {
		ES::delete($id);

		$redirect_to = $request->headers->get('referer');

		if (strpos($redirect_to, 'view/') !== false) $redirect_to = '/'; // don't send back to view what we just deleted

		return $this->redirect($redirect_to);
	}
}
