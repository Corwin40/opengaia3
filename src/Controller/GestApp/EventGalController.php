<?php

namespace App\Controller\GestApp;

use App\Entity\GestApp\Event;
use App\Entity\GestApp\EventGal;
use App\Form\GestApp\EventGal2Type;
use App\Form\GestApp\EventGalType;
use App\Repository\GestApp\EventGalRepository;
use App\Repository\GestApp\EventRepository;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Uuid;

/**
 * @Route("")
 */
class EventGalController extends AbstractController
{
    /**
     * @Route("/gest/app/eventgal/", name="op_gestapp_eventgal_index", methods={"GET"})
     */
    public function index(EventGalRepository $eventGalRepository): Response
    {

        return $this->render('gest_app/event_gal/index.html.twig', [
            'event_gals' => $eventGalRepository->findAll(),
        ]);
    }

    /**
     * @Route("/gest/app/eventgalpublish/{eventName}", name="op_gestapp_eventgal_eventgalpublish", methods={"GET"})
     */
    public function EventGalPublish($eventName, PaginatorInterface $paginator, Request $request, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->findBy(['name' => $eventName]);
        $data = $this->getDoctrine()->getRepository(EventGal::class)->EventGalPublish($eventName);
        $eventGal = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            20
        );
        //dd($data);
        return $this->render('gest_app/event_gal/EventGalPublish.html.twig', [
            'event_gals' => $eventGal,
            'event' => $event
        ]);
    }

    /**
     * @Route("/gest/app/eventgal/{id}/{orientation}", name="op_gestapp_eventgal_orientation", methods={"POST"})
     */
    public function EventGalOrientation(EventGal $eventGal, $orientation): Response
    {
        $eventGal->setOrientation($orientation);
        $this->getDoctrine()->getManager()->flush();

        return $this->json([
            'code'=> 200,
            'message' => "L'orientation a été validée",
        ], 200);
    }

    /**
     * @Route("/gest/app/eventgal/new", name="op_gestapp_eventgal_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $eventGal = new EventGal();
        $form = $this->createForm(EventGalType::class, $eventGal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($eventGal);
            $entityManager->flush();

            return $this->redirectToRoute('op_gestapp_eventgal_new');
        }

        return $this->render('gest_app/event_gal/new.html.twig', [
            'event_gal' => $eventGal,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/gest/app/eventgal/{id}", name="op_gestapp_eventgal_show", methods={"GET"})
     */
    public function show(EventGal $eventGal): Response
    {
        return $this->render('gest_app/event_gal/show.html.twig', [
            'event_gal' => $eventGal,
        ]);
    }

    /**
     * @Route("/gest/app/eventgal/{id}/edit", name="op_gestapp_eventgal_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, EventGal $eventGal): Response
    {
        $form = $this->createForm(EventGalType::class, $eventGal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gest_app_event_gal_index');
        }

        return $this->render('gest_app/event_gal/edit.html.twig', [
            'event_gal' => $eventGal,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/gest/app/eventgal/{id}", name="op_gestapp_eventgal_delete", methods={"POST"})
     */
    public function delete(Request $request, EventGal $eventGal): Response
    {
        if ($this->isCsrfTokenValid('delete'.$eventGal->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($eventGal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('gest_app_event_gal_index');
    }

    /**
     * Affiche le contenu de la galerie dans l'admin selon un event.
     * @Route("/opadmin/gestapp/showGalByEvent/{event}", name="op_admin_eventgal_showgalbyevent", methods={"GET"})
     */
    public function showGalByEvent(EntityManagerInterface $em, $event)
    {
        $galeries = $em->getRepository(EventGal::class)->findBy(array('event'=>$event));

        return $this->render('gest_app/event_gal/showgalbyevent.html.twig',[
            'galeries' => $galeries
        ]);
    }

    /**
     * @Route("/opadmin/gestapp/addbyevent/{idevent}", name="op_gestapp_eventgal_addbyevent", methods={"GET","POST"})
     */
    public function AddImageByEvent($idevent, Request $request, EntityManagerInterface $em)
    {
        $event = $em->getRepository(Event::class)->find($idevent);
        $uuid = Uuid::V1_MAC;
        //dd($event);
        $eventGal = new EventGal();
        $form = $this->createForm(EventGal2Type::class, $eventGal, [
            'action' => $this->generateUrl('op_gestapp_eventgal_addbyevent', ['idevent'=>$idevent]),
            'method' => 'POST'
        ]);
        $form->handleRequest($request);

        $img = $form->get('imageFile');
        // dd($img);
        if($img){
            if ($form->isSubmitted() && $form->isValid()) {
                $eventGal->setName($event->getName().$uuid);
                $eventGal->setEvent($event);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($eventGal);
                $entityManager->flush();

                return $this->redirectToRoute('op_gestapp_event_edit', ['id'=> $idevent]);
            }
        }

        return $this->render('gest_app/event_gal/addbyevent.html.twig', [
            'event_gal' => $eventGal,
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/opadmin/gestapp/supprimg/{id}", name="op_gestapp_eventgal_supprimg", methods={"GET","POST"})
     */
    public function SupprImageByEvent(EventGal $eventGal, Request $request, EntityManagerInterface $em)
    {
        $idEvent = $eventGal->getEvent()->getId();
        if(!$eventGal){
            throw new Exception("Erreur : pas d'image enregistré");
        }
        $em->remove($eventGal);
        $em->flush();

        $eventGals = $em->getRepository(EventGal::class)->findBy(['event'=>$idEvent]);

        return $this->json([
            'code'      => 200,
            'message'   => "Ok",
            'liste' => $this->renderView('gest_app/event_gal/showgalbyevent.html.twig', [
                'galeries' => $eventGals,
            ])
        ], 200);
    }
}
