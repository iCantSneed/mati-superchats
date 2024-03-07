<?php

namespace Mati\Command;

use Mati\Rumble\RssLivestreamUrlFetcher;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('mati:rsstest')]
class MatiRsstestCommand extends Command
{
    public function __construct(
        private RssLivestreamUrlFetcher $livestreamUrlFetcher,
        private LoggerInterface $logger,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $livestreamUrl = $this->livestreamUrlFetcher->fetchLivestreamUrl();
        if ($livestreamUrl === null) {
            return Command::FAILURE;
        }

        $this->logger->notice('Got livestream URL', ['livestreamUrl' => $livestreamUrl]);

        return Command::SUCCESS;
    }
}
