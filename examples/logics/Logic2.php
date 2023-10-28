<?php

    namespace Coco\examples\logics;

    use Coco\processManager\CallableLogic;
    use Coco\processManager\ProcessRegistry;

    class Logic2
    {
        public function run(ProcessRegistry $registry , CallableLogic $logic)
        {
            echo 'logicName : ' . $logic->getName();
            echo PHP_EOL;

            //return false;
        }
    }
