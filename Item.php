<?php


namespace RobotsTxt;


abstract class Item implements ChildInterface {

    private static $idPool = 0;

    /**
     * @var ContainerInterface
     */
    protected $parent;
    protected $isGroup;
    protected $name;
    protected $value;
    protected $comment;


    public function __construct() {
        $this->id = ++self::$idPool;
    }


    public function setParent(ContainerInterface $parent, $top = false) {
        if(!$this->parent) {
            $this->parent = $parent;
            $parent->addItem($this, $top);
        }
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value = '') {
        $this->value = $value;
        return $this;
    }

    public function getComment() {
        return $this->comment;
    }

    public function setComment($comment) {
        $this->comment = $comment;
    }

    public function hasComment() {
        return (boolean)$this->comment;
    }



    public function isGroup() {
        return (boolean)$this->isGroup;
    }

} 