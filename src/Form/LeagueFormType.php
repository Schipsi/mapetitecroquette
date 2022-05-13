<?php

namespace App\Form;

use App\Entity\League;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class LeagueFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('league', EntityType::class, [
                'class' => League::class,
                'choice_label' => 'shortName',
                'label' => 'League :',
                'multiple' => false,
                'expanded' => false,
                'attr' => [
                    'class' => 'mx-2 pr-8 form-select px-3 py-1.5 text-base font-normal text-gray-700
                        bg-white bg-clip-padding bg-no-repeat border border-solid border-gray-300 rounded
                        transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none',
                ],
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('league')
                        ->addOrderBy('league.active', 'DESC')
                        ->addOrderBy('league.shortName', 'ASC');
                },
            ])
        ;
    }
}
