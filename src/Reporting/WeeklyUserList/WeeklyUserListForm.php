<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Reporting\WeeklyUserList;

use App\Form\Type\ReportSumType;
use App\Form\Type\TeamType;
use App\Form\Type\WeekPickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<WeeklyUserList>
 */
final class WeeklyUserListForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('date', WeekPickerType::class, [
            'model_timezone' => $options['timezone'],
            'view_timezone' => $options['timezone'],
            'start_date' => $options['start_date'],
        ]);
        $builder->add('team', TeamType::class, [
            'multiple' => false,
            'required' => false,
            'width' => false,
        ]);
        $builder->add('sumType', ReportSumType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WeeklyUserList::class,
            'timezone' => date_default_timezone_get(),
            'start_date' => new \DateTime(),
            'csrf_protection' => false,
            'method' => 'GET',
        ]);
    }
}
