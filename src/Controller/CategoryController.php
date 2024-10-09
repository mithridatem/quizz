<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\UtilsService;

class CategoryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly DecoderInterface $decoder,
        private readonly CategoryRepository $categoryRepository,
        private readonly UtilsService $utilsService
    ) {}

    #[Route('/api/category', name: 'app_api_category', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $json = $request->getContent();
        //test si le json est vide et si la clé "title" existe
        if (!$this->utilsService->isEmptyJson($json) && array_key_exists('title', $this->decoder->decode($json, 'json'))) {
            $category = $this->serializer->deserialize($json, Category::class, 'json');
            //test si la catégorie existe déjà
            if ($this->categoryRepository->findOneBy(["title" => $category->getTitle()])) {
                $category = ["error" => "Cette catégorie existe déjà"];
                $code = 400;
            }
            //sinon on la persiste 
            else {
                $this->entityManager->persist($category);
                $this->entityManager->flush();
                $code = 200;
            }
        }
        //sinon on renvoie une erreur
        else {
            $category = ["error" => "Json invalide"];
            $code = 400;
        }
        //retour du json
        return $this->json($category, $code);
    }

    #[Route('/api/category/all', name: 'app_api_category_all', methods: ['GET'])]
    public function getAll(): Response
    {
        $categories = $this->categoryRepository->findAll();
        //test si des catégories ont été trouvées
        if ($categories) {
            return $this->json($categories, 200, ['access-control-allow-origin' => '*'], ['groups' => 'category:read']);
        }
        //test sinon on renvoie une erreur
        else {
            return $this->json(["error" => "Aucune catégorie trouvée"], 404);
        }
    }
}
