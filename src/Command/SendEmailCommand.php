<?php

namespace App\Command;

use App\Entity\Emails;
use App\Entity\ImportFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsCommand(
    name: 'app:send-emails',
    description: 'Envoi d\'emails à partir d\'un fichier plat',
)]
class SendEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Nom du fichier dans le dossier import/');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filename = $input->getArgument('file');
        /** @var ImportFile $importFile */
        $importFile = $this->em->getRepository(ImportFile::class)->findOneBy(['filename' => $filename]);
        $importDir = __DIR__ . '/../../import/';
        $archiveDir = $importDir . 'archive/';

        $path = $importDir . $filename;

        if (!file_exists($path)) {
            $io->error("Le fichier $filename n'existe pas dans import/");
            return Command::FAILURE;
        }

        $emails = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $sentCount = 0;
        $ignoredCount = 0;
        $invalidCount = 0;
        $failedCount = 0;

        if (!$emails) {
            $io->warning("Le fichier est vide !");
        } else {
            $existingAddresses = $this->em->getRepository(Emails::class)
                ->createQueryBuilder('e')
                ->select('e.address')
                ->getQuery()
                ->getSingleColumnResult();

            $existingSet = array_flip(array_map('strtolower', $existingAddresses));
            $newEmails = [];

            foreach ($emails as $lineNumber => $emailAddress) {
                $emailAddress = trim(strtolower($emailAddress));

                if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                    $io->warning("Ligne " . ($lineNumber + 1) . " ignorée : email invalide ($emailAddress)");
                    $invalidCount++;
                    continue;
                }

                if (isset($existingSet[$emailAddress])) {
                    $io->note("Email déjà existant en base : $emailAddress (ignoré)");
                    $ignoredCount++;
                    continue;
                }

                $email = (new TemplatedEmail())
                    ->from(new Address('contact@fidelitech.fr', 'Fidelitech'))
                    ->to($emailAddress)
                    ->htmlTemplate('mails/email.html.twig')
                    ->subject('Votre avis compte – Étude sur la fidélisation des clients');

                try {
                    $this->mailer->send($email);
                    $io->success("Email envoyé à $emailAddress");

                    $entity = new Emails();
                    $entity->setAddress($emailAddress);
                    $this->em->persist($entity);

                    $existingSet[$emailAddress] = true;
                    $newEmails[] = $emailAddress;

                    $sentCount++;
                } catch (TransportExceptionInterface $e) {
                    $io->error("Erreur d'envoi à $emailAddress : " . $e->getMessage());
                    $failedCount++;
                }
            }


            if (!empty($newEmails)) {
                $this->em->flush();
            }
        }

        $summary = <<<TXT
        Résultat de l'import :
        - Emails envoyés : $sentCount
        - Emails ignorés (déjà en base) : $ignoredCount
        - Emails invalides : $invalidCount
        - Échecs d'envoi : $failedCount
        TXT;

        $io->section("Résumé");
        $io->text($summary);

        // === archivage du fichier ===
        if (!is_dir($archiveDir)) {
            mkdir($archiveDir, 0777, true);
        }

        $archivedPath = $archiveDir . $filename;

        if (rename($path, $archivedPath)) {
            $io->success("Fichier archivé sous : $archivedPath");
        } else {
            $io->error("Impossible de déplacer le fichier vers archive/");
        }

        $output->writeln($summary);

        return Command::SUCCESS;
    }
}
