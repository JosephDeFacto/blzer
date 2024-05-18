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
        $builder
            ->add('startDate', DateType::class, [])
            ->add('endDate', DateType::class, [

            ])
            ->add('user', HiddenType::class, [
                'data_class' => User::class,
            ])
            ->add('book', HiddenType::class, [
                'data_class' => Book::class,
            ])
            ->add('submit', SubmitType::class, [])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Loan::class,
        ]);
    }
}
