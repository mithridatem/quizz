<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Quizz;
use App\Repository\QuizzRepository;
use App\Repository\QuestionRepository;
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
        private readonly QuestionRepository $questionRepository,
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
        //test si le json est valide
        if (
            !$this->utilsService->isEmptyJson($json) &&
            array_key_exists("title", json_decode($json, true))
        ) {
            $quizz = $this->decoder->decode($json,  'json');
            $newquizz = new Quizz();
            $newquizz
                ->setTitle($quizz['title'] ?? "vide")
                ->setDescription($quizz['title'] ?? "vide");
            //boucle ajout des categories
            for ($i = 0; $i < count($quizz['categories']); $i++) {
                
                if ($this->categoryRepository->find($quizz['categories'][$i]["id"])) {
                    $newquizz->addCategory($this->categoryRepository->find($quizz['categories'][$i]["id"]));
                }else{
                    return $this->json(["error" => "Category not found"], 400,  ['Access-Control-Allow-Origin' => '*']);
                }
            }
            //parcours des questions
            for ($i = 0; $i <count($quizz['questions']); $i++) {
                $question = $this->questionRepository->find($quizz['questions'][$i]["id"]);
                if ($question) {
                    $newquizz->addQuestion($question);
                } else {
                    return $this->json(["error" => "Question not found"], 400,  ['Access-Control-Allow-Origin' => '*']);
                }
            }
            $this->entityManager->persist($newquizz);
            $this->entityManager->flush();
            $code = 200;
        } else {
            $newquizz = ["error" => "Json invalide"];
            $code = 400;
        }
        return $this->json($newquizz, $code, ['Access-Control-Allow-Origin' => '*'], ['groups' => 'quizz:read']);
    }

    #[Route('/api/quizz/{id}', name: 'app_quizz_id', methods: 'GET')]
    public function get(int $id): Response
    {
        $quizz = $this->quizzRepository->find($id);
        if ($quizz === null) {
            return $this->json(
                ["error" => "Quizz not found"],
                404,
                ['Access-Control-Allow-Origin' => '*'],
                []
            );
        }
        return $this->json(
            $quizz,
            200,
            ['Access-Control-Allow-Origin' => '*'],
            ['groups' => 'quizz:read']
        );
    }

    #[Route('/api/quizz', name: 'app_quizz_all', methods: 'GET')]
    public function getAll(): Response
    {
        $quizz = $this->quizzRepository->findAll();
        if($quizz === null){
            return $this->json(
                ["error" => "Quizz not found"],
                404,
                ['Access-Control-Allow-Origin' => '*'],
                []
            );
        }
        return $this->json(
            $quizz,
            200,
            ['Access-Control-Allow-Origin' => '*'],
            ['groups' => 'quizz:read']
        );
    }
}
