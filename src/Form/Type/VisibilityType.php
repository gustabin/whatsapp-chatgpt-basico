<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Type;

use App\Repository\Query\VisibilityInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Custom form field type to select a visibility.
 * @extends AbstractType<int>
 */
final class VisibilityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'visible',
            'choices' => [
                'both' => VisibilityInterface::SHOW_BOTH,
                'yes' => VisibilityInterface::SHOW_VISIBLE,
                'no' => VisibilityInterface::SHOW_HIDDEN,
            ],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
