<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_access_denied');
        } 
        return $this->render('admin/admin_dashboard.html.twig');
    }

    #[Route('/admin/access-denied', name: 'admin_access_denied')]
    public function accessDenied(): Response
    {
        return $this->render('admin/access_denied.html.twig');	
    }


    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Api Soluval');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
