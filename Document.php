<?php

namespace RobotsTxt;

/**
 * processing robots.txt structure
 * Class Document
 * @package RobotsTxt
 */
class Document implements ContainerInterface
{

    protected $items = array();
    protected $itemsMap = array();
    protected $groups = array();
    protected $groupsMap = array();


    public function __construct($content = null)
    {
        if ($content) {
            $this->loadFromString($content);
        }
    }

    /**
     * load structure from string
     * @param $content
     */
    public function loadFromString($content)
    {
        $parser = new Parser();
        $parser->parse($content, $this);
    }

    /**
     * create resulted plain text
     * @return string
     */
    public function render()
    {
        $content = array();

        if($this->groups) {
            /**
             * @var $group Group
             */
            foreach($this->groups as $group) {
                $groupValue = $group->getValue();
                $groupName = $group->getName();

                if($group->hasComment()) {
                    $content[] = '#'.$group->getComment();
                }

                if(is_array($groupValue)) {
                    foreach($groupValue as $value) {
                        $content[] = $groupName.': '.$value;
                    }
                } else {
                    $content[] = $groupName.': '.$groupValue;
                }


                if($group->hasItems()) {
                    /**
                     * @var Item $item
                     */
                    $items = $group->getItems();
                    foreach($group->getOrderedItemsIds() as $itemId) {
                        $item = $items[$itemId];
                        if($item->hasComment()) {
                            $content[] = '#'.$item->getComment();
                        }
                        $content[] = $item->getName().': '.$item->getValue();
                    }
                }
                $content[] = '';
            }
        }


        if($this->items) {

            /**
             * @var $item Item
             */
            foreach($this->items as $item) {
                if($item->hasComment()) {
                    $content[] = '#'.$item->getComment();
                }
                $content[] = $item->getName().': '.$item->getValue();
            }


        }

        return implode("\n", $content);

    }


    /**
     * adding child element
     * @param ChildInterface $item
     * @return bool|null
     */
    public function addItem(ChildInterface $item)
    {
        $itemId   = $item->getId();
        $itemName = strtolower($item->getName());

        if ($this->hasItem($item)) {
            return false;
        }

        if ($item->isGroup()) {
            $this->groups[$itemId] = $item;
            if (!isset($this->groupsMap[$itemName])) {
                $this->groupsMap[$itemName] = array();
            }
            $this->groupsMap[$itemName][$itemId] = $itemId; //true; //=$item;
        } else {
            $this->items[$itemId] = $item;
            if (!isset($this->itemsMap[$itemName])) {
                $this->itemsMap[$itemName] = array();
            }
            $this->itemsMap[$itemName][$itemId] = $itemId; //true; //=$item;
        }
    }

    /**
     * checks child existence
     * @param ChildInterface $item
     * @return bool
     */
    public function hasItem(ChildInterface $item)
    {
        if ($item->isGroup()) {
            return isset($this->groups[$item->getId()]);
        }
        return isset($this->items[$item->getId()]);
    }

    /**
     * remove child element from this container
     * @param ChildInterface $item
     */
    public function removeItem(ChildInterface $item)
    {
        if ($this->hasItem($item)) {
            $itemId = $item->getId();
            $itemName = strtolower($item->getName());

            if($item->isGroup()) {
                unset($this->groupsMap[$itemName][$itemId]);
                unset($this->groups[$itemId]);
                if(!sizeof($this->groupsMap[$itemName])) {
                    unset($this->groupsMap[$itemName]);
                }
            } else {
                    unset($this->itemsMap[$itemName][$itemId]);
                    unset($this->items[$itemId]);
                    if(!sizeof($this->itemsMap[$itemName])) {
                        unset($this->itemsMap[$itemName]);
                    }
            }
        }
    }

    /**
     * Search User-agent group by value
     * @param $userAgent
     * @return array|null
     */
    public function findUserAgentGroup($userAgent) {
        return $this->findGroup(Group::USER_AGENT_GROUP, $userAgent);
    }

    /**
     * search group with particular name (and value) over children groups
     * @param string $groupName
     * @param null   $searchValue
     * @return array|null
     */
    protected function findGroup($groupName = Group::USER_AGENT_GROUP, $searchValue = null)
    {
        $groupName = strtolower($groupName);
        if (!isset($this->groupsMap[$groupName])) {
            return null;
        }

        $groups = array();

        if ($searchValue === null) {
            foreach ($this->groupsMap[$groupName] as $id) {
                $groups[] = $this->groups[$id];
            }
            return $groups;
        }

        $searchValue = strtolower($searchValue);

        /**
         * search by value
         */

        foreach ($this->groupsMap[$groupName] as $id) {
            /**
             * @var $group Item
             */
            $group = $this->groups[$id];
            $value = $group->getValue();

            if ((!is_array($value) && strtolower($value) == $searchValue) || (is_array($value) && $this->inArrayI(
                        $searchValue,
                        $value
                    ))
            ) {
                $groups[] = $group;
            }
        }

        return $groups ? $groups : null;

    }

    /**
     * search item with particular name (and value) over children elements
     * @param      $itemName
     * @param null $searchValue
     * @return array|null
     */
    public function findItem($itemName, $searchValue = null)
    {
        $itemName = strtolower($itemName);

        if (!isset($this->itemsMap[$itemName])) {
            return null;
        }

        $items = array();

        if ($searchValue === null) {
            foreach ($this->itemsMap[$itemName] as $id) {
                $items[] = $this->items[$id];
            }
            return $items;
        }


        /**
         * search by value
         */

        foreach ($this->itemsMap[$itemName] as $id) {
            /**
             * @var $item Item
             */
            $item  = $this->items[$id];
            $value = $item->getValue();

            if ($value === $searchValue) {
                $items[] = $value;
            }
        }

        return $items ? $items : null;
    }

    /**
     * creates User-agent group
     * @param $userAgent
     * @return Group
     */
    public function createUserAgentGroup($userAgent) {
        return $this->createGroup(Group::USER_AGENT_GROUP, $userAgent);

    }

    /**
     * creates group with particular name and value
     * @param $name
     * @param $value
     * @return Group
     */
    public function createGroup($name, $value)
    {
        return new Group($name, $value, $this);
    }

    /**
     * creates element with particular name and value
     * @param $name
     * @param $value
     * @return Element
     */
    public function createElement($name, $value)
    {
        return new Element($name, $value, $this);
    }

    /**
     * case-insensitive analog of the php function  in_array
     * @param $needle
     * @param $haystack
     * @return bool
     */
    public function inArrayI($needle, &$haystack) {
        return in_array(strtolower($needle), array_map('strtolower', $haystack));
    }
} 