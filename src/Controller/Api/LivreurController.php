<?php
// src/Controller/Api/LivreurController.php
namespace App\Controller\Api;

use App\Entity\Livreur;
use App\Entity\Sac;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[Route('/api/livreurs', name: 'api_livreurs')]
class LivreurController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer
    ) {}

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        description: "Créer un livreur (envoie un email avec le mot de passe)",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nom', type: 'string'),
                    new OA\Property(property: 'email', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Livreur créé"),
            new OA\Response(response: 400, description: "Données invalides")
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $livreur = new Livreur();
        $livreur->setNom($data['nom']);
        $livreur->setEmail($data['email']);
        $livreur->setCreatedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($livreur);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        // Générer un mot de passe aléatoire
        $password = bin2hex(random_bytes(8));
        $livreur->setPassword($this->passwordHasher->hashPassword($livreur, $password));

        $this->em->persist($livreur);
        $this->em->flush();

        // Créer un sac pour le livreur
        $sac = new Sac();
        $sac->setLivreur($livreur);
        $sac->setCreatedAt(new \DateTimeImmutable());
        $this->em->persist($sac);
        $this->em->flush();

        // Envoyer un email avec le mot de passe
        $email = (new \Symfony\Component\Mime\Email())
            ->from('noreply@citylunch.com')
            ->to($livreur->getEmail())
            ->subject('Vos identifiants CityLunch')
            ->text("Bonjour {$livreur->getNom()},\n\nVotre mot de passe est : $password\n\nCordialement,\nL'équipe CityLunch");
        $this->mailer->send($email);

        return $this->json($livreur, 201, [], ['groups' => ['livreur:read']]);
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        description: "Lister tous les livreurs",
        responses: [
            new OA\Response(response: 200, description: "Liste des livreurs")
        ]
    )]
    public function list(): JsonResponse
    {
        $livreurs = $this->em->getRepository(Livreur::class)->findAll();
        return $this->json($livreurs, 200, [], ['groups' => ['livreur:read']]);
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        description: "Récupérer un livreur par ID",
        responses: [
            new OA\Response(response: 200, description: "Livreur trouvé"),
            new OA\Response(response: 404, description: "Livreur non trouvé")
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $livreur = $this->em->getRepository(Livreur::class)->find($id);
        if (!$livreur) {
            return $this->json(['error' => 'Livreur non trouvé'], 404);
        }
        return $this->json($livreur, 200, [], ['groups' => ['livreur:read']]);
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[OA\Put(
        description: "Modifier un livreur",
        responses: [
            new OA\Response(response: 200, description: "Livreur modifié"),
            new OA\Response(response: 404, description: "Livreur non trouvé")
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $livreur = $this->em->getRepository(Livreur::class)->find($id);
        if (!$livreur) {
            return $this->json(['error' => 'Livreur non trouvé'], 404);
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Livreur::class,
            'json',
            ['object_to_populate' => $livreur]
        );

        $errors = $this->validator->validate($livreur);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->flush();
        return $this->json($livreur, 200, [], ['groups' => ['livreur:read']]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        description: "Supprimer un livreur",
        responses: [
            new OA\Response(response: 204, description: "Livreur supprimé"),
            new OA\Response(response: 404, description: "Livreur non trouvé")
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $livreur = $this->em->getRepository(Livreur::class)->find($id);
        if (!$livreur) {
            return $this->json(['error' => 'Livreur non trouvé'], 404);
        }

        $this->em->remove($livreur);
        $this->em->flush();
        return $this->json(null, 204);
    }
}