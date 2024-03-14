<?php

    namespace Coco\processManager;

use Coco\magicAccess\MagicMethod;

abstract class LogicAbstract
{
    use MagicMethod;

    protected string             $name         = '';
    protected string             $msg          = '';
    protected string             $debugMsg     = '';
    protected bool               $isEnable     = true;
    private bool                 $result       = false;
    private bool                 $isInnerLogic = false;
    private null|ProcessRegistry $registry     = null;

    /**
     * @return null|bool
     */
    abstract public function exec(): ?bool;

    /**
     * @return bool
     */
    public function run(): bool
    {
        //执行闭包
        $result = call_user_func_array([$this, 'exec'], []);

        if ($result !== false) {
            $result = true;
        }

        $this->setResult($result);

        return $result;
    }

    /**
     * @return ProcessRegistry
     */
    public function getRegistry(): ProcessRegistry
    {
        return $this->registry;
    }

    /**
     * @param ProcessRegistry|null $registry
     *
     * @return $this
     */
    public function setRegistry(?ProcessRegistry $registry): static
    {
        $this->registry = $registry;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;

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
     * @param bool $result
     *
     * @return $this
     */
    public function setResult(bool $result): static
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->isEnable;
    }

    /**
     * @param bool $isEnable
     *
     * @return $this
     */
    public function setIsEnable(bool $isEnable): static
    {
        $this->isEnable = $isEnable;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }

    /**
     * @param string $msg
     *
     * @return $this
     */
    public function setMsg(string $msg): static
    {
        $this->msg = $msg;

        return $this;
    }

    /**
     * @return string
     */
    public function getDebugMsg(): string
    {
        return $this->debugMsg;
    }

    /**
     * @param string $debugMsg
     *
     * @return $this
     */
    public function setDebugMsg(string $debugMsg): static
    {
        $this->debugMsg = "[{$this->getName()}]:" . $debugMsg;

        return $this;
    }

    /**
     * @param bool $isInnerLogic
     *
     * @return $this
     */
    public function setIsInnerLogic(bool $isInnerLogic): static
    {
        $this->isInnerLogic = $isInnerLogic;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInnerLogic(): bool
    {
        return $this->isInnerLogic;
    }
}
