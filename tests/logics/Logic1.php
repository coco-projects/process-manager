<?php

    namespace Coco\Tests\logics;

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

        $registry->val[] = 7;

        return true;
    }
}
