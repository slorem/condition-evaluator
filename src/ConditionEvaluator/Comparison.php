<?php

namespace Slorem\ConditionEvaluator;

/**
 * Comparison class
 */
class Comparison
{
    /**
     * @var mixed
     */
    protected $left;
    /**
     * @var string
     */
    protected $operator;
    /**
     * @var mixed
     */
    protected $right;
    
    
    /**
     * Create new comparison
     * 
     * @param   mixed   $left
     * @param   string  $operator
     * @param   mixed   $right
     */
    public function __construct($left, $operator, $right)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }
    
    
    /**
     * Get left
     * 
     * @return  mixed
     */
    public function getLeft()
    {
        return $this->left;
    }
    
    /**
     * Set left
     * 
     * @param   mixed   $left
     * @return  $this
     */
    public function setLeft($left)
    {
        $this->left = $left;
        return $this;
    }
    
    /**
     * Get operator
     * 
     * @return  string
     */
    public function getOperator()
    {
        return $this->operator;
    }
    
    /**
     * Set operator
     * 
     * @param   mixed   $operator
     * @return  $this
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }
    
    /**
     * Get right
     * 
     * @return  mixed
     */
    public function getRight()
    {
        return $this->right;
    }
    
    /**
     * Set right
     * 
     * @param   mixed   $right
     * @return  $this
     */
    public function setRight($right)
    {
        $this->right = $right;
        return $this;
    }
    
    /**
     * Evaluate comparison
     * 
     * @return  boolean
     */
    public function evaluate()
    {
        $value = false;
        
        switch ($this->operator) {
            case "=":
            case "==":
                $value = ($this->left == $this->right);
                break;
            case "===":
                $value = ($this->left === $this->right);
                break;
            case "!=":
            case "<>":
                $value = ($this->left != $this->right);
                break;
            case "!==":
                $value = ($this->left !== $this->right);
                break;
            case "<":
                $value = ($this->left < $this->right);
                break;
            case "<=":
                $value = ($this->left <= $this->right);
                break;
            case ">":
                $value = ($this->left > $this->right);
                break;
            case ">=":
                $value = ($this->left >= $this->right);
                break;
            case "is":
                if ($this->right === "null") {
                    $value = ($this->left === null);
                } elseif ($this->right === "empty") {
                    $value = ($this->left === "");
                }
                break;
            case "is not":
                if ($this->right === "null") {
                    $value = ($this->left !== null);
                } elseif ($this->right === "empty") {
                    $value = ($this->left !== "");
                }
                break;
            case "like":
                $value = (boolean)preg_match("/^" . str_replace(array("%", "_"), array("[\s\S]*", "[\s\S]"), preg_quote($this->right, "/")) . "$/ui", $this->left);
                break;
            case "not like":
                $value = (boolean)!preg_match("/^" . str_replace(array("%", "_"), array("[\s\S]*", "[\s\S]"), preg_quote($this->right, "/")) . "$/ui", $this->left);
                break;
        }
        
        return $value;
    }
}
