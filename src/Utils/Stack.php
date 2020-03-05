<?php

namespace Slorem\Utils;

/**
 * Stack class
 */
class Stack
{
    /**
     * @var array
     */
    protected $data;
    /**
     * @var int 
     */
    protected $count;
    
    
    /**
     * Create new stack
     */
    public function __construct()
    {
        $this->data = array();
        $this->count = 0;
    }
    
    
    /**
     * Push item
     * 
     * @param   mixed   $item
     */
    public function push($item)
    {
        $this->data[$this->count++] = $item;
    }
    
    /**
     * Get top item
     * 
     * @return  mixed
     */
    public function top()
    {
        return ($this->count > 0 ? $this->data[$this->count - 1] : null);
    }
    
    /**
     * Pop top item
     * 
     * @return  mixed
     */
    public function pop()
    {
        if ($this->count > 0) {
            --$this->count;
            $top = $this->data[$this->count];
            unset($this->data[$this->count]);
            return $top;
        }
        
        return null;
    }
    
    /**
     * Get count/length
     * 
     * @return  int
     */
    public function count()
    {
        return $this->count;
    }
    
    /**
     * Convert to array
     * 
     * @return  array
     */
    public function toArray()
    {
        return $this->data;
    }
}
