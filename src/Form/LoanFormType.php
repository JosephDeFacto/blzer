<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\Loan;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoanFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dt = new \DateTime();

        $newDT = (clone $dt)->modify('+14 day');

        $builder
            ->add('startDate', DateType::class, [
                'label' => 'Date of loan',
                'disabled' => true,
                'format' => 'dd-MM-yyyy',
                'data' => $dt,
            ])
            ->add('endDate', DateType::class, [
                'attr' => ['class' => 'flex'],
                'label' => 'Date of return',
                'disabled' => true,
                'format' => 'dd-MM-yyyy',
                'data' => $newDT,
            ])

            ->add('user', HiddenType::class, [
                'data_class' => User::class,
            ])

            ->add('book', HiddenType::class, [
                'data_class' => Book::class,
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'flex flex-column items-center bg-white mt-5 hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 rounded shadow'],
                'label' => 'Borrow',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Loan::class,
        ]);
    }
}
