<?php

namespace App\Command;

use App\Entity\Book;
use App\Service\GenreService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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

    protected function configure(): void
    {
        $this
            ->addArgument('subject', InputArgument::REQUIRED, 'Add subject!');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $subject = $input->getArgument('subject');
        $startIndex = 0;
        $maxResults = 40;


        $endpoint = sprintf('https://www.googleapis.com/books/v1/volumes?q=subject:%s&startIndex=%d&maxResults=%d', $subject, $startIndex, $maxResults);

        $client = HttpClient::create();

        $response = $client->request('GET', $endpoint);


        if ($response->getStatusCode() !== 200) {
            $output->writeln('<error>' . $response->getStatusCode() . '</error>');
        }

        $books = $response->toArray();

        foreach ($books['items'] as $item) {
            $book = new Book();
            $book->setTitle($item['volumeInfo']['title']);
            if (isset($item['volumeInfo']['description'])) {
                $book->setDescription($item['volumeInfo']['description']);
            }
            if (is_array($item['volumeInfo']['authors'])) {
                foreach ($item['volumeInfo']['authors'] as $author) {
                    $book->setAuthor($author);
                }
            } else {
                $book->setAuthor($item['volumeInfo']['authors']);
            }
            $book->setPageCount($item['volumeInfo']['pageCount']);

            $this->entityManager->persist($book);
        }

        $this->entityManager->flush();

        $output->writeln('<info>Books created</info>');
        return Command::SUCCESS;
    }
}