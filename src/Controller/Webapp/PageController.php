<?php

namespace App\Controller\Webapp;

use App\Entity\Webapp\Page;
use App\Form\Webapp\PageType;
use App\Repository\Webapp\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    /**
     * @Route("/webapp/page/", name="op_webapp_page_index", methods={"GET"})
     */
    public function index(PageRepository $pageRepository): Response
    {
        return $this->render('webapp/page/index.html.twig', [
            'pages' => $pageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/webapp/page/new", name="op_webapp_page_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $member = $this->getUser();
        $page = new Page();
        $page->setAuthor($member);
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($page);
            $entityManager->flush();

            return $this->redirectToRoute('op_webapp_page_index');
        }

        return $this->render('webapp/page/new.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/webapp/page/{id}", name="op_webapp_page_show", methods={"GET"})
     */
    public function show(Page $page): Response
    {
        return $this->render('webapp/page/show.html.twig', [
            'page' => $page,
        ]);
    }

    /**
     * @Route("/webapp/page/{id}/edit", name="op_webapp_page_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Page $page): Response
    {
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('op_webapp_page_index');
        }

        return $this->render('webapp/page/edit.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/webapp/page/{id}", name="op_webapp_page_delete", methods={"POST"})
     */
    public function delete(Request $request, Page $page): Response
    {
        if ($this->isCsrfTokenValid('delete'.$page->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($page);
            $entityManager->flush();
        }

        return $this->redirectToRoute('webapp_page_index');
    }

    /**
     * @param PageRepository $pageRepository
     * @param $slug
     * @return Response
     * @Route("/page/{slug}", name="")
     */
    public function pagebyslug(PageRepository $pageRepository, $slug): Response
    {
        $page = $pageRepository->findOneBy(array('slug' => $slug));

        return $this->render('webapp/page/page.html.twig', [
            'page' => $page,
        ]);
    }
}
