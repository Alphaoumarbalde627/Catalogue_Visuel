<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Form\RegistrationFormType;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class UserValidationTest extends TestCase
{
    public function testEmailConstraintsAreDefined(): void
    {
        $classAttributes = (new \ReflectionClass(User::class))->getAttributes();
        $classNames = array_map(static fn ($attribute) => $attribute->getName(), $classAttributes);
        $this->assertContains(UniqueEntity::class, $classNames);

        $emailProperty = new \ReflectionProperty(User::class, 'email');
        $emailAttributes = array_map(static fn ($attribute) => $attribute->getName(), $emailProperty->getAttributes());

        $this->assertContains(Assert\NotBlank::class, $emailAttributes);
        $this->assertContains(Assert\Email::class, $emailAttributes);
    }

    public function testPasswordConstraintsAreDefined(): void
    {
        $passwordProperty = new \ReflectionProperty(User::class, 'password');
        $passwordAttributes = array_map(static fn ($attribute) => $attribute->getName(), $passwordProperty->getAttributes());

        $this->assertContains(Assert\NotBlank::class, $passwordAttributes);
        $this->assertContains(Assert\Length::class, $passwordAttributes);
        $this->assertContains(Assert\Regex::class, $passwordAttributes);

        $regexConstraint = $passwordProperty->getAttributes(Assert\Regex::class)[0]->newInstance();
        $this->assertSame('Le mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spécial.', $regexConstraint->message);

        $lengthConstraint = $passwordProperty->getAttributes(Assert\Length::class)[0]->newInstance();
        $this->assertSame(8, $lengthConstraint->min);
        $this->assertSame('Le mot de passe doit contenir au moins 8 caractères.', $lengthConstraint->minMessage);
    }

    public function testRegistrationFormAcceptsMappedPlainPassword(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory();

        $user = new User();
        $form = $formFactory->create(RegistrationFormType::class, $user);
        $form->submit([
            'email' => 'admin@example.com',
            'agreeTerms' => true,
            'plainPassword' => 'Azerty123!',
        ]);

        $this->assertTrue($form->isValid(), (string) $form->getErrors(true, false));
    }
}
