<?php

namespace Slorem\ConditionEvaluator;

use Slorem\Utils\Stack;
use Slorem\ConditionEvaluator\Exception;

/**
 * Condition evaluator class
 */
class ConditionEvaluator
{
    /**
     * Evaluate condition
     * 
     * @param   string  $condition
     * @param   array   $data
     * @return  boolean
     */
    public function evaluate($condition, $data)
    {
        $value = null;
        $stack = new Stack();
        $currentToken = null;
        $top = null;
        
        $condition = trim($condition) . "###END###";
        
        $stack->push(array(
            'token' => new Token(Token::TYPE_SHARP),
            'status' => 0,
        ));
        
        $currentToken = $this->readToken($condition, $data);
        
        while ($stack->count() > 0) {
            $top = $stack->top();
            $status = $top['status'];
            
            switch ($status) {
                case 0:
                    switch ($currentToken->getType()) {
                        case Token::TYPE_SHARP: // OK
                            $stack->pop(); // #
                            $value = true;
                            break;
                        case Token::TYPE_COMPARISON: // s2
                            $stack->push(array(
                                'token' => $currentToken,
                                'status' => 2,
                            ));
                            $currentToken = $this->readToken($condition, $data);
                            break;
                        case Token::TYPE_NOT: // s3
                            $stack->push(array(
                                'token' => $currentToken,
                                'status' => 3,
                            ));
                            $currentToken = $this->readToken($condition, $data);
                            break;
                        case Token::TYPE_LEFT_BRACKET: // s4
                            $stack->push(array(
                                'token' => $currentToken,
                                'status' => 4,
                            ));
                            $currentToken = $this->readToken($condition, $data);
                            break;
                        default:
                            throw new Exception\InvalidTokenTypeException("Invalid token type '" . $currentToken->getType() . "' at status #" . $status);
                    }
                    break;
                case 1:
                    switch ($currentToken->getType()) {
                        case Token::TYPE_AND:
                        case Token::TYPE_OR: // s5
                            $stack->push(array(
                                'token' => $currentToken,
                                'status' => 5,
                            ));
                            $currentToken = $this->readToken($condition, $data);
                            break;
                        case Token::TYPE_SHARP: // OK
                            $exp = $stack->pop(); // expression
                            $expToken = $exp['token'];
                            $stack->pop(); // #
                            $value = $expToken->getValue();
                            break;
                        default:
                            throw new Exception\InvalidTokenTypeException("Invalid token type '" . $currentToken->getType() . "' at status #" . $status);
                    }
                    break;
                case 2:
                    switch ($currentToken->getType()) {
                        case Token::TYPE_AND:
                        case Token::TYPE_OR:
                        case Token::TYPE_RIGHT_BRACKET:
                        case Token::TYPE_SHARP: // r1: E -> c
                            $cmp = $stack->pop(); // comparison
                            $cmpToken = $cmp['token'];
                            $prev = $stack->top();
                            $prevStatus = $prev['status'];
                            $nextStatus = null;
                            
                            switch ($prevStatus) {
                                 case 0:
                                     $nextStatus = 1;
                                     break;
                                 case 3:
                                     $nextStatus = 6;
                                     break;
                                 case 4:
                                     $nextStatus = 7;
                                     break;
                                 case 5:
                                     $nextStatus = 8;
                                     break;
                                default:
                                    throw new Exception\InvalidStatusException("Invalid previous status #" . $prevStatus . " at status #" . $status);
                            }
                            
                            $stack->push(array(
                                'token' => new Token(Token::TYPE_EXPRESSION, $cmpToken->getValue()),
                                'status' => $nextStatus,
                            ));
                            break;
                        default:
                            throw new Exception\InvalidTokenTypeException("Invalid token type '" . $currentToken->getType() . "' at status #" . $status);
                    }
                    break;
                case 3:
                case 4:
                case 5:
                    switch ($currentToken->getType()) {
                        case Token::TYPE_COMPARISON: // s2
                            $stack->push(array(
                                'token' => $currentToken,
                                'status' => 2,
                            ));
                            $currentToken = $this->readToken($condition, $data);
                            break;
                        case Token::TYPE_NOT: // s3
                            $stack->push(array(
                                'token' => $currentToken,
                                'status' => 3,
                            ));
                            $currentToken = $this->readToken($condition, $data);
                            break;
                        case Token::TYPE_LEFT_BRACKET: // s4
                            $stack->push(array(
                                'token' => $currentToken,
                                'status' => 4,
                            ));
                            $currentToken = $this->readToken($condition, $data);
                            break;
                        default:
                            throw new Exception\InvalidTokenTypeException("Invalid token type '" . $currentToken->getType() . "' at status #" . $status);
                    }
                    break;
                case 6:
                    switch ($currentToken->getType()) {
                        case Token::TYPE_AND:
                        case Token::TYPE_OR:
                        case Token::TYPE_RIGHT_BRACKET:
                        case Token::TYPE_SHARP: // r2: E -> uE
                            $exp = $stack->pop(); // expression
                            $expToken = $exp['token'];
                            $stack->pop(); // not
                            $prev = $stack->top();
                            $prevStatus = $prev['status'];
                            $nextStatus = null;
                            
                            switch ($prevStatus) {
                                 case 0:
                                     $nextStatus = 1;
                                     break;
                                 case 3:
                                     $nextStatus = 6;
                                     break;
                                 case 4:
                                     $nextStatus = 7;
                                     break;
                                 case 5:
                                     $nextStatus = 8;
                                     break;
                                default:
                                    throw new Exception\InvalidStatusException("Invalid previous status #" . $prevStatus . " at status #" . $status);
                            }
                            
                            $stack->push(array(
                                'token' => new Token(Token::TYPE_EXPRESSION, !$expToken->getValue()),
                                'status' => $nextStatus,
                            ));
                            break;
                        default:
                            throw new Exception\InvalidTokenTypeException("Invalid token type '" . $currentToken->getType() . "' at status #" . $status);
                    }
                    break;
                case 7:
                    switch ($currentToken->getType()) {
                        case Token::TYPE_AND:
                        case Token::TYPE_OR: // s5
                            $stack->push(array(
                                'token' => $currentToken,
                                'status' => 5,
                            ));
                            $currentToken = $this->readToken($condition, $data);
                            break;
                        case Token::TYPE_RIGHT_BRACKET: // s9
                            $stack->push(array(
                                'token' => $currentToken,
                                'status' => 9,
                            ));
                            $currentToken = $this->readToken($condition, $data);
                            break;
                        default:
                            throw new Exception\InvalidTokenTypeException("Invalid token type '" . $currentToken->getType() . "' at status #" . $status);
                    }
                    break;
                case 8:
                    switch ($currentToken->getType()) {
                        case Token::TYPE_AND:
                        case Token::TYPE_OR:
                        case Token::TYPE_RIGHT_BRACKET:
                        case Token::TYPE_SHARP: // r3: E -> EbE
                            $rightExp = $stack->pop(); // right expression
                            $rightExpToken = $rightExp['token'];
                            $operator = $stack->pop(); // and/or
                            $operatorToken = $operator['token'];
                            $leftExp = $stack->pop(); // left expression
                            $leftExpToken = $leftExp['token'];
                            $prev = $stack->top();
                            $prevStatus = $prev['status'];
                            $nextStatus = null;
                            
                            if ($operatorToken->getType() === Token::TYPE_AND) { // and-* => evaluate and: r3
                                switch ($prevStatus) {
                                     case 0:
                                         $nextStatus = 1;
                                         break;
                                     case 3:
                                         $nextStatus = 6;
                                         break;
                                     case 4:
                                         $nextStatus = 7;
                                         break;
                                     case 5:
                                         $nextStatus = 8;
                                         break;
                                    default:
                                        throw new Exception\InvalidStatusException("Invalid previous status #" . $prevStatus . " at status #" . $status);
                                }

                                $stack->push(array(
                                    'token' => new Token(Token::TYPE_EXPRESSION, $leftExpToken->getValue() && $rightExpToken->getValue()),
                                    'status' => $nextStatus,
                                ));
                            } elseif ($currentToken->getType() === Token::TYPE_AND) { // or-and => read and: s5
                                $stack->push(array(
                                    'token' => $currentToken,
                                    'status' => 5,
                                ));
                                $currentToken = $this->readToken($condition, $data);
                            } else { // or-* => evaluate or: r3
                                switch ($prevStatus) {
                                     case 0:
                                         $nextStatus = 1;
                                         break;
                                     case 3:
                                         $nextStatus = 6;
                                         break;
                                     case 4:
                                         $nextStatus = 7;
                                         break;
                                     case 5:
                                         $nextStatus = 8;
                                         break;
                                    default:
                                        throw new Exception\InvalidStatusException("Invalid previous status #" . $prevStatus . " at status #" . $status);
                                }
                                
                                $stack->push(array(
                                    'token' => new Token(Token::TYPE_EXPRESSION, $leftExpToken->getValue() || $rightExpToken->getValue()),
                                    'status' => $nextStatus,
                                ));
                            }
                            break;
                        default:
                            throw new Exception\InvalidTokenTypeException("Invalid token type '" . $currentToken->getType() . "' at status #" . $status);
                    }
                    break;
                case 9:
                    switch ($currentToken->getType()) {
                        case Token::TYPE_AND:
                        case Token::TYPE_OR:
                        case Token::TYPE_RIGHT_BRACKET:
                        case Token::TYPE_SHARP: // r4: E -> (E)
                            $stack->pop(); // right bracket
                            $exp = $stack->pop(); // expression
                            $expToken = $exp['token'];
                            $stack->pop(); // left bracket
                            $prev = $stack->top();
                            $prevStatus = $prev['status'];
                            $nextStatus = null;
                            
                            switch ($prevStatus) {
                                 case 0:
                                     $nextStatus = 1;
                                     break;
                                 case 3:
                                     $nextStatus = 6;
                                     break;
                                 case 4:
                                     $nextStatus = 7;
                                     break;
                                 case 5:
                                     $nextStatus = 8;
                                     break;
                                default:
                                    throw new Exception\InvalidStatusException("Invalid previous status #" . $prevStatus . " at status #" . $status);
                            }
                            
                            $stack->push(array(
                                'token' => new Token(Token::TYPE_EXPRESSION, $expToken->getValue()),
                                'status' => $nextStatus,
                            ));
                            break;
                        default:
                            throw new Exception\InvalidTokenTypeException("Invalid token type '" . $currentToken->getType() . "' at status #" . $status);
                    }
                    break;
            }
        }
        
        return $value;
    }
    
    /**
     * Get next token
     * 
     * @param   string  $condition
     * @return  \Common\ConditionEvaluator\Token
     */
    protected function readToken(&$condition, $data)
    {
        if ($condition === null) {
            return null;
        }
        
        $regexp = "(". // 1: comparison start
                      "(?:". // left side start
                          "([\"][^\"]*[\"])". // 2: "string"
                          "|(['][^']*['])". // 3: 'string'
                          "|([a-z0-9_.\/:-]+(?:[(][)])?)". // 4: string, string()
                      ")". // left side end
                      "[\s]*". // white-space
                      "(". // 5: operator
                          "[!=]?[=]{1,2}".
                          "|[<>][=]?".
                          "|[<][>]".
                          "|is(?: not)?".
                          "|(?:not )?like".
                      ")". // 5: operator end
                      "[\s]*". // white-space
                      "(?:". // right side start
                          "([\"][^\"]*[\"])". // 6: "string"
                          "|(['][^']*['])". // 7: 'string'
                          "|([a-z0-9_.\/:-]+(?:[(][)])?)". // 8: string, string()
                      ")". // right side end
                  ")". // 1: comparison end
                  "|([(])". // 9: left bracket
                  "|([)])". // 10: right bracket
                  "|(and)". // 11: and
                  "|(or)". // 12: or
                  "|(not)". // 13: not
                  "|(###END###)"; // 14: end/#
        $matches = array();
        $type = null;
        $value = null;
        
        if (!preg_match("/^[\s]*(?:" . $regexp . ")/iu", $condition, $matches)) {
            throw new Exception\InvalidConditionException("Invalid condition");
        }

        if (!empty($matches[1])) {
            $type = Token::TYPE_COMPARISON;
            $left = null;
            $operator = $matches[5];
            $right = null;

            if (!empty($matches[2])){
                $left = substr($matches[2], 1, -1);
            } elseif (!empty($matches[3])) {
                $left = substr($matches[3], 1, -1);
            } elseif (substr($matches[4], -2) === "()") {
                switch (substr($matches[4], 0, -2)) {
                    case "today":
                        $left = date("Y-m-d");
                        break;
                    case "now":
                        $left = date("Y-m-d H:i:s");
                        break;
                    default:
                        throw new \Exception("Procedure does not exist...");
                }
            } elseif (is_numeric(substr($matches[4], 0, 1))) {
                $left = (strstr($matches[4], ".") !== false ? (float)$matches[4] : (int)$matches[4]);
            } elseif (array_key_exists($matches[4], $data)) {
                $left = $data[$matches[4]];
            }

            if (!empty($matches[6])){
                $right = substr($matches[6], 1, -1);
            } elseif (!empty($matches[7])) {
                $right = substr($matches[7], 1, -1);
            } elseif (substr($matches[8], -2) === "()") {
                switch (substr($matches[8], 0, -2)) {
                    case "today":
                        $right = date("Y-m-d");
                        break;
                    case "now":
                        $right = date("Y-m-d H:i:s");
                        break;
                    default:
                        throw new \Exception("Procedure does not exist...");
                }
            } elseif (is_numeric(substr($matches[8], 0, 1))) {
                $right = (strstr($matches[8], ".") !== false ? (float)$matches[8] : (int)$matches[8]);
            } elseif ($matches[8] === "null" || $matches[8] === "empty") {
                $right = $matches[8];
            } elseif (array_key_exists($matches[8], $data)) {
                $right = $data[$matches[8]];
            }

            $comparison = new Comparison($left, $operator, $right);
            $value = $comparison->evaluate();
        } elseif (!empty($matches[9])) {
            $type = Token::TYPE_LEFT_BRACKET;
        } elseif (!empty($matches[10])) {
            $type = Token::TYPE_RIGHT_BRACKET;
        } elseif (!empty($matches[11])) {
            $type = Token::TYPE_AND;
        } elseif (!empty($matches[12])) {
            $type = Token::TYPE_OR;
        } elseif (!empty($matches[13])) {
            $type = Token::TYPE_NOT;
        } elseif (!empty($matches[14])) {
            $type = Token::TYPE_SHARP;
        }

        $condition = preg_replace("/".preg_quote($matches[0], "/")."/u", "", $condition, 1);

        return new Token($type, $value);
    }
}
