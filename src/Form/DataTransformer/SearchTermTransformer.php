<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\DataTransformer;

use App\Utils\SearchTerm;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class SearchTermTransformer implements DataTransformerInterface
{
    /**
     * Transforms a SearchTerm object to a string.
     *
     * @param SearchTerm|null $searchTerm
     * @return string
     */
    public function transform(mixed $searchTerm): mixed
    {
        if (empty($searchTerm) || !($searchTerm instanceof SearchTerm)) {
            return '';
        }

        return $searchTerm->getOriginalSearch();
    }

    /**
     * Transforms a string to a SearchTerm object.
     *
     * @param string|null $searchTerm
     * @return SearchTerm|null
     * @throws TransformationFailedException if object (issue) is not found
     */
    public function reverseTransform(mixed $searchTerm): mixed
    {
        if (empty($searchTerm)) {
            return null;
        }

        return new SearchTerm($searchTerm);
    }
}
