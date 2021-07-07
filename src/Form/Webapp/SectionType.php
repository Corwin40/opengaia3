<?php

namespace App\Form\Webapp;

use App\Entity\Webapp\Article;
use App\Entity\Webapp\Section;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class SectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('attrId')
            ->add('attrName')
            ->add('attrClass')
            ->add('page')
            ->add('contentType', ChoiceType::class, [
                'choices'  => [
                    'aucun' => 'none',
                    'introduction' => 'intro',
                    'Un article' => 'One_article',
                    'Plusieurs articles' => 'More_article',
                    'Une categorie' => 'Category',
                    'Un évènement' => 'One_event',
                    'les évènements' => 'Events',
                    'membre' => 'Member',
                    "bulletin d'adhésion" => "Adhesion",
                    "image seule" => "Media_one",
                    "liste des avis" => "Avis",
                    'Autres' => 'Others'
                ],
            ])
            ->add('oneArticle')
            ->add('favorites')
            ->add('isSectionFluid')
            ->add('position')
            ->add('isShowtitle')
            ->add('backgroundImageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer',
                'download_label' => 'Télecharger',
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Section::class,
        ]);
    }
}
