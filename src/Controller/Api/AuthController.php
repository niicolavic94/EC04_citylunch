<?php
// src/Controller/Api/AuthController.php
namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api', name: 'api_')]
class AuthController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\Post(
        description: "Se connecter et recevoir un token JWT",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'password', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Token JWT"),
            new OA\Response(response: 401, description: "Identifiants invalides")
        ]
    )]
    public function login(): JsonResponse
    {
        // Cette méthode ne sera pas appelée directement.
        // Le bundle LexikJWT gère automatiquement la génération du token.
        return $this->json(['error' => 'Utilisez la route /api/login avec un POST contenant email et password.']);
    }
}