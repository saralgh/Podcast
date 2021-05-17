<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Podcast;
use App\Form\PodcastType;
use App\Repository\PodcastRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/misPodcasts")
 */
class CrudController extends AbstractController
{
    private $userId;
    private $userEmail;

    /**
     * @Route("/", name="crud_index", methods={"GET"})
     */
    public function index(PodcastRepository $podcastRepository, Request $request): Response
    {
        $podcasts = $request->get('podcasts');
        $this->userId = $request->get('userId');

        return $this->render('crud/index.html.twig', ['podcasts' => $request->get('podcasts')]);
    }

    /**
     * @Route("/new", name="crud_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $podcast = new Podcast();
        $form = $this->createForm(PodcastType::class, $podcast);
        $form->handleRequest($request);
        $this->userId = intval($request->query->get('id'));
        $this->userEmail = $request->query->get('userEmail');

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            /*$fileAudio = $podcast->getAudio();
            $fileAudioName = $this->generateUniqueFileName().'.'.$file->getExtension();*/

            $fileAudio = $form->get('Audio')->getData();
            $originalFileAudioName = pathinfo($fileAudio->getClientOriginalName(), PATHINFO_FILENAME);
            $newFileAudioName = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFileAudioName);
            $fileAudioName = $newFileAudioName.'-'.uniqid().'.'.$fileAudio->guessExtension();

            $fileImagen = $form->get('Imagen')->getData();
            $originalFileImagenName = pathinfo($fileImagen->getClientOriginalName(), PATHINFO_FILENAME);
            $newFileImagenName =  transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFileImagenName);
            $fileImagenName = $newFileImagenName.'-'.uniqid().'.'.$fileImagen->guessExtension();

            try{
                $fileAudio->move($this->getParameter('uploadsAudio'), $fileAudioName);
                $fileImagen->move($this->getParameter('uploadsImagen'), $fileImagenName);
            } catch (FileException $e) {
                // TODO: IMPLEMENTAR ERROR
            }
            /*$extAudio = $fileAudio->getExtension();
            $fileAudioName = time().".".$extAudio;
            $fileAudio->move("uploads", $fileAudioName);

            $fileImagen = $form['Imagen']->getData();
            $extImagen = $fileImagen->getExtension();
            $fileImagenName = time().".".$extImagen;
            $fileImagen->move('uploads',$fileImagenName);*/

            $podcast->setTittle($form['tittle']->getData());
            $podcast->setDescription($form['description']->getData());
            $podcast->setAudio($fileAudioName);
            $podcast->setImagen($fileImagenName);
            $podcast->setFechaCreacion(new \DateTime());
            $podcast->setUserId($entityManager->getRepository(User::class)->findOneBy(['id'=>$this->userId]));
            
            $entityManager->persist($podcast);
            $entityManager->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('crud/new.html.twig', [
            'podcast' => $podcast,
            'form' => $form->createView(),
            'userEmail' => $this->userEmail
        ]);
    }

    /**
     * @Route("/{id}", name="crud_show", methods={"GET"})
     */
    public function show(Podcast $podcast, Request $request): Response
    {

        $this->userEmail = $request->query->get('userEmail');

        return $this->render('crud/show.html.twig', [
            'podcast' => $podcast,
            'userEmail' => $this->userEmail
        ]);
    }

    /**
     * @Route("/{id}/edit", name="crud_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Podcast $podcast): Response
    {
        $form = $this->createForm(PodcastType::class, $podcast);
        $form->handleRequest($request);

        $this->userEmail = $request->query->get('userEmail');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('crud/edit.html.twig', [
            'podcast' => $podcast,
            'form' => $form->createView(),
            'userEmail' => $this->userEmail
        ]);
    }

    /**
     * @Route("/{id}", name="crud_delete", methods={"POST"})
     */
    public function delete(Request $request, Podcast $podcast): Response
    {
        if ($this->isCsrfTokenValid('delete'.$podcast->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($podcast);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dashboard');
    }
}
