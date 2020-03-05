<?php

namespace Slorem\ConditionEvaluator;

/**
 * Token class
 */
class Token
{
    const TYPE_SHARP         = "end_sharp";
    const TYPE_COMPARISON    = "comparison";
    const TYPE_LEFT_BRACKET  = "left_bracket";
    const TYPE_RIGHT_BRACKET = "right_bracket";
    const TYPE_AND           = "logical_and";
    const TYPE_OR            = "logical_or";
    const TYPE_NOT           = "logical_not";
    const TYPE_EXPRESSION    = "expression";
    
    
    /**
     * @var string
     */
    protected $type;
    /**
     * @var mixed
     */
    protected $value;
    
    
    /**
     * Create new token
     * 
     * @param   string  $type
     * @param   mixed   $value
     */
    public function __construct($type, $value = null)
    {
        $this->type = $type;
        $this->value = $value;
    }
    
    
    /**
     * Get type
     * 
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     * 
     * @param   string  $type
     * @return  $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get value
     * 
     * @return  mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value
     * @param   mixed   $value
     * @return  $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}
