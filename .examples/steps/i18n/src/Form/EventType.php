<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, [
            'label' => 'event.title',
            'empty_data' => '',
        ])->add('description', TextareaType::class, [
            'label' => 'event.field.description',
            'empty_data' => '',
        ])->add('startsAt', DateTimeType::class, [
            'label' => 'event.field.starts_at',
            'widget' => 'single_text',
            'input' => 'datetime_immutable',
        ])->add('category', EntityType::class, [
            'class' => Category::class,
            'choice_label' => 'name',
            'label' => 'event.field.category',
            'required' => false,
            'placeholder' => 'Без категории',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Event::class, 'csrf_token_id' => 'event']);
    }
}
