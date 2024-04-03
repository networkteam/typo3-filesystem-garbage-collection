<?php

declare(strict_types=1);

namespace Networkteam\FilesystemGarbageCollection\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CleanupFolderCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('Removes files by given age in folder')
            ->addArgument('folder', InputArgument::REQUIRED, 'folder identifier like "1:/_temp_/"')
            ->addArgument('days', InputArgument::REQUIRED, 'Maximum age in days')
            ->addOption('remove-empty-subfolders', 'e', InputOption::VALUE_NONE, 'Remove empty subfolders');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $folder = $resourceFactory->getFolderObjectFromCombinedIdentifier($input->getArgument('folder'));
        $deleteBefore = time() - ((int)$input->getArgument('days') * 24 * 60 * 60);

        foreach ($folder->getFiles(recursive: true) as $file) {
            if ($file->getCreationTime() < $deleteBefore) {
                $output->writeln('Deleting ' . $file->getIdentifier());
                $file->delete();
            }
        }

        if ($input->getOption('remove-empty-subfolders')) {
            foreach ($folder->getSubfolders(filterMode: Folder::FILTER_MODE_NO_FILTERS) as $subfolder) {
                $this->removeEmptyFolderRecursive($subfolder);
            }
        }

        return Command::SUCCESS;
    }

    protected function removeEmptyFolderRecursive(Folder $folder): void
    {
        $subFolders = $folder->getSubfolders(filterMode: Folder::FILTER_MODE_NO_FILTERS);
        if ($subFolders === [] && $folder->getFiles(filterMode: Folder::FILTER_MODE_NO_FILTERS) === []) {
            $folder->delete();
            return;
        }

        foreach ($subFolders as $subfolder) {
            $this->removeEmptyFolderRecursive($subfolder);
        }
    }
}
