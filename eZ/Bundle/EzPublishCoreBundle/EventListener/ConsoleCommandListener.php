<?php

/**
 * File containing the ConsoleCommandListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Exception\InvalidSiteAccessException;
use eZ\Publish\Core\MVC\Symfony\Event\ConsoleInitEvent;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * ConsoleCommandListener event listener.
 */
class ConsoleCommandListener implements EventSubscriberInterface, SiteAccessAware
{
    /** @var string */
    private $defaultSiteAccessName;

    /** @var array */
    private $siteAccessList;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @var
     */
    private $siteAccess;

    /** @var bool */
    private $debug;

    /**
     * ConsoleCommandListener constructor.
     */
    public function __construct($defaultSiteAccessName, array $siteAccessList, EventDispatcherInterface $eventDispatcher, $debug = false)
    {
        $this->defaultSiteAccessName = $defaultSiteAccessName;
        $this->siteAccessList = $siteAccessList;
        $this->eventDispatcher = $eventDispatcher;
        $this->debug = $debug;
    }

    public static function getSubscribedEvents()
    {
        return [
            MVCEvents::CONSOLE_INIT => [
                ['onConsoleCommand', -1],
            ],
        ];
    }

    public function onConsoleCommand(ConsoleInitEvent $event)
    {
        $this->siteAccess->name = $event->getInput()->getParameterOption('--siteaccess', $this->defaultSiteAccessName);
        $this->siteAccess->matchingType = 'cli';

        if (!in_array($this->siteAccess->name, $this->siteAccessList)) {
            throw new InvalidSiteAccessException($this->siteAccess->name, $this->siteAccessList, $this->siteAccess->matchingType, $this->debug);
        }

        $this->eventDispatcher->dispatch(new ScopeChangeEvent($this->siteAccess), MVCEvents::CONFIG_SCOPE_CHANGE);
    }

    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        $this->siteAccess = $siteAccess;
    }

    public function setDebug($debug = false)
    {
        $this->debug = $debug;
    }
}
