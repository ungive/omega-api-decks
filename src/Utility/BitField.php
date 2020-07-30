<?php

namespace Utility;


class BitField implements \Countable
{
    private int $bits = 0;

    public function __construct(int $bits) { $this->bits = $bits; }

    public function get   (): int  { return $this->bits; }
    public function clear (): void { $this->bits = 0; }

    public function set    (int $bits): void { $this->bits  =  $bits; }
    public function add    (int $bits): void { $this->bits |=  $bits; }
    public function remove (int $bits): void { $this->bits &= ~$bits; }

    public function has($bits): bool { return ($this->bits & $bits) === $bits; }
    public function any():      bool { return $this->bits !== 0; }

    public function count(): int
    {
        return substr_count(decbin($this->bits), '1');
    }
}
