<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class ApirestController extends AbstractController
{
    #[Route('/apirest', methods: ['GET'])]
    public function metodoGet(): JsonResponse
    {
        return $this->json([
            'estado' => 'ok',
            'path' => 'Hola mundo con GET',
        ], 202);
    }

    #[Route('/apirest/{id}', methods: ['GET'])]
    public function metodoGet2(int $id=0): JsonResponse
    {
        return $this->json([
            'estado' => 'ok',
            'path' => "Hola mundo con GET | id={$id}",
        ], 202);
    }

    #[Route('/apirest/{id}', methods: ['POST'])]
    public function metodoPost(int $id): JsonResponse
    {
        return $this->json([
            'estado' => 'ok',
            'mensaje' => 'Hola mundo con POST | id='.$id,
        ]);
    }
    #[Route('/apirest', methods: ['POST'])]
    public function metodoPost2(): JsonResponse
    {
        return $this->json([
            'estado' => 'ok',
            'mensaje' => 'Hola mundo con POST',
        ]);
    }

    #[Route('/apirest', methods: ['PUT'])]
    public function metodoPUT(): JsonResponse
    {
        return $this->json([
            'estado' => 'ok',
            'mensaje' => 'Hola mundo con PUT',
        ]);
    }

    #[Route('/apirest', methods: ['DELETE'])]
    public function metodoDELETE(): JsonResponse
    {
        return $this->json([
            'estado' => 'ok',
            'mensaje' => 'Hola mundo con DELETE',
        ]);
    }

    #[Route('/apirestQuery', methods: ['GET'])]
    public function queryString(Request $request): JsonResponse
    {
        return $this->json([
            'estado'=> 'ok',
            'mensaje'=> 'metodo GET | id = '.$request->request->get('id'),
        ]);
    }

    #[Route('/apirestRequest', methods: ['POST'])]
    public function metodoPostRequest(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        //print_r($data); exit();
        return $this->json([
            'estado'=>'ok',
            'mensaje'=>'metodo POST',
            'correo'=>$data['correo'],
            'password'=>$data['password']
        ]);
    }

    #[Route('/apirestRequest', methods: ['GET'])]
    public function headerRequest(Request $request): JsonResponse
    {
        if ($request->headers->get('Agencia')) {
             return $this->json([
                'estado'=>'ok',
                'mensaje'=>'metodo POST',
                'miHeader'=>$request->headers->get('Agencia')
            ]);
        }
        else if ($request->headers->get('Authorization')){
            return $this->json([
                'estado'=>'ok',
                'mensaje'=>'metodo POST',
                'miHeader'=>$request->headers->get('Authorization')
            ]);
        }
        else {
            die('No se le ha pasado ningun header :(');
        }
    }

    #[Route('/apirestRequestCustom', methods: ['GET'])]
    public function headerRequestCustom(): Response
    {
        $response = new Response(json_encode(array(
            'estado'=>'ok',
            'mensaje'=>'mensaje desde GET :)'
        )));
        $response->headers->set('ikero', 'www.ikerowebpage.com');
        return $response;
    }
}
