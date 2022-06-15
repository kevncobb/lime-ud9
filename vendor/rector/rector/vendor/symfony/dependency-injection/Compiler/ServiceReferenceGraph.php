<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler;

use RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Reference;
/**
 * This is a directed graph of your services.
 *
 * This information can be used by your compiler passes instead of collecting
 * it themselves which improves performance quite a lot.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * @final
 */
class ServiceReferenceGraph
{
    /**
     * @var ServiceReferenceGraphNode[]
     */
    private $nodes = [];
    public function hasNode(string $id) : bool
    {
        return isset($this->nodes[$id]);
    }
    /**
     * Gets a node by identifier.
     *
     * @throws InvalidArgumentException if no node matches the supplied identifier
     */
    public function getNode(string $id) : \RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraphNode
    {
        if (!isset($this->nodes[$id])) {
            throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('There is no node with id "%s".', $id));
        }
        return $this->nodes[$id];
    }
    /**
     * Returns all nodes.
     *
     * @return ServiceReferenceGraphNode[]
     */
    public function getNodes() : array
    {
        return $this->nodes;
    }
    /**
     * Clears all nodes.
     */
    public function clear()
    {
        foreach ($this->nodes as $node) {
            $node->clear();
        }
        $this->nodes = [];
    }
    /**
     * Connects 2 nodes together in the Graph.
     * @param mixed $sourceValue
     * @param mixed $destValue
     */
    public function connect(?string $sourceId, $sourceValue, ?string $destId, $destValue = null, \RectorPrefix20220418\Symfony\Component\DependencyInjection\Reference $reference = null, bool $lazy = \false, bool $weak = \false, bool $byConstructor = \false)
    {
        if (null === $sourceId || null === $destId) {
            return;
        }
        $sourceNode = $this->createNode($sourceId, $sourceValue);
        $destNode = $this->createNode($destId, $destValue);
        $edge = new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraphEdge($sourceNode, $destNode, $reference, $lazy, $weak, $byConstructor);
        $sourceNode->addOutEdge($edge);
        $destNode->addInEdge($edge);
    }
    /**
     * @param mixed $value
     */
    private function createNode(string $id, $value) : \RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraphNode
    {
        if (isset($this->nodes[$id]) && $this->nodes[$id]->getValue() === $value) {
            return $this->nodes[$id];
        }
        return $this->nodes[$id] = new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraphNode($id, $value);
    }
}
