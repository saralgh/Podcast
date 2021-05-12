<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Podcast;
use App\Repository\UserRepository;
use App\Repository\PodcastRepository;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends AbstractController
{
    private $user;
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(Request $request): Response
    {
        $subidaCorrecta = $request->get('subidaCorrecta');
        $podcasts = $request->get('podcasts');

        return $this->render('dashboard/index.html.twig', ['subidaCorrecta' => $subidaCorrecta, 'podcasts'=>$podcasts]);
    }

    /**
     * @Route("/nuevoPodcast", name="nuevoPodcast")
     */
    public function nuevoPodcast(Request $request) {
        $entityManager = $this->getDoctrine()->getManager();
        $Podcast = new Podcast;
        $Podcast->setUserId($entityManager->getRepository(User::class)->findOneBy(['email'=>$request->get('username')]));
        
        // Subida del fichero de Audio.
        $audioTmpPath = $_FILES['audio']['tmp_name'];
        $audioName = $_FILES['audio']['name'];
        $audioSize = $_FILES['audio']['size'];
        $audioType = $_FILES['audio']['type'];
        $audioNameCmps = explode(".", $audioName);
        $audioExtension = strtolower(end($audioNameCmps));
        $newAudioName = md5(time() . $audioName) . '.' . $audioExtension;

        // Directorio en el que se va a subir el archivo de audio
        $uploadAudioDir = 'D:/Podcast/audio/';
        $urlAudio = $uploadAudioDir . $audioName;
        $Podcast->setAudio($urlAudio);
        
        if(move_uploaded_file($audioTmpPath, $urlAudio)) { $statusAudio = true; }
        else { $statusAudio = false;}

        // Subida de la imagen
        $imagenTmpPath = $_FILES['imagen']['tmp_name'];
        $imagenName = $_FILES['imagen']['name'];
        $imagenSize = $_FILES['imagen']['size'];
        $imagenType = $_FILES['imagen']['type'];
        $imagenNameCmps = explode(".", $imagenName);
        $imagenExtension = strtolower(end($imagenNameCmps));
        $newImagenName = md5(time() . $imagenName) . '.' . $imagenExtension;

        // Directorio en el que se va a subir el archivo de imagen
        $uploadAudioDir = 'D:/Podcast/imagen/';
        $urlImagen = $uploadAudioDir . $newImagenName;
        $Podcast->setImagen($urlImagen);
        
        if(move_uploaded_file($imagenTmpPath, $urlImagen)){ $statusImagen =true;}
        else { $statusImagen = false; }

        if($statusAudio && $statusImagen) { $mensaje = 'Los ficheros se han subido correctamente.';} 
        else { $mensaje = 'Ha habido un error en la subida de ficheros. Intentelo de nuevo más adelante.'; }

        // Datos para guardar en la base de datos.
        $Podcast->setTittle($request->get('titulo'));
        $Podcast->setDescription($request->get('descripcion'));
        $Podcast->setFechaCreacion(new \DateTime());

        $entityManager->persist($Podcast);
        $entityManager->flush();

        return $this->redirectToRoute('dashboard', ['subidaCorrecta' => $mensaje, 'email'=>$request->get('email')]);
    }

    /**
     * @Route("/misPodcast", name="misPodcast")
     * @return
     * @param Request $request
     */
    public function misPodcast(Request $request) {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $podcastRepository = $entityManager->getRepository(Podcast::class);

        $idUser = ($userRepository->findOneBy(['email'=>$request->get('email')]))->getId();
        $podcastsUser = $podcastRepository->findAll();
        //$podcastsUser = $this->obtenerPodcasts($idUser);

        return $this->redirectToRoute('dashboard', [array('podcasts'=>$podcastsUser)]);
    }

    /*public function obtenerPodcasts($idUser)
    {
        $sql = "SELECT * FROM podcast WHERE user_id_id = $idUser";

    }*/
}