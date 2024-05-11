<?php

namespace App\Command;

use App\Entity\Book;
use App\Entity\Genre;
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

        $genres = [];

        foreach ($books['items'] as $book) {
            foreach ($book['volumeInfo']['categories'] as $category) {
                $genres[] = $category;
            }
        }

        foreach (array_unique($genres) as $genreName) {

            $genre = $this->entityManager->getRepository(Genre::class)->findOneBy(['name' => $genreName]);

            if (!$genre) {
                $genre = new Genre();
                $genre->setName($genreName);
                $this->entityManager->persist($genre);
            }
        }

        $this->entityManager->flush();

        foreach ($books['items'] as $bookData) {
            $book = new Book();
            $book->setTitle($bookData['volumeInfo']['title']);
            foreach ($bookData['volumeInfo']['authors'] as $author) {
                $book->setAuthor($author);
            }
            $book->setPageCount($bookData['volumeInfo']['pageCount']);
            $book->setGenre($this->entityManager->getRepository(Genre::class)->findOneBy(['name' => $bookData['volumeInfo']['categories'][0]]));

            $this->entityManager->persist($book);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}