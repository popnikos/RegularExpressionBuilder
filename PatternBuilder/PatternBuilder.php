<?php

namespace Popnikos\RegularExpressionBuilder\PatternBuilder;

/**
 * Class PatternBuilder helps to build complex regular expression
 * used in preg_... functions as pattern argument in an easy way
 *
 * @author popnikos
 */
class PatternBuilder
{
    const PCRE_CASELESS = 'i';
    const PCRE_MULTILINE = 'm';
    const PCRE_DOTALL = 's';
    const PCRE_EXTENDED = 'x';
    const PCRE_ANCHORED = 'A';
    const PCRE_DOLLAR_ENDONLY = 'D';
    const PCRE_UNGREEDY = 'U';
    const PCRE_EXTRA = 'X';
    const PCRE_INFO_JCHANGED = 'J';
    const PCRE_UTF8 = 'u';
    
    private $parent;
    
    private $fragments=[];
    
    private $group=false;
    
    private $capture=false;
    
    private $options=[];
    
    public function __construct(PatternBuilder $parent=null) 
    {
        $this->setParent($parent);
    }
    
    /**
     * 
     * @return PatternBuilder
     */
    public function getParent() {
        return $this->parent;
    }

    public function setParent($parent) {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Whether pattern has a parent pattern (means 'is Subpattern')
     * @return boolean
     */
    public function hasParent()
    {
        return isset($this->parent) && $this->parent instanceof self;
    }
    
    /**
     * Whether or not pattern is a subpattern
     * @see PatternBuilder::hasParent
     * @return boolean
     */
    public function isSubpattern()
    {
        return $this->hasParent();
    }
    
    /**
     * Array of expressions (as string or Pattern)
     * @return array
     */
    public function getFragments() {
        return $this->fragments;
    }

    /**
     * 
     * @param array $fragments Array of mixed string / Pattern values
     * @return \Popnikos\RegularExpressionBuilder\PatternBuilder
     */
    public function setFragments($fragments) 
    {
        $this->fragments = $fragments;
        return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getGroup() {
        return $this->group;
    }

    public function getCapture() {
        return $this->capture;
    }

    /**
     * Declares the PatternBuilder as a subpattern
     * @param boolean $group
     * @return \Popnikos\RegularExpressionBuilder\PatternBuilder\PatternBuilder
     */
    public function setGroup($group) {
        $this->group = $group;
        return $this;
    }

    public function setCapture($capture) {
        $this->capture = $capture;
        return $this;
    }

    /**
     * 
     * @return string
     */
    private function escape($expression)
    {
        return preg_quote($expression, '/');
    }
    
    /**
     * 
     * @return PatternBuilder
     */
    public function add($expression, $escape=false)
    {
        if ($escape) {
            $expression = $this->escape($expression);
        }
        $this->fragments[]=$expression;
        return $this;
    }
    
    /**
     * 
     * @return PatternBuilder
     */
    public function any()
    {
        return $this->add('.');
    }
    
    /**
     * 
     * @return PatternBuilder
     */
    public function something()
    {
        return $this->add('.*');
    }
    
    /**
     * 
     * @return PatternBuilder
     */
    public function anyDigit()
    {
        return $this->add('\d');
    }
    
    /**
     * 
     * @return PatternBuilder
     */
    public function zeroOrMore() 
    {
        return $this->add('*');
    }
    
    /**
     * 
     * @return PatternBuilder
     */
    public function oneOrMore()
    {
        return $this->add('+');
    }
    
    /**
     * 
     * @return PatternBuilder
     */
    public function & startCapture()
    {
        $subpattern = (new PatternBuilder($this))->setGroup(true)->setCapture(true);
        $this->add($subpattern);
        return $subpattern;
    }
    
    /**
     * 
     * @return PatternBuilder
     */
    public function endCapture()
    {
        if( $this->getCapture() ) {
            return $this->end();
        }
        trigger_error('You cannot end capture that has not started yet!', E_USER_WARNING);
        return $this;
    }
    
    /**
     * Add a subpattern (no capture)
     * @return \Popnikos\RegularExpressionBuilder\PatternBuilder\PatternBuilder
     */
    public function & subPattern()
    {
        $subpattern = (new PatternBuilder($this))->setGroup(true);
        $this->add($subpattern);
        return $subpattern;
    }
    
    public function end()
    {
        if( $this->hasParent() ) {
            return $this->getParent();
        }
        trigger_error('You cannot end subpattern that has not started yet!', E_USER_WARNING);
        return $this;
    }
    /**
     * Add repetition predicate on last expression added
     * Force using 0 as minimum occurs if $from is not set or other than a valid integer
     * @param int $from
     * @param int $to
     * @return \Popnikos\RegularExpressionBuilder\PatternBuilder
     */
    public function repeated($from=null,$to=null)
    {
        if( !empty($this->fragments) && (isset($from) || isset($to))) {
            $from = intval($from);
            $to = isset($to)?intval($to):'';
            return $this->add("{{$from},{$to}}");
        }
        trigger_error(__METHOD__ . " require one parameter.", E_USER_WARNING);
        return $this;
    }
    
    /**
     * 
     * @return PatternBuilder
     */
    public function ponctuation()
    {
        return $this->add('\p{P}');
    }
    
    /**
     * 
     * @return PatternBuilder
     */
    public function symbol()
    {
        return $this->add('\p{S}');
    }
    
    /**
     * @return PatternBuilder
     */
    public function whitespace()
    {
        return $this->add('\p{Z}');
    }
    
    /**
     * @return PatternBuilder
     */
    public function currency()
    {
        return $this->add('\p{Sc}');
    }
    
    
    /**
     * 
     * @param string
     * @return PatternBuilder
     */
    public function contains($expression)
    {
        return $this->add($expression,true);
    }
    
    /**
     * 
     * @param string $expression
     */
    public function startWith($expression)
    {
        if( count($this->getFragments())<1 ) {
            $this->add('^')->add($expression, true);
        } elseif( '^' !== $this->fragments[0] ) { 
            array_unshift($this->fragments, '^'.$this->escape($expression));
        }
        return $this;
    }
    
    /**
     * Add an alternative into a group/subpattern (with or without capture)
     * @param string|PatternBuilder $expression
     * @return \Popnikos\RegularExpressionBuilder\PatternBuilder\PatternBuilder
     */
    public function orExp($expression)
    {
        if( $this->getGroup() ) {
            if( !empty($this->fragments)) {
                $this->add('|');
            }
            $this->add($expression);
            return $this;
        } 
    }
    
    /**
     * @param string $expression
     */
    public function endsWith($expression)
    {
        return $this->add($expression, true)->add('$');
    }
    
    /**
     * Add option to pattern (after checking option is not already set)
     * @link http://php.net/manual/en/reference.pcre.pattern.modifiers.php PHP Manual
     * @param string $option
     */
    private function addOption($option) {
        if( !in_array($option, $this->options))
        {
            $this->options[]= $option;
        }
        return $this;
    }
    
    /**
     * Add the caseless PCRE Option
     * @return PatternBuilder
     */
    public function caseless()
    {
        return $this->addOption(self::PCRE_CASELESS);
    }
    
    /**
     * Add the multiline PCRE Option
     * @return \Popnikos\RegularExpressionBuilder\PatternBuilder\PatternBuilder
     */
    public function multiline()
    {
        return $this->addOption(self::PCRE_MULTILINE);
    }
    
    /**
     * Add the Ungreedy PCRE Option
     * @return \Popnikos\RegularExpressionBuilder\PatternBuilder\PatternBuilder
     */
    public function ungreedy()
    {
        return $this->addOption(self::PCRE_UNGREEDY);
    }
    
    /**
     * Add a lookahead positive assertion
     * @param string $expression The main expression
     * @param string $assertion The Assertion expression
     * @return type
     */
    public function lookahead($expression, $assertion)
    {
        return $this->add($expression, true)->add('(?=')->add($assertion)->add(')');
    }
    
    /**
     * Negative lookahead assertion
     * @param string $expression
     * @param string $assertion
     * @return PatternBuilder
     */
    public function notLookahead($expression, $assertion)
    {
        return $this->add($expression, true)->add('(?!')->add($assertion)->add(')');
    }
    
    /**
     * Positive lookbehind assertion
     * @param string $expression
     * @param string $assertion
     * @return PatternBuilder
     */
    public function lookbehind($expression,$assertion) 
    {
        return $this->add('(?<=')->add($assertion)->add(')')->add($expression, true);
    }
    
    /**
     * Negative lookbehind assertion 
     * @param string $expression
     * @param string $assertion
     * @return PatternBuilder
     */
    public function notLookbehind($expression,$assertion) 
    {
        return $this->add('(?<!')->add($assertion)->add(')')->add($expression, true);
    }
    /**
     * Render the pattern as it can be used into a preg_...function
     * @return string
     */
    public function __toString()
    {
        $string='';
        if( $this->getGroup() ) {
            $string .= '(';
        }
        if( $this->getGroup() && !$this->getCapture()) {
            $string .= '?' . implode('',  array_unique($this->options)) . ':';
            
        }
        foreach ($this->getFragments() as $fragment) {
            if ( is_string($fragment) ) {
                $string.= $fragment;
            } elseif ($fragment instanceof PatternBuilder) {
                $string.=strval($fragment);
            }
        }
        if ($this->getGroup()) {
            $string.=')';
        }
        if (!$this->hasParent()) {
            $string = "/" . $string . "/";
            $string .= implode('',array_unique($this->options));
        }
        return $string;
    }
}
