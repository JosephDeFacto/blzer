<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Loan;
use App\Form\LoanFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LoanController extends AbstractController
{

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    #[Route('/loan/book/{id}', name: 'app_loan')]
    public function index(Request $request, Book $book, LoggerInterface $logger): Response
    {

        $user = $this->getUser();
        $loan = new Loan();

        $form = $this->createForm(LoanFormType::class, $loan);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new DateTime();
            $loan->setUser($user);
            $loan->setBook($book);
            $loan->setStartDate($now);
            $loan->setEndDate($now);

            $this->em->persist($loan);
            $this->em->flush();

            return $this->redirectToRoute('app_index');
        }

        return $this->redirectToRoute('app_index');
    }
}
