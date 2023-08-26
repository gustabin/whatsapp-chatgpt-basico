<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventSubscriber;

use App\Entity\User;
use App\Event\ConfigureMainMenuEvent;
use App\Utils\MenuItemModel;
use KevinPapst\TablerBundle\Event\MenuEvent;
use KevinPapst\TablerBundle\Model\MenuItemInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class MenuBuilder configures the main navigation.
 * @internal
 */
final class MenuBuilderSubscriber implements EventSubscriberInterface
{
    public function __construct(private EventDispatcherInterface $eventDispatcher, private Security $security)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MenuEvent::class => ['onSetupNavbar', 100],
        ];
    }

    public function onSetupNavbar(MenuEvent $event): void
    {
        $menuEvent = new ConfigureMainMenuEvent();

        /** @var User $user */
        $user = $this->security->getUser();

        // error pages don't have a user and will fail when is_granted() is called
        if (null !== $user) {
            $this->eventDispatcher->dispatch($menuEvent);
        }

        foreach ($menuEvent->getMenu()->getChildren() as $child) {
            if ($child->getRoute() === null && !$child->hasChildren()) {
                continue;
            }
            $event->addItem($child);
        }

        if ($menuEvent->getAppsMenu()->hasChildren()) {
            $event->addItem($menuEvent->getAppsMenu());
        }
        if ($menuEvent->getAdminMenu()->hasChildren()) {
            $event->addItem($menuEvent->getAdminMenu());
        }
        if ($menuEvent->getSystemMenu()->hasChildren()) {
            $event->addItem($menuEvent->getSystemMenu());
        }

        $route = $event->getRequest()->get('_route');
        if (!\is_string($route)) {
            return;
        }

        $this->activateByRoute($route, $event->getItems());
    }

    /**
     * @param string $route
     * @param MenuItemInterface[] $items
     * @return bool
     */
    private function activateByRoute(string $route, array $items): bool
    {
        foreach ($items as $item) {
            if ($item instanceof MenuItemModel) {
                if ($item->isChildRoute($route)) {
                    $item->setIsActive(true);

                    return true;
                }
            }

            if ($item->getRoute() === $route) {
                $item->setIsActive(true);

                return true;
            }

            if ($item->hasChildren()) {
                if ($this->activateByRoute($route, $item->getChildren())) {
                    return true;
                }
            }
        }

        return false;
    }
}
