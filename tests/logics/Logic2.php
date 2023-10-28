<?php

    namespace Coco\Tests\logics;

    use Coco\processManager\CallableLogic;
    use Coco\processManager\ProcessRegistry;

class Logic2
{
    public function run(ProcessRegistry $registry, CallableLogic $logic)
    {

        $registry->val[] = 8;

        //return false;
    }
}
