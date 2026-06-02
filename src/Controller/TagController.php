<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/tags', name: 'app_tag_')]
final class TagController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TagRepository $tagRepository,
        private readonly SluggerInterface $slugger,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('tag/index.html.twig', [
            'tags' => $this->tagRepository->findAllOrderedByName(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $tag = new Tag('', '');
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->ensureSlug($tag);
            $this->entityManager->persist($tag);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Tag "%s" cree.', $tag->getName()));

            return $this->redirectToRoute('app_tag_index');
        }

        return $this->render('tag/new.html.twig', [
            'form' => $form,
            'tag' => $tag,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Tag $tag): Response
    {
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->ensureSlug($tag);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Tag "%s" mis a jour.', $tag->getName()));

            return $this->redirectToRoute('app_tag_index');
        }

        return $this->render('tag/edit.html.twig', [
            'form' => $form,
            'tag' => $tag,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Tag $tag): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $tag->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $name = $tag->getName();
        $this->entityManager->remove($tag);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('Tag "%s" supprime.', $name));

        return $this->redirectToRoute('app_tag_index');
    }

    private function ensureSlug(Tag $tag): void
    {
        if ($tag->getSlug() === '') {
            $tag->setSlug(strtolower((string) $this->slugger->slug($tag->getName())));
        }
    }
}
