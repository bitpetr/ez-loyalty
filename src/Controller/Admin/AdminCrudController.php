<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public static function getEntityFqcn(): string
    {
        return Admin::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $roles = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_MERCHANT'];

        return [
            TextField::new('username'),
            TextField::new('plainPassword', 'Password')->setFormType(PasswordType::class)->onlyWhenUpdating()
                ->setFormTypeOption('attr', ['placeholder'=>'Blank = unchanged']),
            TextField::new('plainPassword', 'Password')->setFormType(PasswordType::class)->onlyWhenCreating()
                ->setRequired(true),
            ChoiceField::new('roles')->setChoices(array_combine($roles,$roles))->allowMultipleChoices()
        ];
    }

    public function setHasher(UserPasswordHasherInterface $hasher): void
    {
        $this->hasher = $hasher;
    }

    private function updateUserPassword(Admin $user): void
    {
        if ($user->getPlainPassword()) {
            $user->setPassword($this->hasher->hashPassword($user, $user->getPlainPassword()));
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->updateUserPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->updateUserPassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }
}
