<?php

declare(strict_types=1);

namespace App\Form;

use MeteoConcept\HCaptchaBundle\Form\HCaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordRequestFormType extends AbstractType
{
    protected bool $captchaEnabled;

    public function __construct(bool $captchaEnabled)
    {
        $this->captchaEnabled = $captchaEnabled;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['autocomplete' => 'email'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your email',
                    ]),
                ],
            ])
        ;

        if ($this->captchaEnabled) {
            $builder->add('captcha', HCaptchaType::class, [
                'label' => false,
                'help' => 'In order to fight spam, we ask you to fill in the captcha. This protects us against bots.',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
