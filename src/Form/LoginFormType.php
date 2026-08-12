<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => [
                    'placeholder' => 'exemple@gmail.com',
                    'autocomplete' => 'email',
                    'autofocus' => true,
                    'class' => 'w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#2b7bc6] focus:bg-white focus:ring-4 focus:ring-blue-100',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => [
                    'placeholder' => '••••••••',
                    'autocomplete' => 'current-password',
                    'class' => 'w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#2b7bc6] focus:bg-white focus:ring-4 focus:ring-blue-100',
                ],
            ])
            // CSRF field is handled by the form system via options (see configureOptions)
            ->add('_remember_me', CheckboxType::class, [
                'label' => 'Se souvenir de moi',
                'required' => false,
                'attr' => [
                    'class' => 'h-4 w-4 rounded border-slate-300 text-[#2b7bc6]',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Se connecter',
                'attr' => [
                    'class' => 'w-full rounded-2xl bg-[#2b7bc6] px-4 py-3 font-semibold text-white shadow-lg transition hover:bg-[#235ea3]',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'attr' => ['class' => 'space-y-5'],
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'authenticate',
        ]);
    }
}
