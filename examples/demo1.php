<?php

    use Coco\examples\logics\Logic1;
    use Coco\examples\logics\Logic2;
    use Coco\processManager\CallableLogic;
    use Coco\processManager\ProcessRegistry;

    require '../vendor/autoload.php';

    $registry = new ProcessRegistry();

    $registry->testMsg         = [];
    $registry->testMsg['data'] = 123;

    $registry->setIsDebug(!true);

    $registry->setOnStart(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('setOnStart-debugMsg');
        $logic->setMsg('setOnStart-msg');
        echo 'logicName : ' . $logic->getName();

        //$registry->testMsg = 'setOnStart-value';

        echo PHP_EOL;

        //return false;
    }, 'setOnStart'));

    $registry->setOnDone(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('setOnDone-debugMsg');
        $logic->setMsg('setOnDone-msg');
        echo 'logicName : ' . $logic->getName();
        echo PHP_EOL;
        //return false;
    }, 'setOnDone'));

    $registry->setOnCatch(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('setOnCatch-debugMsg');
        $logic->setMsg('setOnCatch-msg');
        echo 'logicName : ' . $logic->getName();
        echo PHP_EOL;

        //return false;
    }, 'setOnCatch'));

    $registry->setOnResultIsTrue(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('setOnResultIsTrue-debugMsg');
        $logic->setMsg('setOnResultIsTrue-msg');
        echo 'logicName : ' . $logic->getName();
        echo PHP_EOL;

        //return false;
    }, 'setOnResultIsTrue'));

    $registry->setOnResultIsFalse(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('setOnResultIsFalse-debugMsg');
        $logic->setMsg('setOnResultIsFalse-msg');
        echo 'logicName : ' . $logic->getName();
        echo PHP_EOL;

        //return false;
    }, 'setOnResultIsFalse'));

    $registry->apendLogic(new Logic1());

    $registry->apendLogic(CallableLogic::getIns([
        new Logic2(),
        'run',
    ], 'Logic2'));

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('apendLogic_1-debugMsg');
        $logic->setMsg('apendLogic_1-msg');
        echo 'logicName : ' . $logic->getName();
        echo PHP_EOL;

        //$registry->setLogicStatus('apendLogic_2_before' , false);

        //return false;
    }, 'apendLogic_1'));

    $registry->prependLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('prependLogic_1-debugMsg');
        $logic->setMsg('prependLogic_1-msg');
        echo 'logicName : ' . $logic->getName();
        echo PHP_EOL;

        //return false;
    }, 'prependLogic_1'));

    $registry->if(

        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('ifCondition_1-debugMsg');
            $logic->setMsg('ifCondition_1-msg');
            echo 'logicName : ' . $logic->getName();
            echo PHP_EOL;

            //return false;
        }, 'ifCondition_1'),

        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('if_1-debugMsg');
            $logic->setMsg('if_1-msg');
            echo 'logicName : ' . $logic->getName();
            echo PHP_EOL;

            $registry->prependLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
                $logic->setDebugMsg('prependLogic_if-debugMsg');
                $logic->setMsg('prependLogic_if-msg');
                echo 'logicName : ' . $logic->getName();
                echo PHP_EOL;

                //return false;
            }, 'prependLogic_if'));

            $registry->if(

                CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
                    $logic->setDebugMsg('ifCondition_1_1-debugMsg');
                    $logic->setMsg('ifCondition_1_1-msg');
                    echo 'logicName : ' . $logic->getName();
                    echo PHP_EOL;

                    //throw new Exception('ifCondition_1-Exception');

                    //return false;
                }, 'ifCondition_1_1'),

                CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
                    $logic->setDebugMsg('if_1_1-debugMsg');
                    $logic->setMsg('if_1_1-msg');
                    echo 'logicName : ' . $logic->getName();
                    echo PHP_EOL;

                    //return false;
                }, 'if_1_1'));

            //return false;
        }, 'if_1'),

        CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('else_1-debugMsg');
            $logic->setMsg('else_1-msg');
            echo 'logicName : ' . $logic->getName();
            echo PHP_EOL;

            $registry->prependLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
                $logic->setDebugMsg('prependLogic_else-debugMsg');
                $logic->setMsg('prependLogic_else-msg');
                echo 'logicName : ' . $logic->getName();
                echo PHP_EOL;

                //return false;
            }, 'prependLogic_else'));

            //return false;
        }, 'else_1')

    );

    $registry->apendLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('apendLogic_2-debugMsg');
        $logic->setMsg('apendLogic_2-msg');
        echo 'logicName : ' . $logic->getName();
        echo PHP_EOL;

        //return false;
    }, 'apendLogic_2'));

    $registry->replaceLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('replaceLogic_2-debugMsg');
        $logic->setMsg('replaceLogic_2-msg');
        echo 'logicName : replaced : ' . $logic->getName();

        echo ' - ' . $registry->a;

        echo PHP_EOL;

        //return false;
    }, 'apendLogic_2'));

    $registry->injectLogicBefore(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('apendLogic_2_before-debugMsg');
        $logic->setMsg('apendLogic_2_before-msg');
        echo 'logicName : apendLogic_2_before : ' . $logic->getName();
        echo PHP_EOL;

        //return false;
    }, 'apendLogic_2_before'), 'apendLogic_2');

    $registry->injectLogicAfter(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('apendLogic_2_after-debugMsg');
        $logic->setMsg('apendLogic_2_after-msg');
        echo 'logicName : apendLogic_2_after : ' . $logic->getName();
        echo PHP_EOL;

        //        return false;
    }, 'apendLogic_2_after'), 'apendLogic_2');

    $registry->prependLogic(CallableLogic::getIns(function(ProcessRegistry $registry, CallableLogic $logic) {
        $logic->setDebugMsg('prependLogic_2-debugMsg');
        $logic->setMsg('prependLogic_2-msg');
        echo 'logicName : ' . $logic->getName();
        echo PHP_EOL;

        //return false;
    }, 'prependLogic_2'));

    $registry->setLogicStatus('prependLogic_2', false);

    $registry->executeLogics();

    echo "-done";
    echo PHP_EOL;

    print_r($registry->testMsg);;;
    echo PHP_EOL;

    echo $registry->getResultMessage();
    echo PHP_EOL;
