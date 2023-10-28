<?php

    use Coco\processManager\CallableLogic;
    use Coco\processManager\ProcessRegistry;

    require '../vendor/autoload.php';

    $registry = new ProcessRegistry();

    $registry->if(

        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
            echo 'logicName : ' . $logic->getName();
            echo PHP_EOL;

            return true;
        }, 'ifCondition_1'),

        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
            echo 'logicName : ' . $logic->getName();
            echo PHP_EOL;

            $registry->if(

                CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
                    echo 'logicName : ' . $logic->getName();
                    echo PHP_EOL;

                    return true;
                }, 'ifCondition_1_1'),

                CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
                    echo 'logicName : ' . $logic->getName();
                    echo PHP_EOL;

                    $registry->if(

                        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
                            echo 'logicName : ' . $logic->getName();
                            echo PHP_EOL;

                            //return false;
                        }, 'ifCondition_1_1_1'),

                        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
                            echo 'logicName : ' . $logic->getName();
                            echo PHP_EOL;

                            //return false;
                        }, 'if_1_1_1'),

                        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
                            echo 'logicName : ' . $logic->getName();
                            echo PHP_EOL;

                            //return false;
                        }, 'else_1_1_1')

                    );
                    //return false;
                }, 'if_1_1'),

                CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
                    echo 'logicName : ' . $logic->getName();
                    echo PHP_EOL;

                    //return false;
                }, 'else_1_1'));

            //return false;
        }, 'if_1'),

        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
            echo 'logicName : ' . $logic->getName();
            echo PHP_EOL;

            //return false;
        }, 'else_1')

    );

    $registry->replaceLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        echo 'logicName : replaced : ' . $logic->getName();

        echo PHP_EOL;

        //return false;
    }, 'else_1_1_1'));

    $registry->executeLogics();