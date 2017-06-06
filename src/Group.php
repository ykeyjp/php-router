<?php
namespace ykey\router;

/**
 * Class Group
 *
 * @package ykey\router
 */
class Group
{
    use StrategyTrait {
        getStrategy as _getStrategy;
    }
    use MiddlewareTrait {
        getMiddlewares as _getMiddlewares;
    }
    /**
     * @var string
     */
    private $base;
    /**
     * @var null|Group
     */
    private $parent;

    /**
     * Group constructor.
     *
     * @param string     $base
     * @param null|Group $parent
     */
    public function __construct(string $base, ?Group $parent)
    {
        if (!is_null($parent)) {
            $base = $parent->getBase() . $base;
        }
        $this->base = $base;
        $this->parent = $parent;
    }

    /**
     * @return string
     */
    public function getBase(): string
    {
        return $this->base;
    }

    /**
     * @return null|StrategyInterface
     */
    public function getStrategy(): ?StrategyInterface
    {
        if ($this->strategy) {
            return $this->strategy;
        } elseif ($this->parent) {
            return $this->parent->getStrategy();
        }

        return null;
    }

    /**
     * @return callable[]
     */
    public function getMiddlewares(): array
    {
        return ($this->parent ? $this->parent->getMiddlewares() : []) + $this->middlewares;
    }
}
