<?php

namespace Core;

class EventDispatcher
{
    private $listeners = [];
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register a listener for a given event.
     */
    public function listen($eventName, $listener)
    {
        $this->listeners[$eventName][] = $listener;
    }

    /**
     * Dispatch an event to all its registered listeners (observers)
     */
    public function dispatch($event)
    {
        $eventName = get_class($event);

        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $listener) {
                // Use the container to build the listener instance
                $listenerInstance = $this->container->resolve($listener);
                $listenerInstance->handle($event);
                // The listener can be a callable or a class instance with a handle method
                // if (is_callable($listener)) {
                //     $listener($event);
                // } else {
                //     // In a more complex setting, one might resolve this from the container
                //     $listenerInstance = new $listener();
                //     $listenerInstance->handle($event);
                //}
            }
        }
    }
}
