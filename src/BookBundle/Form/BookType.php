<?php

namespace BookBundle\Form;

use BookBundle\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\HttpFoundation\File\File;

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
                    'empty_data' =>
                        $options['data']->getCover()
                            ? new File(__DIR__.'/../../../web/uploads/covers/'. $options['data']->getCover())
                            : null
                ]
            )
            ->add(
                'file',
                FileType::class,
                [
                    'data_class' => null,
                    'required' => false,
                    'empty_data' =>
                        $options['data']->getFile()
                            ? new File(__DIR__.'/../../../web/uploads/files/'. $options['data']->getFile())
                            : null
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
