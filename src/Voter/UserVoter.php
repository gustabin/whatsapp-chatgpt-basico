<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Voter;

use App\Entity\User;
use App\Security\RolePermissionManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * A voter to check permissions on user profiles.
 *
 * @extends Voter<string, User>
 */
final class UserVoter extends Voter
{
    private const ALLOWED_ATTRIBUTES = [
        'view',
        'edit',
        'roles',
        'teams',
        'password',
        '2fa',
        'delete',
        'preferences',
        'api-token',
        'hourly-rate',
        'view_team_member',
        'contract',
    ];

    public function __construct(private RolePermissionManager $permissionManager)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!($subject instanceof User)) {
            return false;
        }

        if (!\in_array($attribute, self::ALLOWED_ATTRIBUTES)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!($user instanceof User)) {
            return false;
        }

        if ($attribute === 'contract') {
            return $this->permissionManager->hasRolePermission($user, 'contract_other_profile');
        }

        if ($attribute === 'view_team_member') {
            if ($subject->getId() !== $user->getId()) {
                return false;
            }

            return $this->permissionManager->hasRolePermission($user, 'view_team_member');
        }

        if ($attribute === 'delete') {
            if ($subject->getId() === $user->getId()) {
                return false;
            }

            return $this->permissionManager->hasRolePermission($user, 'delete_user');
        }

        if ($attribute === 'password') {
            if (!$subject->isInternalUser()) {
                return false;
            }
        }

        if ($attribute === '2fa') {
            // can only be activated by the logged-in user for himself or by a super-admin
            return $subject->getId() === $user->getId() || $user->isSuperAdmin();
        }

        $permission = $attribute;

        // extend me for "team" support later on
        if ($subject->getId() === $user->getId()) {
            $permission .= '_own';
        } else {
            $permission .= '_other';
        }

        $permission .= '_profile';

        return $this->permissionManager->hasRolePermission($user, $permission);
    }
}
