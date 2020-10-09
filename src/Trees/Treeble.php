<?php

namespace Kvelaro\PhpTrees\Trees;

interface Treeble {

    public function makeTree(array $arrayOfObjects, $id = 'id', $parent_id = 'parent_id');

    public function appendNodes(array $array, $id = 'id', $parent_id = 'parent_id');

    public function getTree();

    public function addLeaf($leaf, $id  = 'id', $parent_id = 'parent_id', &$incomingNode = null);

    public function getNode($fieldToSearch, $valueToSearch, $level = -1, &$incomingNode = null);

    public function isEmpty();

    public function iterateDS($nodeToStart, $callback, $depth = 0);

}