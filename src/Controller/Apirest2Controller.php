<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class Apirest2Controller extends AbstractController
{
    #[Route('/upload', methods:['POST'])]
    public function uploadPost(Request $request): JsonResponse
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
            }
            catch (FileException $th) {
                return $this->json([
                    'estado'=> 'error',
                    'mensaje' => 'No se ha podido subir la foto :(',
                    'excepcion' => $th
                ]);
            }
            finally {
                return $this->json([
                    'estado'=>'ok',
                    'mensaje'=>'Se subió la foto exitosamente :)'
                ]);
            }
        }
    }
}
