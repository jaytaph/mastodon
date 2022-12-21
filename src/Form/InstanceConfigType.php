<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstanceConfigType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('instanceDomain')
            ->add('instanceTitle')
            ->add('instanceDescription')
            ->add('instanceShortDescription')
            ->add('instanceEmail')
//            ->add('languages')
            ->add('registrationAllowed')
            ->add('approvalRequired')
            ->add('inviteEnabled')
            ->add('thumbnailUrl')
            ->add('adminAccount')
            ->add('statusLength')
            ->add('mediaAttachments')
            ->add('charactersPerUrl')
            ->add('accountTags')
            ->add('optionsPerPoll')
            ->add('characersPerOption')
            ->add('minimumPollExpiration')
            ->add('maximumPollExpiration')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}
