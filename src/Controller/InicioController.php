<?php

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class InicioController extends Controller
{
   /**
    * @Route("/", name="inicio")
    */    
    public function inicio(Request $request)
    {
        return $this->render('inicio.html.twig');
    }


	/**
	 * 	 * @Route("/documetal/{modulo}",requirements={"modulo":"\d+"},
	 *       defaults={"modulo"=null},
	 *      name="inicio_documetal_lista")
	 */
	public function documental(Request $request, $modulo)
	{
		$form = $this->createFormBuilder()
			->add('referencia', TextType::class, array('required' => false ))
			->add('btnFiltro', SubmitType::class, array('label' => 'Filtrar'))
			->getForm();
		$form->handleRequest($request);
		$raw=[];
		$arrModulos = [
			'Todos' => null,
			'Cartera' => 1,
			'Crm' => 2,
			'Documental' =>3,
			'Financiero'=>4,
			'General'=>5,
			'Inventario'=>6,
			'RecursoHumano'=>7,
			'Seguridad'=>8,
			'Tesoreria'=>9,
			'Transporte'=>10,
			'Turno'=>11
		];
		switch ($modulo){
			case 1:
				$modulo='Cartera';
				break;
			case 2:
				$modulo='Crm';
				break;
			case 3:
				$modulo='Documental';
				break;
			case 4:
				$modulo='Financiero';
				break;
			case 5:
				$modulo='General';
				break;
			case 6:
				$modulo='Inventario';
				break;
			case 7:
				$modulo='RecursoHumano';
				break;
			case 8:
				$modulo='Seguridad';
				break;
			case 9:
				$modulo='Tesoreria';
				break;
			case 10:
				$modulo='Transporte';
				break;
			case 11:
				$modulo='Turno';
				break;
		}
		$raw['filtro'] = ['modulo'=>$modulo];
		if ($form->isSubmitted()) {
			if ($form->get('btnFiltro')->isClicked()) {
				/**
				 * combinar el array
				 */
				$raw['filtro'] = array_merge($raw['filtro'], ['criterio' => $form->get('referencia')->getData()]);
			}
		}
		$arRegistros = $this->datosLista($raw);
		return $this->render('documentacion/lista.html.twig', [
			'form' => $form->createView(),
			'arRegistros' => $arRegistros,
			'arrModulos'=>$arrModulos,
		]);

	}

	/**
	 *
	 * @Route("/documetal/detalle/{id}", name="inicio_documetal_detalle")
	 */
	public function detalle(Request $request, $id)
	{

		$arRegistro = $this->datosDetalle($id);
		return $this->render('documentacion/detalle.html.twig', [
			'arRegistro' => $arRegistro,
		]);

	}


	public function datosLista ($raw)
	{
		$filtros = $raw['filtro'] ?? null;
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
}

