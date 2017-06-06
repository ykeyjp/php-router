<?php
namespace ykey\router;

/**
 * Trait StrategyTrait
 *
 * @package ykey\router
 */
trait StrategyTrait
{
    /**
     * @var null|StrategyInterface
     */
    private $strategy;

    /**
     * @return null|StrategyInterface
     */
    public function getStrategy(): ?StrategyInterface
    {
        return $this->strategy;
    }

    /**
     * @param StrategyInterface $strategy
     *
     * @return StrategyTrait|self
     */
    public function setStrategy(StrategyInterface $strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }
}
