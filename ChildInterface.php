<?php

namespace RobotsTxt;


interface ChildInterface
{
    public function getId();
    public function getName();
    public function getValue();
    public function isGroup();
    public function remove();
} 