<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/*
 * @todo supprimer l'action delete
 * @todo ajouter l'action consulter
 */
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('name')->setLabel('Nom'),
            EmailField::new('email')->setLabel('Adresse email'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->remove(Action::INDEX, 'new');
        $actions->remove(Action::INDEX, 'delete');
        $actions->remove(Action::INDEX, 'edit');
        $actions->add(Action::INDEX, 'detail');

        $actions->remove(Action::DETAIL, 'delete');
        $actions->remove(Action::DETAIL, 'edit');

        return $actions;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInPlural('Utilisateurs internes de l\'application')
            ;
    }
}
