<?php
/**
 * A modified MIT License (MIT)
 * Copyright © 2015
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so., subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * Neither the Software, nor any derivative product, shall be used to operate weapons,
 * military nuclear facilities, life support or other mission critical applications
 * where human life or property may be at stake or endangered.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace dazz\Silex\SimpleBus;

use Silex\Application;
use Silex\ServiceProviderInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use SimpleBus\Message\Bus\Middleware\FinishesHandlingMessageBeforeHandlingNext;
use SimpleBus\Message\Handler\Map\LazyLoadingMessageHandlerMap;
use SimpleBus\Message\Name\ClassBasedNameResolver;

/**
 * Class SimpleServiceBusProvider
 * @package dazz\Silex\SimpleBus
 */
class SimpleServiceBusProvider implements ServiceProviderInterface
{
    /**
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['simpleBus.commandBus'] = $app->share(
            function () {
                return new MessageBusSupportingMiddleware(
                    [
                        new FinishesHandlingMessageBeforeHandlingNext(),
                    ]
                );

            }
        );

        $app['simpleBus.commandHandler.locator'] = $app->protect(
            function ($serviceId) use ($app) {
                if ($app->offsetExists($serviceId)) {
                    return $app[$serviceId];
                }
                throw new \Exception($serviceId . ' to handle message could not be located.');
            }
        );

        $app['simpleBus.commandHandlers'] = $app->share(
            function () {
                return [
                    // example
                    // Fully\Qualified\Class\Name\Of\Command::class => 'command.handler_service_id'
                ];
            }
        );

        $app['simpleBus.commandHandler.resolver'] = $app->share(
            function () {
                return new ClassBasedNameResolver();
            }
        );


        $app['simpleBus.commandHandler.map'] = $app->share(
            function () use ($app) {
                return new LazyLoadingMessageHandlerMap(
                    $app['simpleBus.commandHandlers'],
                    $app['simpleBus.commandHandler.locator']
                );
            }
        );
    }

    /**
     * @param Application $app An Application instance
     */
    public function boot(Application $app)
    {
    }
}
