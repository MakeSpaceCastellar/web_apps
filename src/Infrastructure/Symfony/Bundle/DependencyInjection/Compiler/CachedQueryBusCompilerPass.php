<?php

declare(strict_types=1);

namespace MakeSpace\Infrastructure\Symfony\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CachedQueryBusCompilerPass implements CompilerPassInterface
{
    const SERVICE_ID_WHERE_REGISTER_HANDLERS = 'makespace.infrastructure.cached_query_bus';
    private $tag;
    private $methodMapper;

    public function __construct($tag)
    {
        $this->tag          = $tag;
        $this->methodMapper = new CallableFirstParameterExtractor();
    }

    public function process(ContainerBuilder $container)
    {
        $queryBus          = $container->findDefinition(self::SERVICE_ID_WHERE_REGISTER_HANDLERS);
        $handlerServiceIds = array_keys($container->findTaggedServiceIds($this->tag));

        foreach ($handlerServiceIds as $id) {
            $queryHandler = $container->findDefinition($id);
            $this->registerHandler($queryHandler, $id, $queryBus);
        }
    }

    private function registerHandler(Definition $queryHandler, $handlerServiceId, Definition $queryBus)
    {
        $queryClass = $this->methodMapper->extract($queryHandler->getClass());
        $queryBus->addMethodCall('register', [$queryClass, new Reference($handlerServiceId)]);
    }
}
