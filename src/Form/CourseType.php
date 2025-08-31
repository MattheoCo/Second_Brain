<?php
namespace App\Form;

use App\Entity\Course;
use App\Entity\CourseFolder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom du cours'])
            ->add('code', TextType::class, ['required' => false, 'label' => 'Code'])
            ->add('ects', NumberType::class, ['required' => false, 'label' => 'ECTS', 'scale' => 1])
            ->add('description', TextareaType::class, ['required' => false, 'label' => 'Description'])
            ->add('folder', EntityType::class, [
                'class' => CourseFolder::class,
                'required' => false,
                'label' => 'Dossier',
                'placeholder' => 'Aucun',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Course::class]);
    }
}
