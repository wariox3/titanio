<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;


class documentacionController extends AbstractController
{
	/**
	 * @Route("/documentacion", name="documentacion")
	 */
	public function lista(Request $request)
	{
		$session = new Session();
		$raw = [
			'filtros' => $session->get('filtroDocumentacion')
		];
		$arrModulos = [
			'Todos' =>          null,
			'Cartera' =>        'Cartera',
			'Crm' =>            'CRM',
			'Documental' =>     'Documental',
			'Financiero' =>     'Fiannciero',
			'General' =>        'General',
			'Inventario' =>     'Inventario',
			'RecursoHumano' =>  'RecursoHumano',
			'Seguridad' =>      'Seguridad',
			'Tesoreria' =>      'Tesoreria',
			'Transporte' =>     'Transporte',
			'Turno'=>           'Turno'
		];
		$form = $this->createFormBuilder()
			->add('criterio', TextType::class, array('required' => false ))
			->add('btnFiltro', SubmitType::class, array('label' => 'Filtrar'))
			->add('modulo', ChoiceType::class, ['choices' => $arrModulos, 'required' => false, 'data' => $raw['filtros']['modulo']?$raw['filtros']['modulo']:null])
			->getForm();
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			if ($form->get('btnFiltro')->isClicked()) {
				$raw['filtros'] = $this->getFiltros($form);
			}
		}
		$arRegistros = $this->datosLista($raw);
		return $this->render('documentacion/lista.html.twig', [
			'arRegistros' => $arRegistros,
			'form' => $form->createView(),
			'arrModulos'=>$arrModulos,
		]);
	}

	/**
	 *@Route("/documentacion/detalle/{id}", name="documentacion_detalle")
	 */
	public function detalle(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$arRegistro = $this->datosDetalle($id);
		return $this->render('documentacion/detalle.html.twig', [
			'arRegistro' => $arRegistro,
		]);
	}

	public function datosLista ($raw)
	{
		$filtros = $raw['filtros'] ?? null;
		$criterio = null;
		$modulo = null;
		if ($filtros) {
			$criterio = $filtros['criterio'] ?? null;
			$modulo = $filtros['modulo'] ?? null;
		}
		$datosJson = json_encode(['criterio'=> $criterio, 'modulo'=>$modulo]);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://165.22.222.162/mai/public/index.php/api/documentacion/lista');
//		curl_setopt($ch, CURLOPT_URL, 'http://localhost/rubidio/public/index.php/api/documentacion/lista');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $datosJson);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($datosJson))
		);
		$respuesta = curl_exec($ch);
		curl_close($ch);
		$respuesta = json_decode($respuesta);
		return $respuesta;
	}

	public function datosDetalle ($id)
	{
		$ch = curl_init();;
		$datosJson = json_encode(['id' =>$id]);
		curl_setopt($ch, CURLOPT_URL, "http://165.22.222.162/mai/public/index.php/api/documentacion/detalle");
//		curl_setopt($ch, CURLOPT_URL, 'http://localhost/rubidio/public/index.php/api/documentacion/detalle');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $datosJson);
		$respuesta = curl_exec($ch);
		curl_close($ch);
		$respuesta = json_decode($respuesta);
		return $respuesta;
	}

	public function getFiltros($form)
	{
		$session = new Session();
		$filtro = [
			'modulo' => $form->get('modulo')->getData(),
			'criterio' => $form->get('criterio')->getData(),
		];
		$session->set('filtroDocumentacion', $filtro);
		return $filtro;
	}
}