<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control email-input'],
            ])
            ->add('password', PasswordType::class, [
                'attr' => ['class' => 'form-control password-input'],
                'label' => 'Password',
                'required' => true,
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Eau' => 'ROLE_WATER',
                    'Filibus' => 'ROLE_FILIBUS',
                    'Edf' => 'ROLE_EDF',
                    'Administrateur' => 'ROLE_ADMIN',
                    // Ajoutez d'autres rôles selon vos besoins
                ],
                'multiple' => false,
                'expanded' => true, // Si vous voulez des cases à cocher
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
