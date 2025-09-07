<?php

namespace App\Controller\Admin;

use App\Entity\ImportFile;
use App\Entity\Log;
use App\Enum\LogActionEnum;
use App\Field\VichFileField;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ImportFileCrudController extends AbstractCrudController
{

    public function __construct(private LogService $logService)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ImportFile::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('filename')->hideOnForm()->setLabel('Nom du fichier'),
            AssociationField::new('importedBy')->hideOnForm()->setLabel('Importé par'),
            DateTimeField::new('importedAt')->hideOnForm()->setLabel('Date d\'import'),
            BooleanField::new('imported')->hideOnForm()->setLabel('Est importé')->setDisabled(true),
            VichFileField::new('file')->onlyOnForms()->setLabel('Fichier'),
            TextField::new('status')->hideOnForm()->renderAsHtml(),
            TextField::new('log')->renderAsHtml()->onlyOnDetail(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var ImportFile $importFile */
        $importFile = $entityInstance;

        $lastImportFile = $entityManager->getRepository(ImportFile::class)
            ->findOneBy([], ['id' => 'DESC']);

        $nextNumber = $lastImportFile ? $lastImportFile->getId() + 1 : 1;

        $this->logService->log(LogActionEnum::CREATE, $this->getUser(), 'Création du fichier d\'import #' . $nextNumber);

        $importFile->setImportedAt(new \DateTimeImmutable());
        $importFile->setImportedBy($this->getUser());

        parent::persistEntity($entityManager, $importFile);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var ImportFile $importFile */
        $importFile = $entityInstance;
        $this->logService->log(LogActionEnum::DELETE, $this->getUser(), 'Suppression du fichier d\'import ' . $importFile->getId());
        parent::deleteEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var ImportFile $importFile */
        $importFile = $entityInstance;
        $this->logService->log(LogActionEnum::UPDATE, $this->getUser(), 'Modification du fichier d\'import ' . $importFile->getId());
        parent::updateEntity($entityManager, $entityInstance);
    }


    public function configureActions(Actions $actions): Actions
    {
        $displayConditionExecute = function (ImportFile $importFile) {
            return !$importFile->isImported() && ($this->isGranted('ROLE_ADMIN'));
        };

        $displayConditionDeleteAndEdit = function (ImportFile $importFile) {
            return !$importFile->isImported() && ($this->isGranted('ROLE_ADMIN'));
        };

        $viewExecuteAction = Action::new('viewExecuteAction', 'Envoyer l\'email')
            ->displayAsLink()
            ->setHtmlAttributes(['data-foo' => 'bar'])
            ->setCssClass('btn')
            ->addCssClass('some-custom-css-class text-success')
            ->linkToRoute('viewExecuteAction', function (ImportFile $importFile): array {
                return
                    [
                        'id' => $importFile->getId(),
                        'filename' => $importFile->getFilename(),
                        'importedBy' => $importFile->getImportedBy(),
                        'importedAt' => $importFile->getImportedAt(),
                        'status' => $importFile->getStatus(),
                    ];
            })
            ->displayIf($displayConditionExecute);

        $actions->add(Crud::PAGE_DETAIL, $viewExecuteAction);
        $actions->add(Crud::PAGE_INDEX, $viewExecuteAction);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        $actions->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) use ($displayConditionDeleteAndEdit) {
            return $action->displayIf($displayConditionDeleteAndEdit);
        });
        $actions->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) use ($displayConditionDeleteAndEdit) {
            return $action->displayIf($displayConditionDeleteAndEdit);
        });
        $actions->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) use ($displayConditionDeleteAndEdit) {
            return $action->displayIf($displayConditionDeleteAndEdit);
        });
        $actions->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) use ($displayConditionDeleteAndEdit) {
            return $action->displayIf($displayConditionDeleteAndEdit);
        });

        return parent::configureActions($actions);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Fichier d\'import d\'emails')
            ->setDefaultSort(['id' => 'DESC'])
            ;
    }

}
