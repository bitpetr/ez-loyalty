<?php

namespace App\Controller\Admin;

use App\Entity\AccountTransaction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AccountTransactionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AccountTransaction::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $operations = array_combine(AccountTransaction::getOperations(), AccountTransaction::getOperations());
        $currencies = array_combine(
            AccountTransaction::getCurrencyCodes(),
            AccountTransaction::getCurrencyCodes()
        );

        return [
            AssociationField::new('account'),
            ChoiceField::new('operation')->setChoices($operations),
            DateTimeField::new('time'),
            TextField::new('value')->onlyOnIndex(),
            NumberField::new('coinsAmount')->onlyOnForms(),
            NumberField::new('moneyAmount')->onlyOnForms(),
            ChoiceField::new('currency')->setChoices($currencies)->onlyOnForms(),
            AssociationField::new('source'),
            AssociationField::new('campaign'),
            NumberField::new('orderId'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural('Account Transactions')
            ->setEntityLabelInSingular('Account Transaction')
            ->setDefaultSort(['time' => 'DESC'])
            ->setSearchFields(['account.id','operation','time','source.name','orderId']);
    }


    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('account')
            ->add('operation')
            ->add('time')
            ->add('source')
            ->add('campaign')
            ->add('orderId');
    }
}
