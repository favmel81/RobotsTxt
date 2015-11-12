<?php

namespace RobotsTxt;

/**
 * ordinary it is only User-agent group
 * Class Group
 * @package RobotsTxt
 */
class Group extends Item implements ChildInterface, ContainerInterface {

    const USER_AGENT_GROUP = 'User-agent';

    protected $isGroup = true;
    protected $items = array();
    protected $itemsMap = array();
    protected $itemsOrdered = array();

    public function __construct($name, $value = null, Document $parent = null) {
        parent::__construct();
        $this->name = $name;
        $this->value = $value;
        if($parent) {
            $this->setParent($parent);
        }

    }


    /**
     * makes this group actual for multiple user-agents
     * @param $value
     */
    public function addMultipleValue($value) {
        if(!is_array($this->value)) {
            $this->value = array($this->value);
        }

        if(!in_array($value, $this->value)) {
            $this->value[] = $value;
        }

    }

    /**
     * removes this group from it`s parent container
     */
    public function remove() {
        if($this->parent) {
            $this->parent->removeItem($this);
            $this->parent = null;
        }
    }

    /**
     * adds child item to this container
     * @param ChildInterface $item
     * @param bool           $top
     */
    public function addItem(ChildInterface $item, $top = false) {
        $itemName = strtolower($item->getName());
        $itemId = $item->getId();
        if(!isset($this->items[$itemId])) {
            if(!isset($this->itemsMap[$itemName])) {
                $this->itemsMap[$itemName] = array();
            }
            $this->itemsMap[$itemName][$itemId] = $itemId; //true;
            $this->items[$itemId] = $item;
            if($top) {
                array_unshift($this->itemsOrdered, $itemId);
            } else {
                $this->itemsOrdered[] = $itemId;
            }
        }
    }

    /**
     * removes child item from this container
     * @param ChildInterface $item
     */
    public function removeItem(ChildInterface $item) {
        $itemName = strtolower($item->getName());
        $itemId = $item->getId();

        if(isset($this->items[$itemId])) {
            unset($this->itemsMap[$itemName][$itemId]);
            unset($this->items[$itemId]);
            if(!sizeof($this->itemsMap[$itemName])) {
                unset($this->itemsMap[$itemName]);
            }
            $this->itemsOrdered = array_diff($this->itemsOrdered, array($itemId));
        }
    }

    /**
     * checks children existence
     * @return bool
     */
    public function hasItems() {
        return (boolean)sizeof($this->items);
    }

    /**
     * @return array
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * @return array
     */
    public function getOrderedItemsIds() {
        return $this->itemsOrdered;
    }

    /**
     * search Host command in this group (with particular value)
     * @param null $value
     * @return array|null
     */
    public function findHostElement($value = null) {
        return $this->findElement(Element::HOST_ELEMENT, $value);
    }

    /**
     * search Allow command in this group (with particular value)
     * @param null $value
     * @return array|null
     */
    public function findAllowElement($value = null) {
        return $this->findElement(Element::ALLOW_ELEMENT, $value);
    }

    /**
     * search Disallow command in this group (with particular value)
     * @param null $value
     * @return array|null
     */
    public function findDisallowElement($value = null) {
        return $this->findElement(Element::DISALLOW_ELEMENT, $value);
    }


    /**
     * search commands with particular value
     * @param      $elementName
     * @param null $searchValue
     * @return array|null
     */
    public function findElement($elementName, $searchValue = null)
    {
        $elementName = strtolower($elementName);
        if (!isset($this->itemsMap[$elementName])) {
            return null;
        }

        $elements = array();

        if ($searchValue === null) {
            foreach ($this->itemsMap[$elementName] as $id) {
                $elements[] = $this->items[$id];
            }
            return $elements;
        }

        $searchValue = strtolower($searchValue);

        /**
         * search by value
         */

        foreach ($this->itemsMap[$elementName] as $id) {
            /**
             * @var $element Item
             */
            $element = $this->items[$id];
            $value = $element->getValue();

            if ((!is_array($value) && strtolower($value) == $searchValue) || (is_array($value) && $this->inArrayI(
                        $searchValue,
                        $value
                    ))
            ) {
                $elements[] = $element;
            }
        }

        return $elements ? $elements : null;

    }

    public function findElementByValue($searchValue) {
        $elements = array();
        $searchValue = strtolower($searchValue);
        if($this->hasItems()) {
            foreach($this->items as $element) {
                /**
                 * $var $element Item
                 */
                if(strtolower($element->getValue()) == $searchValue) {
                    $elements[] = $element;
                }
            }
        }
        return $elements?$elements:null;
    }

    public function createElement($name, $values, $top = false)
    {
        if(!is_array($values)) {
            $values = array($values);
        }
        $elements = array();
        foreach($values as $value) {
            $elements[] = new Element($name, $value, $this, $top);
        }

        if(sizeof($elements) == 1) {
            $elements = $elements[0];
        }
        return $elements;
    }

    /**
     * creates Allow command
     * @param string $value
     * @param bool   $top
     * @return $this
     */
    public function allow($value = '', $top = false) {
        $this->createElement(Element::ALLOW_ELEMENT, $value, $top);
        return $this;
    }

    /**
     * creates Disallow command
     * @param string $value
     * @param bool   $top
     * @return $this
     */
    public function disallow($value = '', $top = false) {
        $this->createElement(Element::DISALLOW_ELEMENT, $value, $top);
        return $this;
    }

    /**
     * creates Host command
     * @param string $value
     * @param bool   $top
     * @return $this
     */
    public function host($value = '', $top = false) {
        $this->createElement(Element::HOST_ELEMENT, $value, $top);
        return $this;
    }



} 