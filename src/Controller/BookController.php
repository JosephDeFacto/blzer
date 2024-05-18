<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Loan;
use App\Form\LoanFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/book/view/{id}', name: 'app_book')]
    public function index(Book $book): Response
    {

        $loan = new Loan();

        $form = $this->createForm(LoanFormType::class, $loan, [
            'action' => $this->generateUrl('app_loan', ['id' => $book->getId()]),
            'method' => 'POST',
        ]);

        return $this->render('book/index.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }
}
