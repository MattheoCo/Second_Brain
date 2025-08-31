<?php
namespace App\Form;

use App\Entity\Grade;
use App\Enum\SessionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GradeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, ['label' => 'Intitulé'])
            ->add('sessionType', ChoiceType::class, [
                'label' => 'Type',
                'choices' => SessionType::cases(),
                'choice_label' => fn(SessionType $t) => $t->value,
                'choice_value' => fn(?SessionType $t) => $t?->value,
                'placeholder' => 'Sélectionner',
            ])
            ->add('score', NumberType::class, ['label' => 'Note (/20)', 'scale' => 2, 'html5' => true])
            ->add('weight', NumberType::class, ['label' => 'Coefficient', 'scale' => 2, 'html5' => true, 'empty_data' => '1'])
            ->add('gradedAt', DateTimeType::class, ['label' => 'Date', 'widget' => 'single_text', 'html5' => true]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Grade::class]);
    }
}
