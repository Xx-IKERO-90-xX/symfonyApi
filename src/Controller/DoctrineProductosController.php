<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Producto;
use App\Entity\Categoria;
use App\Entity\ProductoFoto;

class DoctrineProductosController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/doctrine/productos', methods:["GET"])]
    public function mostrarProductos(): JsonResponse
    {
        $datos = $this->entityManager->getRepository(Producto::class)->findBy(array(), array('id'=>'asc'));
        return $this->json($datos);
    }

    #[Route('/doctrine/productos/{id}', methods:['GET'])]
    public function mostrarProductosID(int $id): JsonResponse
    {
        $datos = $this->entityManager->getRepository(Producto::class)->find($id);
        if (!$datos)
        {
            return $this->json([
                "estado"=>"error",
                "mensaje"=>"No existe el producto solicitado :("
            ], 404);
        }
        else
        {
            return $this->json($datos);
        }
    }

    #[Route('/doctrine/productos', methods:["POST"])]
    public function nuevoProducto(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['descripcion']) && isset($data["precio"]) && isset($data['stock']) && isset($data['categoria_id']))
        {
            $categoria = $this->entityManager->getRepository(Categoria::class)->find($data['categoria_id']);
            $entity = new Producto();
            $entity->setDescripcion($data['descripcion']);
            $entity->setPrecio($data['precio']);
            $entity->setStock($data['stock']);
            $entity->setCategoria($categoria);
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            return $this->json([
                "estado"=> "ok",
                "mensaje" => "Se ha creado el nuevo producto existosamente :)"
            ]);
        }
        else
        {
            return $this->json([
                "estado"=>"error",
                "mensaje"=>"Falta campos importantes a rellenar, revisali plis :)"
            ]);
        }
    }

    #[Route('/doctrine/categorias/{id}', methods:['PUT'])]
    public function editarProducto(int $id, Request $request): JsonResponse
    {
        $datos = $this->entityManager->getRepository(Producto::class)->find($id);
         if (!$datos)
        {
            return $this->json([
                "estado"=>"error",
                "mensaje"=>"La URL no está disponible en este momento :)"
            ], 404);
        }
        $data = json_decode($request->getContent(), true);
        if (isset($data['descripcion']) && isset($data["precio"]) && isset($data['stock']) && isset($data['categoria_id']))
        {
            $categoria = $this->entityManager->getRepository(Categoria::class)->find($data['categoria_id']);
            $datos->setDescripcion($data['descripcion']);
            $datos->setPrecio($data['precio']);
            $datos->setStock($data['stock']);
            $datos->setCategoria($categoria);
            $this->entityManager->persist($datos);
            $this->entityManager->flush();
            return $this->json([
                "estado"=> "ok",
                "mensaje" => "Se ha modificado el producto solicitado existosamente :)"
            ]);
        }
        else
        {
            return $this->json([
                "estado"=>"error",
                "mensaje"=>"Falta campos importantes a rellenar, revisali plis :)"
            ]);
        }
    }

    #[Route('/doctrine/productos/{id}', methods:['DELETE'])]
    public function borrarProducto(int $id): JsonResponse
    {
        $producto = $this->entityManager->getRepository(Producto::class)->find($id);
        if (!$producto)
        {
            return $this->json([
                "estado"=> "error",
                "mensaje"=> "No se ha encontrado el producto solicitado :(0"
            ], 404);
        }
        else
        {
            $this->entityManager->remove($producto);
            $this->entityManager->flush();
            return $this->json([
                "estado"=>"ok",
                "mensaje"=>"El producto solicitado se ha eliminado exitosamente :)"
            ]);
        }
    }
}
