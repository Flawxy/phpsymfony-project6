<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    /**
     * Displays the creating form for a trick
     *
     * @Route("tricks/new", name="tricks_create")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager)
    {
        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            foreach ($trick->getMedias() as $media) {
                $media->setTrick($trick);
                $manager->persist($media);
            }

            $trick->setCreator($this->getUser());

            $manager->persist($trick);
            $manager->flush();

            $this->addFlash(
                'success',
                "La figure <strong>{$trick->getName()}</strong> a bien été enregistrée !"
            );

            return $this->redirectToRoute('tricks_index');
        }

        return $this->render('trick/new.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * Displays all the tricks
     *
     * @Route("/tricks", name="tricks_index")
     * @param TrickRepository $repo
     * @return Response
     */
    public function index(TrickRepository $repo)
    {
        return $this->render('trick/index.html.twig', [
            'tricks' => $repo->findAll()
        ]);
    }

    /**
     * Displays one particular trick
     *
     * @Route("/tricks/{slug}", name="tricks_show")
     * @param Trick $trick
     * @return Response
     */
    public function show(Trick $trick)
    {
        return $this->render('trick/show.html.twig', [
            'trick' => $trick
        ]);
    }

    /**
     * Displays the editing form of a trick
     *
     * @Route("/tricks/{slug}/edit", name="tricks_edit")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param Trick $trick
     * @param EntityManagerInterface $manager
     * @return Response
     * @throws Exception
     */
    public function edit(Request $request, Trick $trick, EntityManagerInterface $manager)
    {
        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            foreach ($trick->getMedias() as $media) {
                $media->setTrick($trick);
                $manager->persist($media);
            }
            $trick->updateDate();
            $manager->persist($trick);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les modifications de la figure <strong>{$trick->getName()}</strong> ont bien été enregistrées !"
            );

            return $this->redirectToRoute('tricks_show', [
                'slug' => $trick->getSlug()
            ]);
        }

        return $this->render('trick/edit.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick
        ]);
    }

    /**
     * Allows to delete a trick
     * @Route("/tricks/{slug}/delete", name="tricks_delete")
     * @IsGranted("ROLE_USER")
     * @param Trick $trick
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function delete(Trick $trick, EntityManagerInterface $manager)
    {
        $manager->remove($trick);
        $manager->flush();

        $this->addFlash(
            'success',
            "L'annonce <strong>{$trick->getName()}</strong> a bien été supprimée !"
        );

        return $this->redirectToRoute('tricks_index');
    }
}
