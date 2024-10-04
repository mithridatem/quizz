<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Quizz;
use App\Repository\QuizzRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UtilsService;

class QuizzController extends AbstractController
{
    public function __construct(
        private readonly QuizzRepository $quizzRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializerInterface,
        private readonly DecoderInterface $decoder,
        private readonly UtilsService $utilsService
    ) {}
    #[Route('/api/quizz', name: 'app_quizz', methods: 'POST')]
    public function add(Request $request): Response
    {
        $json = $request->getContent();

        if (!$this->utilsService->isEmptyJson($json) && array_key_exists("title", json_decode($json, true))) {
            $quizz = $this->decoder->decode($json,  'json');
            $newquizz = new Quizz();
            $newquizz
                ->setTitle($quizz['title'])
                ->setDescription($quizz['title']);
            //boucle ajout des categories
            for ($i = 0; $i < count($quizz['categories']); $i++) {
                if ($this->categoryRepository->find($quizz['categories'][$i]["id"]) != null) {
                    $newquizz->addCategory($this->categoryRepository->find($quizz['categories'][$i]["id"]));
                }
                return $this->json(["error" => "Category not found"]);
            }

            $this->entityManager->persist($newquizz);
            $this->entityManager->flush();
        } else {
            $newquizz = ["error" => "Json invalide"];
        }
        return $this->json($newquizz);
    }
}
