<?php

namespace App\Controller\Webapp;

use App\Entity\Webapp\Page;
use App\Entity\Webapp\Section;
use App\Form\Webapp\SectionType;
use App\Form\Webapp\Section2Type;
use App\Repository\Webapp\SectionRepository;
use CKSource\CKFinder\Response\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Element;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class SectionController extends AbstractController
{
    /**
     * @Route("/webapp/section/", name="op_webapp_section_index", methods={"GET"})
     */
    public function index(SectionRepository $sectionRepository): Response
    {
        $sections = $this->getDoctrine()->getRepository(Section::class)->findWithPage();

        return $this->render('webapp/section/index.html.twig', [
            'sections' => $sections,
        ]);
    }

    /**
     * @Route("/webapp/section/bypage/{page}", name="op_webapp_section_bypage", methods={"GET"})
     */
    public function byPage(SectionRepository $sectionRepository, $page): Response
    {
        $element = $this->getDoctrine()->getRepository(Page::class)->find($page);

        return $this->render('webapp/section/bypage.html.twig', [
            'sections' => $sectionRepository->findbypage($page),
            'element' => $element,
        ]);
    }

    /**
     * @Route("/webapp/section/frontbypage/{id}", name="op_webapp_section_frontbypage", methods={"GET"})
     */
    public function frontByPage(Page $page): Response
    {
        $sections = $this->getDoctrine()->getRepository(Section::class)->findBy(array('page' => $page), array('position' => 'ASC'));

        return $this->render('webapp/section/frontbypage.html.twig', [
            'sections' => $sections,
        ]);
    }

    /**
     * Liste les sections de la page d'accueil pour les r??organis??es.
     * @Route("/webapp/section/homepagefavourites", name="op_webapp_section_homepagefavourites", methods={"GET"})
     */
    public function HomepageFavourites()
    {
        $sections = $this->getDoctrine()->getRepository(Section::class)->findBy(array('favorites' => 1), array('positionfavorite' => 'ASC'));

        return $this->render('webapp/page/homepagefavourites.html.twig', [
            'sections' => $sections
        ]);
    }

    /**
     * @Route("/webapp/section/new", name="op_webapp_section_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $section = new Section();
        $form = $this->createForm(SectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($section);
            $entityManager->flush();

            return $this->redirectToRoute('op_webapp_section_index');
        }

        return $this->render('webapp/section/new.html.twig', [
            'section' => $section,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/webapp/section/newbypage/{page}", name="op_webapp_section_newbypage", methods={"GET","POST"})
     */
    public function newbypage(Request $request, $page): Response
    {

        $section = new Section();

        $form = $this->createForm(Section2Type::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($section);
            $entityManager->flush();

            return $this->redirectToRoute('op_webapp_section_index');
        }

        return $this->render('webapp/section/new2.html.twig', [
            'section' => $section,
            'page'=> $page,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/webapp/section/addsection/{page}/{row}", name="op_webapp_section_add", methods={"GET","POST"})
     */
    public function addSection( $page, $row, EntityManagerInterface $em) : Response
    {
        $element = $this->getDoctrine()->getRepository(Page::class)->find($page);
        $position = $row +1;

        $section = new Section;
        $section->setTitle('Nouvelle section');
        $section->setContentType('none');
        $section->setPage($element);
        $section->setPosition($position);
        $em->persist($section);
        $em->flush();

        $sections = $this->getDoctrine()->getRepository(Section::class)->findbypage($page);

        return $this->json([
            'code'          => 200,
            'message'       => 'LA section ?? bien ??t?? ajout??e.',
            'liste'         =>  $this->renderView('webapp/section/include/_liste.html.twig', [
                'sections' => $sections
            ])
        ], 200);
    }

    /**
     * @Route("/webapp/section/{id}", name="op_webapp_section_show", methods={"GET"})
     */
    public function show(Section $section): Response
    {
        return $this->render('webapp/section/show.html.twig', [
            'section' => $section,
        ]);
    }

    /**
     * @Route("/webapp/section/{id}/edit", name="op_webapp_section_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Section $section): Response
    {
        $page = $section->getPage();
        $idpage = $page->getId();
        //dd($idpage);
        $form = $this->createForm(SectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('op_webapp_section_edit', [
                'id' => $section->getId()
            ]);
        }

        return $this->render('webapp/section/edit.html.twig', [
            'section' => $section,
            'idpage'=> $idpage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/webapp/section/{id}", name="op_webapp_section_delete", methods={"POST"})
     */
    public function delete(Request $request, Section $section): Response
    {
        if ($this->isCsrfTokenValid('delete'.$section->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($section);
            $entityManager->flush();
        }

        return $this->redirectToRoute('op_webapp_section_index');
    }

    /**
     * Permet d'activer ou de d??sactiver la mise en vedette d'une section sur la page d'accueil
     * @Route("/webapp/section/jsstar/{id}/json", name="op_webapp_section_star")
     */
    public function jsstar(Section $section, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        $isstar = $section->getFavorites();
        // renvoie une erreur car l'utilisateur n'est pas connect??
        if(!$user) return $this->json([
            'code' => 403,
            'message'=> "Vous n'??tes pas connect??"
        ], 403);
        // Si la page est d??ja publi??e, alors on d??publie
        if($isstar == true){
            $section->setfavorites(0);
            $em->flush();
            return $this->json([
                'code'      => 200,
                'message'   => "La section n'est plus publi??e sur la page d'acccueil."
            ], 200);
        }
        // Si la page est d??ja d??publi??e, alors on publie
        $section->setFavorites(1);
        $em->flush();
        return $this->json([
            'code'          => 200,
            'message'       => "La section est publi??e sur la page d'acccueil."
        ], 200);
    }

    /**
     * Supprimer la section
     * @Route("/webapp/section/jsdel/{id}", name="op_webapp_section_del")
     */
    public function jsdel(Section $section, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        if(!$user) return $this->json([
            'code' => 403,
            'message'=> "Vous n'??tes pas connect??"
        ], 403);
        // code de suppression
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($section);
        $entityManager->flush();
        $em->flush();
        return $this->json([
            'code'          => 200,
            'message'       => "La section est correctement supprim??e."
        ], 200);
    }

    /**
     * @Route("/webapp/section/del/{id}", name="op_webapp_section_del", methods={"POST"})
     */
    public function DelEvent(Request $request, Section $section, EntityManagerInterface $em) : Response
    {
        // creation des ??l??ement ncessaire ?? la m??thode
        $user = $this->getUser();
        $page = $section->getPage();

        // Suppression de l'entit??
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($section);
        $entityManager->flush();

        // R??cup??ration de la liste des sectiosn
        $element = $this->getDoctrine()->getRepository(Page::class)->find($page);
        $sections = $em->getRepository(Section::class)->findbypage($page);
        return $this->json([
            'code'=> 200,
            'message' => "L'??v??nenemt a ??t?? supprim??",
            'liste' => $this->renderView('webapp/section/include/_liste.html.twig', [
                'sections' => $sections,
            ])
        ], 200);
    }

    /**
     * Permet de d??placer une section dans la liste
     * @Route("/webapp/section/position/{id}/{level}", name="op_webapp_section_position_down")
     */
    public function Position(Section $section, $level, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        $id = $section->getId();
        $page = $section->getPage();

        // On r??cup??re la position de la page actuelle et on pr??pare des les futures positions +1 et -1.
        $position = $section->getPosition();
        $nextPos = $section->getPosition()+1;
        $previousPos = $section->getPosition()-1;

        if($level == 'up')
        {
            $previousItem = $em->getRepository(Section::class)->findOneBy(array('position' => $previousPos));
            $section->setPosition($previousPos);
            $previousItem->setPosition($position);
            $em->flush();

            // on r??cup??re la liste des pages ordonn??e par position
            $sections = $this->getDoctrine()->getRepository(Section::class)->findbypage($page);

            // on retourne au format JSON
            return $this->json([
                'code'=> 200,
                'message' => "La page est mont??e d'un niveau",
                'liste' => $this->renderView('webapp/section/include/_liste.html.twig', [
                    'sections' => $sections
                ])
            ], 200);
        }elseif($level == 'down'){
            $nextItem = $em->getRepository(Section::class)->findOneBy(array('position' => $nextPos));
            $section->setPosition($nextPos);
            $nextItem->setPosition($position);
            $em->flush();
            // on r??cup??re la liste des pages ordonn??e par position
            $sections = $this->getDoctrine()->getRepository(Section::class)->findbypage($page);
            return $this->json([
                'code'=> 200,
                'message' => "La page est descendu d'un niveau",
                'liste' => $this->renderView('webapp/section/include/_liste.html.twig', [
                    'sections' => $sections
                ])
            ], 200);
        }else{
            return $this->json([
                'code'=> 200,
                'message' => "Une erreur a ??t?? d??tect??",
            ], 200);
        }
    }

    /**
     * Permet de d??placer une section dans la liste
     * @Route("/webapp/section/favorite/position/{id}/{level}", name="op_webapp_section_favorite_position_down")
     */
    public function PositionFavorite(Section $section, $level, EntityManagerInterface $em) : Response
    {
        $user = $this->getUser();
        $id = $section->getId();
        $page = $section->getPage();

        // On r??cup??re la position de la page actuelle et on pr??pare des les futures positions +1 et -1.
        $position = $section->getPositionfavorite();
        $nextPos = $section->getPositionfavorite()+1;
        $previousPos = $section->getPositionfavorite()-1;

        //dd($position, $nextPos, $previousPos);

        if($level == 'up')
        {
            $previousItem = $em->getRepository(Section::class)->findOneBy(array('positionfavorite' => $previousPos));
            //dd($section);
            $section->setPositionfavorite($previousPos);
            $previousItem->setPositionfavorite($position);
            $em->flush();

            // on r??cup??re la liste des pages ordonn??e par position
            $sections = $this->getDoctrine()->getRepository(Section::class)->findBy(array('favorites' => 1), array('positionfavorite' => 'ASC'));

            // on retourne au format JSON
            return $this->json([
                'code'=> 200,
                'message' => "La page est mont??e d'un niveau",
                'liste' => $this->renderView('webapp/section/include/_liste.html.twig', [
                    'sections' => $sections
                ])
            ], 200);
        }elseif($level == 'down'){
            $nextItem = $em->getRepository(Section::class)->findOneBy(array('positionfavorite' => $nextPos));
            $section->setPositionfavorite($nextPos);
            $nextItem->setPositionfavorite($position);
            $em->flush();
            // on r??cup??re la liste des pages ordonn??e par position
            $sections = $this->getDoctrine()->getRepository(Section::class)->findBy(array('favorites' => 1), array('positionfavorite' => 'ASC'));
            return $this->json([
                'code'=> 200,
                'message' => "La page est descendu d'un niveau",
                'liste' => $this->renderView('webapp/section/include/_liste.html.twig', [
                    'sections' => $sections
                ])
            ], 200);
        }else{
            return $this->json([
                'code'=> 200,
                'message' => "Une erreur a ??t?? d??tect??",
            ], 200);
        }
    }
}
