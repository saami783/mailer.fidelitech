<?php

namespace App\Controller\Admin;

use App\Entity\Log;
use App\Enum\LogActionEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class LogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Log::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('message')->setLabel('Message'),
            TextField::new('action')->setLabel('Action'),
            AssociationField::new('user')->setLabel('Utilisateur'),
            DateTimeField::new('createdAt')->setLabel('Crée le'),
        ];
    }

    /**
     * Configure les actions qui seront disponibles pour l'entité Logger.
     *
     * Ici, je désactive toutes les actions sauf celle de visualisation.
     *
     * @param Actions $actions L'instance Actions à configurer.
     * @return Actions L'instance Actions configurée.
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::SAVE_AND_ADD_ANOTHER, Action::EDIT, Action::DELETE);
    }

    /**
     * Configure les paramètres généraux pour l'entité Logger.
     *
     * Ici, je définis le nom de l'entité en pluriel, j'active l'affichage des actions de l'entité inline
     * et je définis l'ordre par défaut pour la liste des entités (par id descendant).
     *
     * @param Crud $crud L'instance Crud à configurer.
     * @return Crud L'instance Crud configurée.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInPlural('Journal de logs')
            ->setDefaultSort(['id' => 'DESC'])
            ;
    }

    /**
     * Configure les filtres qui seront disponibles pour l'entité Logger.
     *
     * @param Filters $filters L'instance Filters à configurer.
     * @return Filters L'instance Filters configurée.
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('createdAt')
            ->add(ChoiceFilter::new('action')
                ->setChoices(array_combine(LogActionEnum::ALL, LogActionEnum::ALL))
            )
            ->add('user')
            ->add('message')
            ;
    }

}
