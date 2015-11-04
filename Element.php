<?php

namespace RobotsTxt;


class Element extends Item implements ChildInterface{

    const ALLOW_ELEMENT = 'Allow';
    const DISALLOW_ELEMENT = 'Disallow';
    const HOST_ELEMENT = 'Host';
    const SITEMAP_ELEMENT = 'Sitemap';

    protected $isGroup = false;

    public function __construct($name, $value = null, ContainerInterface $parent, $top = false) {
        parent::__construct();
        $this->name = $name;
        $this->value = $value;
        if($parent) {
            $this->setParent($parent, $top);
        }
    }



    public function remove() {
        if($this->parent) {
            $this->parent->removeItem($this);
            $this->parent = null;
        }
    }
    public function getValue() {
        return $this->value;
    }


} 