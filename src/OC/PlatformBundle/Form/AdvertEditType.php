<?php

namespace OC\PlatformBundle\Form;

// use Symfony\Component\Form\AbstractType;
// use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class AdvertEditType extends AdvertType
{
    public function getParent() {
        return AdvertType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('date');
                // 3e argument : tableau d'options du champ
                /*->add('categories', CollectionType::class, array(
                    "entry_type" => CategoryType::class,
                    "allow_add" => true,
                    "allow_delete" => true
                ))*/
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oc_platformbundle_advertedit';
    }


}
