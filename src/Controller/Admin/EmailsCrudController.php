<?php

namespace App\Controller\Admin;

use App\Entity\Emails;
use App\Enum\LogActionEnum;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EmailsCrudController extends AbstractCrudController
{

    public function __construct(private LogService $logService) {

    }

    public static function getEntityFqcn(): string
    {
        return Emails::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            EmailField::new('address')->setLabel('Adresse email'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInPlural('Liste des adresses emails déjà traitées')
            ->setDefaultSort(['id' => 'DESC'])
            ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('address')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->remove(Action::INDEX, 'edit');
        return $actions;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Emails $email */
        $email = $entityInstance;
        $this->logService->log(LogActionEnum::CREATE, $this->getUser(), 'Ajout de l\'adresse email ' . $email->getAddress());
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Emails $email */
        $email = $entityInstance;
        $this->logService->log(LogActionEnum::DELETE, $this->getUser(), 'Suppression de l\'adresse email ' . $email->getAddress());
        parent::deleteEntity($entityManager, $entityInstance);
    }


}
