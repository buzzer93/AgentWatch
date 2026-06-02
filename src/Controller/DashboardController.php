<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\RssItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function __invoke(RssItemRepository $rssItemRepository): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'services' => [
                ['name' => 'PostgreSQL', 'port' => '5432', 'status' => 'Pret a demarrer'],
                ['name' => 'n8n', 'port' => '5678', 'status' => 'Pret a demarrer'],
                ['name' => 'Ollama', 'port' => '11434', 'status' => 'Pret a demarrer'],
                ['name' => 'Mailpit', 'port' => '8025', 'status' => 'Pret a demarrer'],
            ],
            'analyzedItems' => $rssItemRepository->findRecentAnalyzed(10),
        ]);
    }
}
