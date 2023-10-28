<?php

    declare(strict_types = 1);

    namespace Coco\Tests\Unit;

    use Coco\Tests\logics\Logic1;
    use Coco\Tests\logics\Logic2;
    use Coco\processManager\CallableLogic;
    use Coco\processManager\ProcessRegistry;
    use PHPUnit\Framework\TestCase;

final class ProcessRegistryTest extends TestCase
{
    public function testA()
    {
        $registry = new ProcessRegistry();
        $registry->setIsDebug(true);

        $registry->val = [];

        $registry->setOnStart(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnStart-debugMsg');
            $logic->setMsg('setOnStart-msg');

            $registry->val[] = 1;

            //return false;
        }, 'setOnStart'));

        $registry->setOnDone(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnDone-debugMsg');
            $logic->setMsg('setOnDone-msg');

            $registry->val[] = 2;

            //return false;
        }, 'setOnDone'));

        $registry->setOnCatch(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnCatch-debugMsg');
            $logic->setMsg('setOnCatch-msg');

            $registry->val[] = 3;

            //return false;
        }, 'setOnCatch'));

        $registry->setOnResultIsTrue(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnResultIsTrue-debugMsg');
            $logic->setMsg('setOnResultIsTrue-msg');

            $registry->val[] = 4;

            //return false;
        }, 'setOnResultIsTrue'));

        $registry->setOnResultIsFalse(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnResultIsFalse-debugMsg');
            $logic->setMsg('setOnResultIsFalse-msg');

            $registry->val[] = 5;

            //return false;
        }, 'setOnResultIsFalse'));

        $registry->apendLogic(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('apendLogic_1-debugMsg');
            $logic->setMsg('apendLogic_1-msg');

            $registry->val[] = 6;

            //return false;
        }, 'Logic_1'));

        $registry->apendLogic(new Logic1());

        $registry->apendLogic(CallableLogic::getIns([
            new Logic2(),
            'run',
        ], 'Logic_2'));

        $registry->executeLogics();

        $expect = [
            1,
            6,
            7,
            8,
            2,
            4,
        ];
        $this->assertSame($expect, $registry->val);
    }

    public function testB()
    {
        $registry = new ProcessRegistry();

        $registry->setIsDebug(true);

        $registry->val = [];

        $registry->setOnStart(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnStart-debugMsg');
            $logic->setMsg('setOnStart-msg');

            $registry->val[] = 1;

            //return false;
        }, 'setOnStart'));

        $registry->setOnDone(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnDone-debugMsg');
            $logic->setMsg('setOnDone-msg');

            $registry->val[] = 2;

            //return false;
        }, 'setOnDone'));

        $registry->setOnCatch(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnCatch-debugMsg');
            $logic->setMsg('setOnCatch-msg');

            $registry->val[] = 3;

            //return false;
        }, 'setOnCatch'));

        $registry->setOnResultIsTrue(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnResultIsTrue-debugMsg');
            $logic->setMsg('setOnResultIsTrue-msg');

            $registry->val[] = 4;

            //return false;
        }, 'setOnResultIsTrue'));

        $registry->setOnResultIsFalse(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnResultIsFalse-debugMsg');
            $logic->setMsg('setOnResultIsFalse-msg');

            $registry->val[] = 5;

            //return false;
        }, 'setOnResultIsFalse'));

        $registry->apendLogic(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('apendLogic_1-debugMsg');
            $logic->setMsg('apendLogic_1-msg');

            $registry->val[] = 6;

            return false;
        }, 'Logic_1'));

        $registry->apendLogic(new Logic1());

        $registry->apendLogic(CallableLogic::getIns([
            new Logic2(),
            'run',
        ], 'Logic_2'));

        $registry->executeLogics();

        $expect = [
            1,
            6,
            2,
            5,
        ];
        $this->assertSame($expect, $registry->val);
    }

    public function testC()
    {
        $registry = new ProcessRegistry();

        $registry->setIsDebug(true);
        $registry->val = [];

        $registry->setOnStart(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnStart-debugMsg');
            $logic->setMsg('setOnStart-msg');

            $registry->val[] = 1;

            //return false;
        }, 'setOnStart'));

        $registry->setOnDone(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnDone-debugMsg');
            $logic->setMsg('setOnDone-msg');

            $registry->val[] = 2;

            //return false;
        }, 'setOnDone'));

        $registry->setOnCatch(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnCatch-debugMsg');
            $logic->setMsg('setOnCatch-msg');

            $registry->val[] = 3;

            //return false;
        }, 'setOnCatch'));

        $registry->setOnResultIsTrue(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnResultIsTrue-debugMsg');
            $logic->setMsg('setOnResultIsTrue-msg');

            $registry->val[] = 4;

            //return false;
        }, 'setOnResultIsTrue'));

        $registry->setOnResultIsFalse(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnResultIsFalse-debugMsg');
            $logic->setMsg('setOnResultIsFalse-msg');

            $registry->val[] = 5;

            //return false;
        }, 'setOnResultIsFalse'));

        $registry->apendLogic(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('apendLogic_1-debugMsg');
            $logic->setMsg('apendLogic_1-msg');

            $registry->val[] = 6;

            $registry->setLogicStatus('Logic_2', false);
            //                return false;
        }, 'Logic_1'));

        $registry->apendLogic(new Logic1());

        $registry->apendLogic(CallableLogic::getIns([
            new Logic2(),
            'run',
        ], 'Logic_2'));

        $registry->executeLogics();

        $expect = [
            1,
            6,
            7,
            2,
            4,
        ];
        $this->assertSame($expect, $registry->val);
    }

    public function testD()
    {
        $registry = new ProcessRegistry();

        $registry->setIsDebug(true);
        $registry->val = [];

        $registry->setOnStart(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnStart-debugMsg');
            $logic->setMsg('setOnStart-msg');

            $registry->val[] = 1;

            //return false;
        }, 'setOnStart'));

        $registry->setOnDone(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnDone-debugMsg');
            $logic->setMsg('setOnDone-msg');

            $registry->val[] = 2;

            //return false;
        }, 'setOnDone'));

        $registry->setOnCatch(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnCatch-debugMsg');
            $logic->setMsg('setOnCatch-msg');

            $registry->val[] = 3;

            //return false;
        }, 'setOnCatch'));

        $registry->setOnResultIsTrue(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnResultIsTrue-debugMsg');
            $logic->setMsg('setOnResultIsTrue-msg');

            $registry->val[] = 4;

            //return false;
        }, 'setOnResultIsTrue'));

        $registry->setOnResultIsFalse(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnResultIsFalse-debugMsg');
            $logic->setMsg('setOnResultIsFalse-msg');

            $registry->val[] = 5;

            //return false;
        }, 'setOnResultIsFalse'));

        $registry->apendLogic(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('apendLogic_1-debugMsg');
            $logic->setMsg('apendLogic_1-msg');

            $registry->val[] = 6;

            //return false;
        }, 'Logic_1'));

        $registry->apendLogic(new Logic1());

        $registry->apendLogic(CallableLogic::getIns([
            new Logic2(),
            'run',
        ], 'Logic_2'));

        $registry->replaceLogic(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('replaceLogic_2-debugMsg');
            $logic->setMsg('replaceLogic_2-msg');

            $registry->val[] = 9;
        }, 'Logic_1'));

        $registry->prependLogic(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('prependLogic_1-debugMsg');
            $logic->setMsg('prependLogic_1-msg');

            $registry->val[] = 10;

            //return false;
        }, 'prependLogic_1'));

        $registry->injectLogicBefore(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('apendLogic_2_before-debugMsg');
            $logic->setMsg('apendLogic_2_before-msg');

            $registry->val[] = 11;

            //return false;
        }, 'apendLogic_2_before'), 'Logic_1');

        $registry->injectLogicAfter(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('apendLogic_2_after-debugMsg');
            $logic->setMsg('apendLogic_2_after-msg');

            $registry->val[] = 12;

            //        return false;
        }, 'apendLogic_2_after'), 'Logic_1');

        $registry->executeLogics();

        $expect = [
            1,
            10,
            11,
            9,
            12,
            7,
            8,
            2,
            4,
        ];

        $this->assertSame($expect, $registry->val);
    }

    public function testE()
    {
        $registry      = new ProcessRegistry();
        $registry->val = [];

        $registry->setIsDebug(true);

        $registry->if(
            CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
                $registry->val[] = 'ifCondition_1';

                return true;
            }, 'ifCondition_1'),
            CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
                $registry->val[] = 'if_1';

                $registry->if(
                    CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
                        $registry->val[] = 'ifCondition_1_1';

                        return true;
                    }, 'ifCondition_1_1'),
                    CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
                        $registry->val[] = 'if_1_1';

                        $registry->if(
                            CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
                                $registry->val[] = 'ifCondition_1_1_1';

                                return false;
                            }, 'ifCondition_1_1_1'),
                            CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
                                $registry->val[] = 'if_1_1_1';

                                //return false;
                            }, 'if_1_1_1'),
                            CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
                                $registry->val[] = 'else_1_1_1';

                                //return false;
                            }, 'else_1_1_1')
                        );

                        //return false;
                    }, 'if_1_1'),
                    CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
                        $registry->val[] = 'else_1_1';

                        //return false;
                    },
                        'else_1_1')
                );

                //return false;
            }, 'if_1'),
            CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
                $registry->val[] = 'else_1';

                //return false;
            }, 'else_1')
        );

        $registry->replaceLogic(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $registry->val[] = 'replaced:else_1_1_1';

            //return false;
        }, 'else_1_1_1'));

        $registry->executeLogics();

        $expect = [
            'ifCondition_1',
            'if_1',
            'ifCondition_1_1',
            'if_1_1',
            'ifCondition_1_1_1',
            'replaced:else_1_1_1',
        ];

        $this->assertSame($expect, $registry->val);
    }

    public function testF()
    {
        $registry = new ProcessRegistry();
        $this->assertSame(null, $registry->val);
    }

    public function testG()
    {
        $registry = new ProcessRegistry();
        $registry->setIsDebug(false);

        $registry->val = 1;

        unset($registry->val);

        $this->assertSame(null, $registry->val);
    }


    public function testH()
    {
        $registry = new ProcessRegistry();
        $registry->setIsDebug(true);

        $registry->apendLogic(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('Logic_1-debugMsg');
            $logic->setMsg('Logic_1-msg');

            throw new \Exception('Logic_1-Exception');

            //return false;
        }, 'Logic_1'));


        $registry->setOnCatch(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) {
            $logic->setDebugMsg('setOnCatch-debugMsg');
            $logic->setMsg('setOnCatch-msg');

            //return false;
        }, 'setOnCatch'));

        $registry->executeLogics();
        $result = strpos($registry->getResultMessage(), 'Logic_1-Exception');

        $this->assertIsInt($result);
    }
}
