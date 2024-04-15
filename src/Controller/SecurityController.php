<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SecurityController extends AbstractController
{

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/security/registro', methods:["POST"])]
    public function index(Request $request, UserPasswordHasherInterface $passwordInterface): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email']))
        {
            return $this->json([
                "estado" => "error",
                "mensaje" => "El campo email es obligatorio :)"
            ], 200);
        }
        $existe = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existe)
        {
            return $this->json([
                "estado"=>'error',
                "mensaje"=>"Este correo ya está en uso ;)"
            ], 404);
        }
        $entity = new User();
        $entity->setEmail($data['email']);
        $entity->setPassword($passwordInterface->hashPassword(
            $entity,
            $data['password']
        ));
        $entity->setRoles(['ROLE_USER']);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        return $this->json([
            'estado' => 'ok',
            'mensaje' => 'Bienvenido nuevo usuario <3'
        ], 201);
    }

    #[Route('/security/login', methods:["POST"])]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email'=>$data['email']]);
        if (!$user)
        {
            return $this->json([
                'estado'=> 'error',
                'password'=> 'Las credenciales ingresadas no son válidas.'
            ], 404);
        }
        if ($passwordHasher->isPasswordValid($user, $data["password"]))
        {
            $payload = [
                "iss"=>"http://".dirname($_SERVER['SERVER_NAME']."".$_SERVER['PHP_SELF'])."/",
                'aud'=>$user->getId(),
                'iat'=>time(),
                'exp'=>time()+ (30 * 24 * 60 * 60)
            ];
            $jwt = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS512');
            return $this->json([
                'token'=>$jwt,
                'nombre'=>$user->getEmail()
            ]);
        }
        else {
            return $this->json([
                "estado"=>"error",
                "password"=>"Contraseña inválida o incorrecta!!"
            ], 404);
        }
    }
}
