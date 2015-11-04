<?php

namespace RobotsTxt;


interface ContainerInterface
{
    public function addItem(ChildInterface $item);
    public function removeItem(ChildInterface $item);
} 