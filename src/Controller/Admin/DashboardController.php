<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\AccountTransaction;
use App\Entity\Admin;
use App\Entity\ApiClient;
use App\Entity\Campaign;
use App\Entity\DeletedAccount;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    private AdminUrlGenerator $urlGenerator;

    public function __construct(AdminUrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $url = $this->urlGenerator->setController(AccountCrudController::class)->generateUrl();
        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle($_ENV['APP_ADMIN_TITLE'] ?? 'Loyalty service');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Campaigns', 'fa fa-percentage', Campaign::class);
        yield MenuItem::linkToCrud('Accounts', 'fa fa-user-tag', Account::class);
        yield MenuItem::linkToCrud('Deleted Accounts', 'fa fa-user-slash', DeletedAccount::class);
        yield MenuItem::linkToCrud('Transactions', 'fa fa-file-invoice-dollar', AccountTransaction::class);
        yield MenuItem::linkToCrud('Api Clients', 'fa fa-cogs', ApiClient::class);
        yield MenuItem::linkToCrud('Admins', 'fa fa-user', Admin::class);
    }
}
