<?php

namespace App\Controller\Admin;

use App\Entity\DeletedAccount;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class  DeletedAccountCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DeletedAccount::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('code'),
            TextField::new('email'),
            DateTimeField::new('creationDate'),
            DateTimeField::new('deletionDate'),
            NumberField::new('coins'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setSearchFields(['code','email'])
            ->setEntityLabelInPlural('Deleted Accounts')
            ->setEntityLabelInSingular('Deleted Account');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)->disable(Action::EDIT, Action::NEW);
    }
}
