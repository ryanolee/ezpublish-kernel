<?php

/**
 * File containing the SiteAccessMatchListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router as SiteAccessRouter;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * kernel.request listener, triggers SiteAccess matching.
 * Should be triggered as early as possible.
 */
class SiteAccessMatchListener implements EventSubscriberInterface
{
    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\Router */
    protected $siteAccessRouter;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        SiteAccessRouter $siteAccessRouter,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->siteAccessRouter = $siteAccessRouter;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            // Should take place just after FragmentListener (priority 48) in order to get rebuilt request attributes in case of subrequest
            KernelEvents::REQUEST => ['onKernelRequest', 45],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // We have a serialized siteaccess object from a fragment (sub-request), we need to get it back.
        if ($request->attributes->has('serialized_siteaccess')) {
            $request->attributes->set(
                'siteaccess',
                unserialize($request->attributes->get('serialized_siteaccess'))
            );
            $request->attributes->remove('serialized_siteaccess');
        } elseif (!$request->attributes->has('siteaccess')) {
            // Get SiteAccess from original request if present ("_ez_original_request" attribute), or current request otherwise.
            // "_ez_original_request" attribute is present in the case of user context hash generation (aka "user hash request").
            $request->attributes->set(
                'siteaccess',
                $this->getSiteAccessFromRequest($request->attributes->get('_ez_original_request', $request))
            );
        }

        $siteaccess = $request->attributes->get('siteaccess');
        if ($siteaccess instanceof SiteAccess) {
            $siteAccessEvent = new PostSiteAccessMatchEvent($siteaccess, $request, $event->getRequestType());
            $this->eventDispatcher->dispatch($siteAccessEvent, MVCEvents::SITEACCESS);
        }
    }

    /**
     * @param Request $request
     *
     * @return SiteAccess
     */
    private function getSiteAccessFromRequest(Request $request)
    {
        return $this->siteAccessRouter->match(
            new SimplifiedRequest(
                [
                    'scheme' => $request->getScheme(),
                    'host' => $request->getHost(),
                    'port' => $request->getPort(),
                    'pathinfo' => $request->getPathInfo(),
                    'queryParams' => $request->query->all(),
                    'languages' => $request->getLanguages(),
                    'headers' => $request->headers->all(),
                ]
            )
        );
    }
}
