<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Length;

class ProfilFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, [
                'attr' => ['placeholder' => 'Votre adresse email']
            ])
            ->add('prenom', null, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Votre prénom']
            ])
            ->add('nom_de_famille', null, [
                'label' => 'Nom de famille',
                'attr' => ['placeholder' => 'Votre nom de famille']
            ])
            ->add('telephone', null, [
                'label' => 'Téléphone',
                'attr' => ['placeholder' => 'Votre numéro de téléphone']
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe (optionnel)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Laisser vide pour ne pas changer'
                ],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => "Le mot de passe doit contenir au moins {{ limit }} caractères.",
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}