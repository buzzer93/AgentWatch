<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AiAnalysis;
use App\Entity\RssItem;
use App\Repository\RssItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/articles', name: 'app_article_')]
final class ArticleController extends AbstractController
{
    private const int PER_PAGE = 50;

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, RssItemRepository $rssItemRepository): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $items = $rssItemRepository->findPaged($offset, self::PER_PAGE);
        $total = $rssItemRepository->countAll();
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        return $this->render('articles/index.html.twig', [
            'items' => $items,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'perPage' => self::PER_PAGE,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(RssItem $item): Response
    {
        return $this->render('articles/show.html.twig', [
            'item' => $item,
            'analysis' => $item->getAnalysis(),
        ]);
    }

    #[Route('/{id}/scores', name: 'update_scores', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function updateScores(Request $request, RssItem $item, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('update-scores' . $item->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $analysis = $item->getAnalysis();

        if (!$analysis instanceof AiAnalysis) {
            $this->addFlash('error', 'Cet article n a pas encore d analyse IA a corriger.');

            return $this->redirectToRoute('app_article_show', ['id' => $item->getId()]);
        }

        $analysis->applyUserScores(
            (int) $request->request->get('relevanceScore', 0),
            (int) $request->request->get('businessScore', 0),
            (int) $request->request->get('learningScore', 0),
            (int) $request->request->get('contentScore', 0),
            (int) $request->request->get('finalScore', 0),
        );

        $entityManager->flush();

        $this->addFlash('success', 'Tes corrections de scores ont ete enregistrees.');

        return $this->redirectToRoute('app_article_show', ['id' => $item->getId()]);
    }

    #[Route('/{id}/scores/reset', name: 'reset_scores', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function resetScores(Request $request, RssItem $item, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('reset-scores' . $item->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $analysis = $item->getAnalysis();

        if ($analysis instanceof AiAnalysis) {
            $analysis->resetUserScores();
            $entityManager->flush();
            $this->addFlash('success', 'Tes corrections ont ete supprimees, les scores IA sont restaures.');
        }

        return $this->redirectToRoute('app_article_show', ['id' => $item->getId()]);
    }
}
