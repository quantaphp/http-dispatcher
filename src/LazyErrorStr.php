<?php

declare(strict_types=1);

namespace Quanta\Http;

final class LazyErrorStr
{
    /**
     * @var string
     */
    const TPL = 'the value returned by the factory must implement interface %s, %s returned';

    /**
     * @var string
     */
    private string $expected;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string    $expected
     * @param mixed     $value
     */
    public function __construct(string $expected, $value)
    {
        $this->expected = $expected;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (!is_object($this->value)) {
            return sprintf(self::TPL, $this->expected, gettype($this->value));
        }

        $classname = get_class($this->value);

        if (strpos($classname, 'class@anonymous') === 0) {
            $classname = 'class@anonymous';
        }

        return sprintf(self::TPL, $this->expected, 'instance of ' . $classname);
    }
}
