<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

final class AclDecisionManager
{
    public function __construct(private AccessDecisionManagerInterface $decisionManager)
    {
    }

    /**
     * @param TokenInterface $token
     * @return bool
     */
    public function isFullyAuthenticated(TokenInterface $token): bool
    {
        if ($this->decisionManager->decide($token, ['IS_AUTHENTICATED_FULLY'])) {
            return true;
        }

        return false;
    }
}
