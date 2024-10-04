<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Service\UtilsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $manager,
        private readonly UtilsService $utilsService,
        private readonly SerializerInterface $serializerInterface,
        private readonly DecoderInterface $decoder,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    //méthode pour afficher tous les utilisateurs
    #[Route('/api/users', name: 'app_api_users', methods: ['GET'])]
    public function showAllUsers(): Response
    {
        $users = $this->userRepository->findAll();
        if (!$users) {
            return $this->json(['message' => 'No users found'], 404);
        }
        return $this->json($users, 200, [], ['groups' => 'user:read']);
    }

    //méthode pour afficher un utilisateur par son id
    #[Route('/api/user/{id}', name: 'app_api_user', methods: ['GET'])]
    public function showUserById(int $id): Response
    {
        $user = $this->userRepository->find($id);
        if ($user) {
            return $this->json(
                $user,
                200,
                ['Access-Control-Allow-Origin' => '*'],
                ['groups' => 'user:read']
            );
        }
        return $this->json(['message' => 'User not found'], 404);
    }

    //méthode pour afficher l'utilisateur connecté
    #[Route('/api/me', name: 'app_api_user_me', methods: ['GET'])]
    public function showMe(): Response
    {
        $user = $this->getUser();
        if ($user) {
            return $this->json(
                $user,
                200,
                ['Access-Control-Allow-Origin' => '*'],
                ['groups' => 'user:read']
            );
        }
        return $this->json(['message' => 'User not found'], 404);
    }

    //méthode pour ajouter un utilisateur
    #[Route('/api/user', name: 'app_api_user', methods: ['POST'])]
    public function addUser(Request $request): Response
    {
        $json = $request->getContent();
        //test si le json est non vide
        if (!$this->utilsService->isEmptyJson($json)) {
            $user = $this->decoder->decode($json, 'json');
            $newUser = new User();
            $newUser
                ->setEmail($user['email'])
                ->setFirstname($user['firstname'])
                ->setLastname($user['lastname'])
                ->setAvatar($user['avatar'] ?? 'default.png')
                ->setRoles(["ROLE_USER"])
                ->setPassword($this->passwordHasher->hashPassword($newUser, $user['password']));
            //test si le compte existe déjà
            if ($this->userRepository->findOneBy(['email' => $newUser->getEmail()])) {
                return $this->json(['error' => 'Email déjà utilisé'], 400);
            }
            $this->manager->persist($newUser);
            $this->manager->flush();
        } 
        //si le json est vide
        else {
            return $this->json(["error" => "Json invalide"], 400);
        }
        return $this->json(
            $newUser,
            200, 
            ['Access-Control-Allow-Origin' => '*'], 
            ['groups' => 'user:read']);
    }
}
