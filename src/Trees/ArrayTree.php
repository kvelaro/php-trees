<?php

namespace Kvelaro\TreeHelper\Trees;

class ArrayTree extends AbstractTree implements Treeble
{
    protected $initTreeData = [
        'item'     => [],
        'children' => [],
    ];

    private $iterator = [
        'position' => null,
        'node' => [],
        'parent' => []
    ];

    public function makeTree(array $arrayOfObjects, $id = 'id', $parent_id = 'parent_id')
    {
        foreach ($arrayOfObjects as $object) {
            $parent = isset($object[$parent_id]) ? $object[$parent_id] : [];
            if (empty($parent)) {
                $item = null;
            } else {
                $item[$id] = $parent;
            }
            $leaf = [
                'item'     => $item,
                'children' => [
                    [
                        'item'     => $object,
                        'children' => [],
                    ],
                ],
            ];
            $this->addLeaf($leaf, $id, $parent_id);
        }
        return $this;
    }

    public function appendNodes(array $arrayOfObjects, $id = 'id', $parent_id = 'parent_id')
    {
        $this->makeTree($arrayOfObjects, $id, $parent_id);
    }

    public function &getTree()
    {
       return $this->tree;
    }

    public function &addLeaf($leaf, $id = 'id', $parent_id = 'parent_id', &$incomingNode = null)
    {
        $parent     = isset($leaf['item'][$id]) ? $leaf['item'][$id] : null;
        $leafItemId = $leaf['children'][0]['item'][$id];

        $node = &$this->getNode($id, $leafItemId);
        if ($node) {
            $node['item'] = array_merge($node['item'], $leaf['children'][0]['item']);
        }
        $parentNode = false;
        if (!empty($parent)) {
            $parentNode = &$this->getNode($id, $parent);
            //if node does not exist then create it
            if (!$parentNode) {
                $parentLeaf = [
                    'item'     => [],
                    'children' => [
                        [
                            'item'     => $leaf['item'],
                            'children' => [],
                        ],
                    ],
                ];
                $parentNode = &$this->addLeaf($parentLeaf, $id, $parent_id);
            }
        }
        //try to find child node if it exists
        $childNode = &$node;
        //if parent and child exist, update child and move to newly created parent
        if ($parentNode && $childNode) {
            //move previously (parent) stored node to it's parent
            $parentNode['children'] = array_merge($parentNode['children'], [$childNode]);
            //mark to delete old(moved) one
            $childNode['deleted'] = true;
            //@todo we should return last element
            return $parentNode['children'];
        }
        //if parent exist, and child does not exist
        if ($parentNode && !$childNode) {
            $parentNode['children'] = array_merge($parentNode['children'], $leaf['children']);
            //@todo we should return last element
            return $parentNode['children'];
        }
        if (!$parentNode && !$childNode) {
            $this->getTree()['children'] = array_merge($this->getTree()['children'], $leaf['children']);
            return $this->getNode($id, $leafItemId);
        }
        //return tree itself
        return $this->getTree();
    }

    public function &getNode($fieldToSearch, $valueToSearch, $level = -1, &$incomingNode = null)
    {
        $found = false;
        if ($level == 0) {
            return $found;
        }
        if ($incomingNode == null) {
            $incomingNode = &$this->getTree()['children'];
        }
        foreach ($incomingNode as $nodeKey => &$node) {
            if (isset($node['deleted']) && $node['deleted'] == true) {
                continue;
            }
            if ($node['item'][$fieldToSearch] == $valueToSearch) {
                return $node;
            }
            if (empty($node['children']) == false && ($level > 0 || $level == -1)) {
                if ($level == -1) {
                    $result = &$this->getNode($fieldToSearch, $valueToSearch, $level, $node['children']);
                } else {
                    $result = &$this->getNode($fieldToSearch, $valueToSearch, $level - 1, $node['children']);
                }
                if ($result) {
                    return $result;
                }
            }
        }
        return $found;
    }

    public function isEmpty()
    {
        return count($this->getTree()['children']) < 0;
    }

    public function iterateDS($callback, $nodeToStart = null, $depth = 0)
    {
        if (empty($nodeToStart)) {
            $nodeToStart = $this->getTree();
            $nodeItem = $nodeToStart['item'];
            $nodeChildren = $nodeToStart['children'];
        }
        else {
            $nodeItem = $nodeToStart['item'];
            $nodeChildren = $nodeToStart['children'];
        }
        foreach ($nodeChildren as $childNode) {
            if (isset($childNode['deleted']) && $childNode['deleted']) {
                continue;
            }
            $callback($childNode, $nodeItem, $depth);
            if (!empty($childNode['children'])) {
                $this->iterateDS($callback, $childNode, $depth + 1);
            }
        }
    }
}