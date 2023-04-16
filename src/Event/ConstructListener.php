<?php

/*
 * This file is part of Ratchet.
 *
 ** (c) 2016 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WyriHaximus\Ratchet\Event;

use Cake\Core\Configure;
use Cake\Event\EventListenerInterface;
use Cake\Event\EventManager;
use React\EventLoop\LoopInterface;
use Thruway\Authentication\AuthenticationManager;
use Thruway\Peer\Router;
use Thruway\Transport\RatchetTransportProvider;
use WyriHaximus\Ratchet\Security\AuthorizationManager;
use WyriHaximus\Ratchet\Security\JWTAuthProvider;
use WyriHaximus\Ratchet\Security\WampCraAuthProvider;
use WyriHaximus\Ratchet\Websocket\InternalClient;

final class ConstructListener implements EventListenerInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var LoopInterface
     */
    private $loop;

    private $authRealms = [];

    /**
     * @return array
     */
    public function implementedEvents(): array
    {
        return [
            ConstructEvent::EVENT => 'construct',
        ];
    }

    /**
     * @param ConstructEvent $event
     */
    public function construct(ConstructEvent $event)
    {
        $this->loop = $event->getLoop();
        $this->router = new Router($this->loop);

        $this->router->registerModule(new AuthenticationManager());

        foreach (Configure::read('WyriHaximus.Ratchet.realms') as $realm => $config) {
            $this->setUpRealm($realm, $config, $event->getEventManager());
        }
        $this->router->addTransportProvider(
            new RatchetTransportProvider(
                Configure::read('WyriHaximus.Ratchet.internal.address'),
                Configure::read('WyriHaximus.Ratchet.internal.port')
            )
        );

        if (count($this->authRealms) > 0) {
            $this->router->addInternalClient((new JWTAuthProvider($this->authRealms, $this->loop)));
        }

        $event->getEventManager()->dispatch(WebsocketStartEvent::create($this->loop));

        $this->router->start(false);
    }

    protected function setUpRealm($realm, array $config, EventManager $eventManager)
    {
        $internalClient = new InternalClient($realm, $this->loop);
        $internalClient->setEventManager($eventManager);
        $this->router->addInternalClient($internalClient);
        if (!\igorw\get_in($config, ['auth'], false)) {
            return;
        }

        $this->router->registerModule((new AuthorizationManager($realm, $this->loop))->setEventManager($eventManager));
        $this->authRealms[] = $realm;
    }
}