<?php

namespace App\Form;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label'=>'Titre'
            ])
            ->add('nb_pages', null, [
                'label'=> 'Nb de pages'
            ])
            ->add('summary', null, [
                'label'=> 'Résumé'
            ])
            ->add('publishedAt', null, [
                'label'=> 'Date de publication',
                'widget'=>'single_text'
            ])
            ->add('author', EntityType::class, [
                'label'=>'Auteur',
                'class'=>Author::class,
                'expanded'=>true

            ])
            ->add('genre', EntityType::class, [
                'label'=>'Genre',
                'class'=>Genre::class,
                'expanded'=>true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
