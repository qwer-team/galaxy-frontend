<?php

namespace Galaxy\FrontendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JumpRequestType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('x', 'integer')
                ->add('y', 'integer')
                ->add('z', 'integer')
                ->add('superjump', 'checkbox', array("required" => false))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'data_class' => 'Galaxy\FrontendBundle\Entity\JumpRequest',
        ));
    }

    public function getName()
    {
        return '';
    }

}