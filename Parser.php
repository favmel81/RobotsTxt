<?php

namespace RobotsTxt;


class Parser
{

    private $dictionaryGroupsNames = array(
        'user-agent'
    );

    private $isGroup = false;
    /**
     * @var Group
     */
    private $currentGroup;
    private $lastComment;

    public function parse($content, Document $document) {

        $content = explode("\n", $content);

        if (sizeof($content)) {
            foreach ($content as $rowNumber => $line) {
                $line     = trim($line);
                //skip bebore/after line comment
                if(strpos($line, '#') === 0) {
                    $this->lastComment = ltrim($line, '#');
                    continue;
                }

                $chunks   = explode(':', $line);

                if(sizeof($chunks) == 1 && $line != '') {
                    throw new \Exception('Error in line number '.(++$rowNumber) ."\n".$line);
                }

                $itemName = trim($chunks[0]);
                $itemNameLC = strtolower($itemName);
                array_shift($chunks);
                //remove inline comment
                $itemValue = explode('#', trim(implode(':', $chunks)));
                if(isset($itemValue[1])) {
                    $this->lastComment = $itemValue[1];
                }
                $itemValue = trim($itemValue[0]);




                if($line == '') {
                    $this->isGroup = $this->currentGroup = $this->lastComment = null;
                    continue;
                }


                if(in_array($itemNameLC, $this->dictionaryGroupsNames)) {
                    if($this->isGroup) {
                        $this->currentGroup->addMultipleValue($itemValue);
                    } else {
                        $this->isGroup = true;
                        $this->currentGroup = $document->createGroup($itemName, $itemValue);
                        if($this->lastComment) {
                            $this->currentGroup->setComment($this->lastComment);
                            $this->lastComment = null;
                        }
                    }
                    continue;
                }

                if($this->isGroup) {
                    $element = $this->currentGroup->createElement($itemName, $itemValue);
                    if($this->lastComment) {
                        $element->setComment($this->lastComment);
                        $this->lastComment = null;
                    }
                    continue;
                }

                $element = $document->createElement($itemName, $itemValue);
                if($this->lastComment) {
                    $element->setComment($this->lastComment);
                    $this->lastComment = null;
                }

            }


        }

//        var_dump($content);


//        die();


    }
} 