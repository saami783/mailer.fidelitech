<?php

namespace App\Controller\Admin\Import;

use App\Controller\Admin\ImportFileCrudController;
use App\Entity\ImportFile;
use App\Entity\User;
use App\Enum\LogActionEnum;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Cette classe est responsable de l'exécution liée aux commandes pour le crudController ImportFile.
 */
class ExecuteCommandController extends AbstractController
{

    public function __construct(private KernelInterface $kernel, private EntityManagerInterface $entityManager, private LogService $logService)
    {
    }

    /**
     * Exécute une commande donnée depuis l'interface d'administration.
     * @throws Exception
     */
    #[Route('/admin/execute/command', name: 'viewExecuteAction')]
    public function executeCommandFromBackOffice(ImportFile $importFile): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $this->logService->log(LogActionEnum::IMPORT, $user, 'Exécution de la commande app:send-emails');

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $output = new BufferedOutput();
        $command = 'app:send-emails';
        $argumentsArr = [
            'command' => $command,
            'file' => $importFile->getFilename(),
        ];

        $statusCode = $application->run(new ArrayInput($argumentsArr), $output);
        $result = $output->fetch();

        $importFile->setLog($result);

        if ($statusCode === Command::SUCCESS) {
            $importFile->setStatus("<a class='text-success'><strong>OK</strong></a>");
            $importFile->setImportedAt(new \DateTimeImmutable());
            $importFile->setImportedBy($user);
            $importFile->setImported(true);
        } else {
            $importFile->setStatus("<a class='text-danger'><strong>KO</strong></a>");
            $importFile->setImported(false);
        }

        $this->entityManager->persist($importFile);
        $this->entityManager->flush();

        if ($statusCode === Command::SUCCESS) {
            return $this->redirectToRoute('admin', [
                'crudControllerFqcn' => ImportFileCrudController::class,
                'crudAction' => 'detail',
                'entityId' => $importFile->getId()
            ]);
        } else {
            $this->addFlash('danger',
                "Le serveur a renvoyé une erreur à l'exécution du job. Veuillez réessayer. 
             Si le problème persiste, contactez le support.");

            return $this->redirectToRoute('admin', [
                'crudControllerFqcn' => ImportFileCrudController::class,
                'crudAction' => 'detail',
                'entityId' => $importFile->getId()
            ]);
        }
    }


}