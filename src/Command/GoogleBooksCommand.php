<?php

namespace App\Command;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'app:create-book',
    description: 'Google Books API',
    aliases: ['app:add-book'],
    hidden: false
)]
class GoogleBooksCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startIndex = 0;
        $maxResults = 40;

        do {
            $endpoint = sprintf('https://www.googleapis.com/books/v1/volumes?q=subject:programming&startIndex=' . $startIndex . '&maxResults=' . $maxResults);

            $client = HttpClient::create();

            $response = $client->request('GET', $endpoint);

            if ($response->getStatusCode() !== 200) {
                $output->writeln('<error>' . $response->getStatusCode() . '</error>');
            }

            $books = $response->toArray();

            foreach ($books['items'] as $item) {
                $book = new Book();
                $book->setTitle($item['volumeInfo']['title']);
                if (is_array($item['volumeInfo']['authors'])) {
                    foreach ($item['volumeInfo']['authors'] as $author) {
                        $book->setAuthor($author);
                    }
                } else {
                    $book->setAuthor($item['volumeInfo']['authors']);
                }

                $this->entityManager->persist($book);
            }

            $this->entityManager->flush();

            $startIndex += $maxResults;
        } while (count($books['items']) > 0);

        $output->writeln('<info>Books created!</info>');

        return Command::SUCCESS;
    }
}