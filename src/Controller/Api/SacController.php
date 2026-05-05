<?php
// src/Controller/Api/SacController.php
namespace App\Controller\Api;

use App\Entity\Livreur;
use App\Entity\SacProduit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

#[Route('/api/sac', name: 'api_sac')]
class SacController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {}

    #[Route('/ajouter', methods: ['POST'])]
    #[OA\Post(
        description: "Ajouter un produit au sac (nécessite un token JWT)",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'produitId', type: 'integer'),
                    new OA\Property(property: 'quantite', type: 'integer', default: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Produit ajouté au sac"),
            new OA\Response(response: 403, description: "Accès refusé"),
            new OA\Response(response: 404, description: "Produit non trouvé")
        ]
    )]
    public function ajouterProduit(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof Livreur) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $sac = $user->getSac();
        $produit = $this->em->getRepository(\App\Entity\Produit::class)->find($data['produitId']);

        if (!$produit) {
            return $this->json(['error' => 'Produit non trouvé'], 404);
        }

        $sacProduit = $this->em->getRepository(SacProduit::class)->findOneBy([
            'sac' => $sac,
            'produit' => $produit
        ]);

        if ($sacProduit) {
            $sacProduit->setQuantite($sacProduit->getQuantite() + ($data['quantite'] ?? 1));
        } else {
            $sacProduit = new SacProduit();
            $sacProduit->setSac($sac);
            $sacProduit->setProduit($produit);
            $sacProduit->setQuantite($data['quantite'] ?? 1);
        }

        $this->em->persist($sacProduit);
        $this->em->flush();

        return $this->json($sacProduit, 201);
    }

    #[Route('/retirer', methods: ['POST'])]
    #[OA\Post(
        description: "Retirer un produit du sac (nécessite un token JWT)",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'produitId', type: 'integer'),
                    new OA\Property(property: 'quantite', type: 'integer', default: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Produit retiré du sac"),
            new OA\Response(response: 403, description: "Accès refusé"),
            new OA\Response(response: 404, description: "Produit non trouvé ou quantité invalide")
        ]
    )]
    public function retirerProduit(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof Livreur) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $sac = $user->getSac();
        $produit = $this->em->getRepository(\App\Entity\Produit::class)->find($data['produitId']);

        if (!$produit) {
            return $this->json(['error' => 'Produit non trouvé'], 404);
        }

        $sacProduit = $this->em->getRepository(SacProduit::class)->findOneBy([
            'sac' => $sac,
            'produit' => $produit
        ]);

        if (!$sacProduit) {
            return $this->json(['error' => 'Produit non trouvé dans le sac'], 404);
        }

        $quantite = $data['quantite'] ?? 1;
        if ($sacProduit->getQuantite() <= $quantite) {
            $this->em->remove($sacProduit);
        } else {
            $sacProduit->setQuantite($sacProduit->getQuantite() - $quantite);
        }

        $this->em->flush();
        return $this->json(['message' => 'Produit retiré du sac']);
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        description: "Consulter le contenu du sac (nécessite un token JWT)",
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: "Contenu du sac"),
            new OA\Response(response: 403, description: "Accès refusé")
        ]
    )]
    public function consulterSac(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof Livreur) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        $sac = $user->getSac();
        $sacProduits = $this->em->getRepository(SacProduit::class)->findBy(['sac' => $sac]);

        return $this->json($sacProduits);
    }
}