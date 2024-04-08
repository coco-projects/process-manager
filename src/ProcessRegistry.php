<?php

    namespace Coco\processManager;

    use Coco\magicAccess\MagicMethod;
    use Exception;

class ProcessRegistry
{
    use MagicMethod;

    private array $logicList = [];

    private array $beforeLogics = [];
    private array $afterLogics  = [];

    private array $topLogics = [];
    private array $endLogics = [];

    private array  $replaceLogicsMap    = [];
    private array  $invokedLogics       = [];
    private bool   $isDebug             = false;
    private bool   $result              = true;
    private string $resultMessage       = '';
    private int    $nameCount           = 0;
    private array  $logicStatusRegistry = [];


    private ?LogicAbstract $errorLogic      = null;
    private ?LogicAbstract $onStart         = null;
    private ?LogicAbstract $onDone          = null;
    private ?LogicAbstract $onCatch         = null;
    private ?LogicAbstract $onResultIsTrue  = null;
    private ?LogicAbstract $onResultIsFalse = null;

    const LOGIC_PREFIX = "LOGIC_";

    public function __construct(bool $isDebug = false)
    {
        $this->setIsDebug($isDebug);
    }

    /**
     * @return array
     */
    public function getLogicList(): array
    {
        return $this->logicList;
    }

    /**
     * @param LogicAbstract $logic
     *
     * @return $this
     */
    public function apendLogic(LogicAbstract $logic): static
    {
        $this->pushLogicToList($this->initLogic($logic));

        return $this;
    }

    /**
     * @param LogicAbstract $logic
     *
     * @return $this
     */
    public function prependLogic(LogicAbstract $logic): static
    {
        $this->unshiftLogicToList($this->initLogic($logic));

        return $this;
    }

    /**
     * @param array  $logics
     * @param string $logicName
     *
     * @return $this
     */
    public function injectLogicBatchBefore(array $logics, string $logicName): static
    {
        if (!isset($this->beforeLogics[$logicName])) {
            $this->beforeLogics[$logicName] = [];
        }

        foreach ($logics as $k => $v) {
            $this->beforeLogics[$logicName][] = $this->initLogic($v);
        }

        return $this;
    }

    /**
     * @param array  $logics
     * @param string $logicName
     *
     * @return $this
     */
    public function injectLogicBatchAfter(array $logics, string $logicName): static
    {
        if (!isset($this->afterLogics[$logicName])) {
            $this->afterLogics[$logicName] = [];
        }

        foreach ($logics as $k => $v) {
            $this->afterLogics[$logicName][] = $this->initLogic($v);
        }

        return $this;
    }

    /**
     * @param LogicAbstract      $condition
     * @param LogicAbstract      $ifCallback
     * @param LogicAbstract|null $elseCallback
     *
     * @return $this
     */
    public function if(LogicAbstract $condition, LogicAbstract $ifCallback, ?LogicAbstract $elseCallback = null): static
    {
        $condition  = $this->initLogic($condition);
        $ifCallback = $this->initLogic($ifCallback);
        ($elseCallback instanceof LogicAbstract) and ($elseCallback = $this->initLogic($elseCallback));

        $this->addInnerLogic(CallableLogic::getIns(function (ProcessRegistry $registry, CallableLogic $logic) use ($condition, $ifCallback, $elseCallback) {
            if ($condition->run() !== false) {
                $this->unshiftLogicToList($ifCallback);
            } else {
                if ($elseCallback instanceof LogicAbstract) {
                    $this->unshiftLogicToList($elseCallback);
                }
            }
        }));

        return $this;
    }

    /**
     * @param LogicAbstract $logic
     *
     * @return $this
     */
    public function replaceLogic(LogicAbstract $logic): static
    {
        $this->replaceLogicsMap[$logic->getName()] = $this->initLogic($logic);

        return $this;
    }


    /**
     * @param array $logics
     *
     * @return $this
     */
    public function injectLogicBatchTop(array $logics): static
    {
        foreach ($logics as $k => $v) {
            $this->topLogics[] = $this->initLogic($v);
        }

        return $this;
    }


    /**
     * @param array $logics
     *
     * @return $this
     */
    public function injectLogicBatchEnd(array $logics): static
    {
        foreach ($logics as $k => $v) {
            $this->endLogics[] = $this->initLogic($v);
        }

        return $this;
    }


    /**
     * @return bool
     */
    public function executeLogics(): bool
    {
        /**
         * @var null|LogicAbstract $logicToRun
         */
        $logicToRun = null;

        $this->integrateLogicList();

        try {
            if ($this->getOnStart() instanceof LogicAbstract) {
                $this->getOnStart()->run();
            }

            while ((function () use (&$logicToRun) {

                if ($this->totalLogics() < 1) {
                    return false;
                }

                $logicToRun = $this->shiftLogicFromList();
                $logicName  = $logicToRun->getName();

                if (isset($this->logicStatusRegistry[$logicName])) {
                    $logicToRun->setIsEnable(!!$this->logicStatusRegistry[$logicName]);
                }

                if (isset($this->replaceLogicsMap[$logicName])) {
                    $logicToRun = $this->replaceLogicsMap[$logicName];
                }

                $this->invokedLogics[$logicName] = $logicToRun;

                return ($this->getResult() !== false);
            })()) {
                if (!$logicToRun->isEnable()) {
                    continue;
                }

                if ($logicToRun->isInnerLogic()) {
                    $logicToRun->run();
                    continue;
                }

                $result = $logicToRun->run();

                $this->setResult($result);

                if ($result === false) {
                    $this->setErrorLogic($logicToRun);
                }

                $msg = $this->isDebug() ? $logicToRun->getDebugMsg() : $logicToRun->getMsg();
                $this->setResultMessage($msg);
            }

            if ($this->getOnDone() instanceof LogicAbstract) {
                $this->getOnDone()->run();
            }
        } catch (Exception $e) {
            $this->setErrorLogic($logicToRun);

            if ($this->isDebug()) {
                $msg = implode('', [
                    'Exception by:[' . $logicToRun->getName() . '],',
                    '[' . $e->getFile() .'('. $e->getLine() . ')],',
                    '[' . $e->getMessage() . ']',
                ]);
            } else {
                $msg = $logicToRun->getMsg();
            }

            $this->setResultMessage($msg);
            $this->setResult(false);

            if ($this->getOnCatch() instanceof LogicAbstract) {
                $this->getOnCatch()->run();
            }
        }

        if ($this->getResult() && $this->getOnResultIsTrue() instanceof LogicAbstract) {
            $this->getOnResultIsTrue()->run();
        }

        if (!$this->getResult() && $this->getOnResultIsFalse() instanceof LogicAbstract) {
            $this->getOnResultIsFalse()->run();
        }

        return $this->getResult();
    }

    /**
     * @return int
     */
    public function totalLogics(): int
    {
        return count($this->logicList);
    }


    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->isDebug;
    }

    /**
     * @param string $logicName
     * @param bool   $isEnable
     *
     * @return $this
     */
    public function setLogicStatus(string $logicName, bool $isEnable): static
    {
        $this->logicStatusRegistry[$logicName] = $isEnable;

        return $this;
    }

    /**
     * @param bool $isDebug
     *
     * @return $this
     */
    public function setIsDebug(bool $isDebug): static
    {
        $this->isDebug = $isDebug;

        return $this;
    }

    /**
     * @return bool
     */
    public function getResult(): bool
    {
        return $this->result;
    }

    /**
     * @return LogicAbstract|null
     */
    public function getErrorLogic(): ?LogicAbstract
    {
        return $this->errorLogic;
    }

    /**
     * @param LogicAbstract|null $errorLogic
     *
     * @return $this
     */
    private function setErrorLogic(?LogicAbstract $errorLogic): static
    {
        $this->errorLogic = $errorLogic;

        return $this;
    }

    /**
     * @return string
     */
    public function getResultMessage(): string
    {
        return $this->resultMessage;
    }

    /**
     * @param string $resultMessage
     *
     * @return $this
     */
    protected function setResultMessage(string $resultMessage): static
    {
        $this->resultMessage = $resultMessage;

        return $this;
    }

    /**
     * @return LogicAbstract|null
     */
    private function getOnStart(): ?LogicAbstract
    {
        return $this->onStart;
    }

    /**
     * @param LogicAbstract|null $onStart
     *
     * @return $this
     */
    public function setOnStart(?LogicAbstract $onStart): static
    {
        $this->onStart = $this->initLogic($onStart);

        return $this;
    }

    /**
     * @return LogicAbstract|null
     */
    private function getOnDone(): ?LogicAbstract
    {
        return $this->onDone;
    }

    /**
     * @param LogicAbstract|null $onDone
     *
     * @return $this
     */
    public function setOnDone(?LogicAbstract $onDone): static
    {
        $this->onDone = $this->initLogic($onDone);

        return $this;
    }

    /**
     * @return LogicAbstract|null
     */
    private function getOnCatch(): ?LogicAbstract
    {
        return $this->onCatch;
    }

    /**
     * @param LogicAbstract|null $onCatch
     *
     * @return $this
     */
    public function setOnCatch(?LogicAbstract $onCatch): static
    {
        $this->onCatch = $this->initLogic($onCatch);

        return $this;
    }

    /**
     * @return LogicAbstract|null
     */
    private function getOnResultIsTrue(): ?LogicAbstract
    {
        return $this->onResultIsTrue;
    }

    /**
     * @param LogicAbstract|null $onResultIsTrue
     *
     * @return $this
     */
    public function setOnResultIsTrue(?LogicAbstract $onResultIsTrue): static
    {
        $this->onResultIsTrue = $this->initLogic($onResultIsTrue);

        return $this;
    }

    /**
     * @return LogicAbstract|null
     */
    private function getOnResultIsFalse(): ?LogicAbstract
    {
        return $this->onResultIsFalse;
    }

    /**
     * @param LogicAbstract|null $onResultIsFalse
     *
     * @return $this
     */
    public function setOnResultIsFalse(?LogicAbstract $onResultIsFalse): static
    {
        $this->onResultIsFalse = $this->initLogic($onResultIsFalse);

        return $this;
    }

    /**
     * @return array
     */
    public function getInvokedLogics(): array
    {
        return $this->invokedLogics;
    }

    /**
     * @param LogicAbstract $logic
     *
     * @return $this
     */
    private function unshiftLogicToList(LogicAbstract $logic): static
    {
        $this->logicList                    = array_reverse($this->logicList, true);
        $this->logicList[$logic->getName()] = $logic;
        $this->logicList                    = array_reverse($this->logicList, true);

        return $this;
    }

    /**
     * @param LogicAbstract $logic
     *
     * @return $this
     */
    private function pushLogicToList(LogicAbstract $logic): static
    {
        $this->logicList[$logic->getName()] = $logic;

        return $this;
    }

    /**
     * @return LogicAbstract
     */
    private function popLogicFromList(): LogicAbstract
    {
        return array_pop($this->logicList);
    }

    /**
     * @return LogicAbstract
     */
    private function shiftLogicFromList(): LogicAbstract
    {
        return array_shift($this->logicList);
    }

    /**
     * @param LogicAbstract $logic
     *
     * @return LogicAbstract
     */
    private function initLogic(LogicAbstract $logic): LogicAbstract
    {
        if (!($logic->getName())) {
            $this->incrCount();
            $logic->setName(static::LOGIC_PREFIX . $this->getNameCount());
        }
        $logic->setRegistry($this);

        return $logic;
    }

    /**
     * @return $this
     */
    private function incrCount(): static
    {
        $this->nameCount++;

        return $this;
    }

    /**
     * @return void
     */
    private function integrateLogicList(): void
    {
        $t = [];
        if (isset($this->topLogics)) {
            foreach ($this->topLogics as $logicTop) {
                $t[$logicTop->getName()] = $logicTop;
            }
        }

        foreach ($this->logicList as $logicName => $logic) {
            if (isset($this->beforeLogics[$logicName])) {
                foreach ($this->beforeLogics[$logicName] as $logic_) {
                    $t[$logic_->getName()] = $logic_;
                }
            }

            $t[$logicName] = $logic;

            if (isset($this->afterLogics[$logicName])) {
                foreach ($this->afterLogics[$logicName] as $logic_) {
                    $t[$logic_->getName()] = $logic_;
                }
            }
        }

        if (isset($this->endLogics)) {
            foreach ($this->endLogics as $logicEnd) {
                $t[$logicEnd->getName()] = $logicEnd;
            }
        }

        $this->beforeLogics = [];
        $this->afterLogics  = [];
        $this->topLogics    = [];
        $this->endLogics    = [];
        $this->logicList    = $t;
    }

    /**
     * @return int
     */
    private function getNameCount(): int
    {
        return $this->nameCount;
    }

    /**
     * @param bool $result
     *
     * @return $this
     */
    private function setResult(bool $result): static
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @param LogicAbstract $logic
     *
     * @return $this
     */
    protected function addInnerLogic(LogicAbstract $logic): static
    {
        $logic = $this->initLogic($logic)->setIsInnerLogic(true);

        $this->pushLogicToList($logic);

        return $this;
    }
}
