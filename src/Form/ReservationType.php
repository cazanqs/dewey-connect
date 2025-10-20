<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Trajet;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $trajet = $options['trajet'];
        $choices = [];
        
        for ($i = 1; $i <= $trajet->getSiegesLibres(); $i++) {
            $label = $i === 1 ? "$i place" : "$i places";
            $choices[$label] = $i;
        }
        
        $builder
            ->add('sieges_reserves', ChoiceType::class, [
                'label' => 'Nombre de places à réserver',
                'choices' => $choices,
                'placeholder' => 'Choisissez le nombre de places',
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'trajet' => null,
        ]);
    }
}
