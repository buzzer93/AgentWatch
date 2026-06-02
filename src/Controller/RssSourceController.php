<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\RssSource;
use App\Form\RssSourceType;
use App\Repository\RssSourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sources', name: 'app_rss_source_')]
final class RssSourceController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RssSourceRepository $rssSourceRepository,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('rss_source/index.html.twig', [
            'sources' => $this->rssSourceRepository->findAllOrderedByPriority(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $source = new RssSource('', '');
        $form = $this->createForm(RssSourceType::class, $source);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($source);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Source "%s" ajoutee.', $source->getName()));

            return $this->redirectToRoute('app_rss_source_index');
        }

        return $this->render('rss_source/new.html.twig', [
            'form' => $form,
            'source' => $source,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, RssSource $source): Response
    {
        $form = $this->createForm(RssSourceType::class, $source);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Source "%s" mise a jour.', $source->getName()));

            return $this->redirectToRoute('app_rss_source_index');
        }

        return $this->render('rss_source/edit.html.twig', [
            'form' => $form,
            'source' => $source,
        ]);
    }

    #[Route('/{id}/toggle', name: 'toggle', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggle(Request $request, RssSource $source): Response
    {
        if (!$this->isCsrfTokenValid('toggle' . $source->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $source->setIsActive(!$source->isActive());
        $this->entityManager->flush();

        $this->addFlash('success', sprintf(
            'Source "%s" %s.',
            $source->getName(),
            $source->isActive() ? 'activee' : 'desactivee',
        ));

        return $this->redirectToRoute('app_rss_source_index');
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, RssSource $source): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $source->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $name = $source->getName();
        $this->entityManager->remove($source);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('Source "%s" supprimee.', $name));

        return $this->redirectToRoute('app_rss_source_index');
    }
}
