<?php

    namespace Coco\examples\logics;

    use Coco\processManager\LogicAbstract;

    class Logic1 extends LogicAbstract
    {
        protected string $name     = 'LogicClassTest';
        protected string $msg      = 'LogicClassTest-debugMsg';
        protected string $debugMsg = 'LogicClassTest-debugMsg';
        protected bool   $isEnable = true;

        public function exec(): ?bool
        {
            $registry = $this->getRegistry();

            $registry->setResultMessage('Logic1 return msg');

            echo 'logicName : ' . $this->getName();
            echo PHP_EOL;

            return true;
        }
    }
