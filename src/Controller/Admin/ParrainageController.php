<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Parrainage;
use App\Form\Admin\ParrainageType;
use App\Repository\Admin\ParrainageRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;


class ParrainageController extends AbstractController
{
    /**
     * @Route("/admin/parrainage", name="op_admin_parrainage_index", methods={"GET"})
     */
    public function index(ParrainageRepository $parrainageRepository): Response
    {
        return $this->render('admin/parrainage/index.html.twig', [
            'parrainages' => $parrainageRepository->findAll(),
        ]);
    }
    /**
     * @Route("/admin/parrainage/add", name="op_admin_parrainage_add", methods={"POST"})
     */
    public function add(Request $request, MailerInterface $mailer)
    {
        // récupération des données
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        // Hydratation de l'objet et persistance en BDD
        $parrainage = new Parrainage();
        $parrainage->setAuthor($user);
        $parrainage->setParrainageFirstname($data['parrainageFirstname']);
        $parrainage->setParrainageLastname($data['parrainageLastname']);
        $parrainage->setParrainageEmail($data['parrainageEmail']);
        $parrainage->setPhoneDesk($data['phoneDesk']);
        $parrainage->setPhoneGsm($data['phoneGsm']);
        $parrainage->setNameSociety($data['nameSociety']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($parrainage);
        $entityManager->flush();

        // Envoyer un email au Just
        $email = (new TemplatedEmail())
            ->from('postmaster@openpixl.fr')
            ->to('xavier.burke@openpixl.fr')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('JUSTàFaire - Nouvelle Invitation pour une adhésion')
            //->text('Sending emails is fun again!')
            ->htmlTemplate('email/newInvitationWebMaster.html.twig')
            ->context([
                'author' => $parrainage->getAuthor(),
                'nameSociety'=> $parrainage->getNameSociety(),
                'firstName'=> $parrainage->getParrainageFirstname(),
                'lastName' => $parrainage->getParrainageLastname(),
                'phoneGSM' => $parrainage->getPhoneGsm(),
                'phoneDesk' => $parrainage->getPhoneDesk(),
                'parrainageEmail' => $parrainage->getParrainageEmail()
            ]);
        $mailer->send($email);

        return $this->json([
            'code'=> 200,
            'message' => "Votre invité a été inscrit, l'équipe du JUST va la contacter ,pour sa participation au prochain RDV."
        ], 200);


    }

    /**
     * @Route("/new", name="admin_parrainage_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $parrainage = new Parrainage();
        $form = $this->createForm(ParrainageType::class, $parrainage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($parrainage);
            $entityManager->flush();

            return $this->redirectToRoute('admin_parrainage_index');
        }

        return $this->render('admin/parrainage/new.html.twig', [
            'parrainage' => $parrainage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_parrainage_show", methods={"GET"})
     */
    public function show(Parrainage $parrainage): Response
    {
        return $this->render('admin/parrainage/show.html.twig', [
            'parrainage' => $parrainage,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_parrainage_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Parrainage $parrainage): Response
    {
        $form = $this->createForm(ParrainageType::class, $parrainage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_parrainage_index');
        }

        return $this->render('admin/parrainage/edit.html.twig', [
            'parrainage' => $parrainage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_parrainage_delete", methods={"POST"})
     */
    public function delete(Request $request, Parrainage $parrainage): Response
    {
        if ($this->isCsrfTokenValid('delete'.$parrainage->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($parrainage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_parrainage_index');
    }
}