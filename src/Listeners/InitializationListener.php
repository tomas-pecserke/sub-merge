<?php

namespace App\Listeners;

use App\Configuration\Property;
use App\Repository\ConfigurationPropertyRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

#[AsEventListener]
class InitializationListener {
    private readonly ConfigurationPropertyRepository $configRepository;
    private readonly RouterInterface $router;

    public function __construct(ConfigurationPropertyRepository $configRepository, RouterInterface $router) {
        $this->configRepository = $configRepository;
        $this->router = $router;
    }

    public function __invoke(RequestEvent $event): void {
        if (
            !$event->isMainRequest() // don't do anything if it's not the main request
            || $this->configRepository->getBoolean(Property::Initialized->name)
            || str_starts_with('settings.', $event->getRequest()->get('route'))
        ) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->router->generate('settings.setup')));
        $event->stopPropagation();
    }
}
