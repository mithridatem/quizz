<?php

namespace App\Controller;

use App\Entity\Answer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Question;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use App\Service\UtilsService;

class QuestionController extends AbstractController
{
    public function __construct(
        private readonly QuestionRepository $questionRepository,
        private readonly AnswerRepository $answerRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializerInterface,
        private readonly DecoderInterface $decoder,
        private readonly UtilsService $utilsService
    ) {}

    #[Route('/api/question', name: 'app_api_question', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $json = $request->getContent();
        $code = 200;

        if (
            !$this->utilsService->isEmptyJson($json)
            && array_key_exists(
                "answers",
                $this->decoder->decode($json, 'json')
            )
        ) {
            $question = $this->decoder->decode($json, 'json');
            $newQuestion = new Question();
            $newQuestion
                ->setTitle($question['title'])
                ->setDescription($question['description'])
                ->setValue($question['value']);
            for ($i = 0; $i < count($question['answers']); $i++) {
                //$newAnswer = $this->answerRepository->find($question['answers'][$i]['id']);
                $newAnswer = new Answer();
                $newAnswer
                    ->setText($question['answers'][$i]['text'])
                    ->setValid($question['answers'][$i]['valid']);
                $newQuestion->addAnswer($newAnswer);
            }
            $this->entityManager->persist($newQuestion);
            dump($newQuestion);
            $this->entityManager->flush();
            dump($newQuestion);
        } else {
            $newQuestion = ["error" => "Json invalide"];
            $code = 400;
        }
        return $this->json($newQuestion, $code);
    }
}
