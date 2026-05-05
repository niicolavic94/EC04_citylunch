<?php
// src/Controller/Api/ProduitController.php
namespace App\Controller\Api;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[Route('/api/produits', name: 'api_produits')]
class ProduitController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        description: "Créer un produit",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nom', type: 'string'),
                    new OA\Property(property: 'prix', type: 'number', format: 'float'),
                    new OA\Property(property: 'description', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Produit créé"),
            new OA\Response(response: 400, description: "Données invalides")
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $produit = $this->serializer->deserialize($request->getContent(), Produit::class, 'json');
        $produit->setCreatedAt(new \DateTimeImmutable());
        $produit->setUpdatedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($produit);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->persist($produit);
        $this->em->flush();

        return $this->json($produit, 201);
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        description: "Lister tous les produits",
        responses: [
            new OA\Response(response: 200, description: "Liste des produits")
        ]
    )]
    public function list(): JsonResponse
    {
        $produits = $this->em->getRepository(Produit::class)->findAll();
        return $this->json($produits);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        description: "Récupérer un produit par ID",
        responses: [
            new OA\Response(response: 200, description: "Produit trouvé"),
            new OA\Response(response: 404, description: "Produit non trouvé")
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $produit = $this->em->getRepository(Produit::class)->find($id);
        if (!$produit) {
            return $this->json(['error' => 'Produit non trouvé'], 404);
        }
        return $this->json($produit);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[OA\Put(
        description: "Modifier un produit",
        responses: [
            new OA\Response(response: 200, description: "Produit modifié"),
            new OA\Response(response: 404, description: "Produit non trouvé")
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $produit = $this->em->getRepository(Produit::class)->find($id);
        if (!$produit) {
            return $this->json(['error' => 'Produit non trouvé'], 404);
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Produit::class,
            'json',
            ['object_to_populate' => $produit]
        );
        $produit->setUpdatedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($produit);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->flush();
        return $this->json($produit);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        description: "Supprimer un produit",
        responses: [
            new OA\Response(response: 204, description: "Produit supprimé"),
            new OA\Response(response: 404, description: "Produit non trouvé")
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $produit = $this->em->getRepository(Produit::class)->find($id);
        if (!$produit) {
            return $this->json(['error' => 'Produit non trouvé'], 404);
        }

        $this->em->remove($produit);
        $this->em->flush();
        return $this->json(null, 204);
    }
}