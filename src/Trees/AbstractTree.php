<?php

namespace Kvelaro\PhpTrees\Trees;

class AbstractTree
{
    protected $tree;

    protected $initTreeData = [];

    protected $updateOnDuplicate = false;

    public function __construct()
    {
        $this->tree = $this->initTreeData;
    }

    public function getTree() {
        return $this->tree;
    }
}