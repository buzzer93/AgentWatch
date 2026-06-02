<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Prompt;
use App\Form\PromptType;
use App\Repository\PromptRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/prompts', name: 'app_prompt_')]
final class PromptController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PromptRepository $promptRepository,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('prompt/index.html.twig', [
            'prompts' => $this->promptRepository->findAllOrderedByKey(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Prompt $prompt): Response
    {
        $form = $this->createForm(PromptType::class, $prompt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Prompt "%s" mis a jour.', $prompt->getName()));

            return $this->redirectToRoute('app_prompt_index');
        }

        return $this->render('prompt/edit.html.twig', [
            'form' => $form,
            'prompt' => $prompt,
        ]);
    }
}
