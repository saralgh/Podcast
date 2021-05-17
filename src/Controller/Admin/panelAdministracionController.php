<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Podcast;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class panelAdministracionController extends AbstractDashboardController
{
    private $session;
    private $user;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    /**
     * @Route("/admin/dashboard", name="admin")
     */
    public function index(): Response
    {
        $this->user = $this->session->get('_security.last_username');

        return $this->render('admin/index.html.twig', ['user' => $this->user]);
        //return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Dark Podcast');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::linkToCrud('User', 'fa fa-user', User::class);
        yield MenuItem::linkToCrud('Podcast', 'fa fa-podcast', Podcast::class);
    }

    /**
     * @Route("/logoutAdmin", name="logoutAdmin")
     */
    public function logoutAdmin() 
    {
        return $this->redirectToRoute('adminLogin');
    }
}
