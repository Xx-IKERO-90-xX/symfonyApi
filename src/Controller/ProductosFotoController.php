<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Producto;
use App\Entity\ProductoFoto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;



class ProductosFotoController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/productos/foto/{id}', methods:['GET'])]
    public function index(int $id): JsonResponse
    {
        $producto = $this->entityManager->getRepository(Producto::class)->find($id);
        if(!$producto)
        {
            return $this->json([
                'estado'=>'error',
                'mensaje'=>'La URL no está disponible en este momento :)'
            ], 404);
        }
        $datos = $this->entityManager->getRepository(ProductoFoto::class)->findBy(array('producto'=>$id), array('id'=>'asc'));
        $result = [];
        foreach($datos as $dato){
            $result[]=['id'=>$dato->getId(), 'foto'=>$dato->getFoto(), 'foto'=>'http://127.0.0.1:8000/uploads/archivos/'.$dato->getFoto(), 'producto_id'=>$dato->getProducto()->getId()];
        }
        return $this->json($result);
    }

    #[Route('/productos/foto/{id}', methods:['POST'])]
    public function uploadPost(Request $request, int $id): JsonResponse
    {
        $foto = $request->files->get('foto');
        if ($foto)
        {
            $newFilename = time().'.'.$foto->guessExtension();
            try {   
                $foto->move(
                    $this->getParameter('archivos'),
                    $newFilename
                );
                $producto = $this->entityManager->getRepository(Producto::class)->find($id);
                $entity = new ProductoFoto();
                $entity->setFoto($newFilename);
                $entity->setProducto($producto);
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
                return $this->json([
                    "estado" => 'ok',
                    "mensaje" => "Se ha creado el registro de la foto del producto exitosamente :)"
                ], 200);
            }
            catch (FileException $th) {
                return $this->json([
                    'estado'=> 'error',
                    'mensaje' => 'No se ha podido subir la foto :(',
                    'excepcion' => $th
                ], 400);
            }
        }
    }
    
    #[Route('/productos/foto/download/{id}', methods:['GET'])]
    public function descargarFoto(int $id): BinaryFileResponse
    {
        $entity = $this->entityManager->getRepository(ProductoFoto::class)->find($id);
        if (!$entity)
        {
            return $this->json([
                "estado"=>"error",
                "mensaje"=>"La URL no está disponible en este momento."
            ], 404);
        }
        $ruta = getcwd();
        return $this->file("{$ruta}/uploads/archivos/{$entity->getFoto()}");
    }

    #[Route('/productos/foto/{id}', methods:['DELETE'])]
    public function eliminarFoto(int $id): JsonResponse
    {
        $entity = $this->entityManager->getRepository(ProductoFoto::class)->find($id);
        if (!$entity)
        {
            return $this->json([
                "estado"=>"error",
                "mensaje"=>"La URL no está disponible en este momento."
            ], 404);
        }
        unlink(getcwd().'/uploads/productos/'.$entity->getFoto());
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        return $this->json([
            'estado'=>'ok',
            "mensaje"=>'Se ha eliminado la foto correctamente :)'
        ], 200);

    }
}
