<?php

namespace BookBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use BookBundle\Entity\Book;

class BookType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('author')
            ->add(
                'cover',
                FileType::class,
                [
                    'data_class' => null,
                    'required' => false,
                    'empty_data' =>$options['data']->getCover()
                ]
            )
            ->add(
                'file',
                FileType::class,
                [
                    'data_class' => null,
                    'required' => false,
                    'empty_data' =>$options['data']->getFile()
                ]
            )
            ->add('readIt', null, ['widget' => 'single_text'])
            ->add('allowDownload');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Book::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bookbundle_book';
    }
}
