<?php

namespace App\Form;

use App\Entity\Trajet;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class TrajetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('point_de_depart', null, [
                'label' => 'Point de départ',
                'attr' => ['placeholder' => 'Point de départ']
            ])
            ->add('destination', null, [
                'attr' => ['placeholder' => 'Destination']
            ])
            ->add('date_et_heure', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => ['placeholder' => 'Date et heure']
            ])
            ->add('sieges_libres', null, [
                'label' => 'Sièges libres',
                'attr' => ['placeholder' => 'Sièges libres']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trajet::class,
        ]);
    }
}
