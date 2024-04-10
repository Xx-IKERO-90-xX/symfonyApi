<?php

namespace App\Controller;

use App\Entity\Categoria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


class DoctrineCategoriasController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/doctrine/categorias', methods:['GET'])]
    public function listarCategorias(): JsonResponse
    {
        $datos = $this->entityManager->getRepository(Categoria::class)->findBy(array(), array('id'=>'asc'));
        return $this->json($datos);
    }
    #[Route('/doctrine/categorias/{id}', methods:['GET'])]
    public function inlistarCategoriasPorId(int $id): JsonResponse
    {
        $datos = $this->entityManager->getRepository(Categoria::class)->find($id);
        if(!$datos)
        {
            return $this->json([
                'estado' => 'error',
                'mensaje' => 'La URL no está disponible en este momento :)'
            ]);
        }
        return $this->json($datos);
    }

    #[Route('/doctrine/categorias/add', methods:['POST'])]
    public function nuevaCategoria(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['descripcion']))
        {
            return $this->json([
                'estado' => 'error',
                'mensaje' => 'El campo nombre es obligatorio >:('
            ]);
        }
        $entity = new Categoria($data['descripcion']);
        $entity->setDescripcion($data['descripcion']);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        return $this->json([
            'estado' => 'ok',
            'mensaje' => 'Se ha creado la categoría exitosamente :)'
        ], 201);
    }

    #[Route('/doctrine/categorias/edit/{id}', methods:['PUT'])]
    public function editarCategoria(int $id, Request $request): JsonResponse
    {
        $datos = $this->entityManager->getRepository(Categoria::class)->find($id);
        if (!$datos)
        {
            return $this->json([
                "estado"=>"error",
                "mensaje"=>"La URL no está disponible en este momento :)"
            ], 404);
        }
        $data = json_decode($request->getContent(), true);
        if (!isset($data['descripcion']))
        {
            return $this->json([
                'estado'=>'error',
                'mensaje'=>'El campo descripcion es obligatorio >:('
            ], 200);
        }
        $datos->setDescripcion($data['descripcion']);
        $this->entityManager->persist($datos);
        $this->entityManager->flush();
        return $this->json([
            'estado' => 'ok',
            'mensaje' => 'Se ha modificado la categoria correctamente :)'
        ], 201);

    }

    #[Route('/doctrine/categorias/delete/{id}', methods:["DELETE"])]
    public function borrarCategoria(int $id): JsonResponse
    {
        $categoria = $this->entityManager->getRepository(Categoria::class)->find($id);
        if (!$categoria)
        {
            return $this->json([
                "estado"=> 'error',
                "mensaje" => "La categoria solicitada no existe :("
            ]);
        }
        else
        {
            $this->entityManager->remove($categoria);
            $this->entityManager->flush();
            return $this->json([
                "estado"=>'ok',
                "mensaje"=>'Se ha eliminado la categoria solicitada :)'
            ]);
        }
    }
}
