<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(
                        message: "L'adresse e-mail est obligatoire.",
                        groups: ['registration'],
                    ),
                    new Email(
                        message: "L'adresse e-mail n'est pas valide.",
                        groups: ['registration'],
                    ),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue(
                        message: 'Vous devez accepter les conditions d’utilisation.',
                        groups: ['registration'],
                    ),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'always_empty' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(
                        message: 'Le mot de passe est obligatoire.',
                        groups: ['registration'],
                    ),
                    new Length(
                        min: 8,
                        minMessage: 'Le mot de passe doit contenir au moins 8 caractères.',
                        max: 4096,
                        maxMessage: 'Le mot de passe ne peut pas dépasser {{ limit }} caractères.',
                        groups: ['registration'],
                    ),
                    new Regex(
                        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).+$/',
                        message: 'Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial.',
                        groups: ['registration'],
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['registration'],
        ]);
    }
}
