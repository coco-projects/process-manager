<?php

    namespace Coco\processManager;

trait BaseTrait
{
    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->$name ?? null;
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return void
     */
    public function __set(string $name, $value)
    {
        $this->$name = $value;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function __unset(string $name)
    {
        unset($this->$name);
    }
}
