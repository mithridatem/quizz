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

        if (!$this->utilsService->isEmptyJson($json) && array_key_exists("title", $this->decoder->decode($json, 'json'))) {
            $category = $this->serializer->deserialize($json, Category::class, 'json');
            $this->entityManager->persist($category);
            $this->entityManager->flush();
            $code = 200;
        } else {
            $category = ["error" => "Json invalide"];
            $code = 400;
        }
        return $this->json($category, $code);
    }
}
