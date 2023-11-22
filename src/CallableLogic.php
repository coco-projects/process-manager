<?php

    namespace Coco\processManager;

class CallableLogic extends LogicAbstract
{
    /**
     * @var null|callable $callback
     */
    protected $callback = null;

    /**
     * @param callable $callback
     * @param string   $name
     * @param bool     $isEnable
     */
    public function __construct(callable $callback, string $name = '', bool $isEnable = true)
    {
        $this->callback = $callback;
        $this->setName($name);
        $this->setIsEnable($isEnable);
    }

    /**
     * @return null|bool
     */
    public function exec(): ?bool
    {
        return call_user_func_array($this->callback, [
            $this->getRegistry(),
            $this,
        ]);
    }

    /**
     * @return callable|null
     */
    public function getCallback(): ?callable
    {
        return $this->callback;
    }

    /**
     * @param callable $callback
     * @param string   $name
     * @param bool     $isEnable
     *
     * @return static
     */
    public static function getIns(callable $callback, string $name = '', bool $isEnable = true): static
    {
        return new static($callback, $name, $isEnable);
    }
}
