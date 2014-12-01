<?php

class Attribute
{
	private $vals; // array of strings

	function __construct($vals)
	{
		$this->set($vals);
	}

	public function contains($val)
	{
		return (in_array($val, $this->vals));
	}

	public function add($vals)
	{
		if(is_scalar($vals))
		{
			$this->vals = array_merge($this->vals, array_map('trim', explode(' ', $vals)));
		}
		else if(is_array($vals))
		{
			$this->vals += array_map('trim', $vals);
		}
		else
		{
			throw new Exception('Invalid vals $vals');
		}
	}

	public function set($vals)
	{
		if(is_scalar($vals))
		{
			$this->vals = explode(' ', $vals);
		}
		else if(is_array($vals))
		{
			$this->vals = $vals;
		}
		else
		{
			throw new Exception("Invalid vals $vals");
		}

		$this->vals = array_map('trim', $this->vals);
	}

	public function remove($val)
	{
		$this->vals = array_filter($this->vals, function($x)
		{
			return ($x != $val);
		});
	}

	public function toString()
	{
		return implode($this->vals, ' ');
	}

}

interface ToHtml
{
	public function toHtml($pretty, $level);
}

class HtmlText implements ToHtml
{
	private $text;

	public function __construct($text)
	{
		$this->text = $text;
	}

	public function toHtml($pretty = false, $level = 0)
	{
		return str_repeat("\t", $level) . $this->text;
	}
}

class Element
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * assoc of string -> attribute
	 * @var assoc
	 */
	private $attributes;

	/**
	 * An array of Element children
	 * @var array
	 */
	private $children;

	/**
	 * The parent of this element
	 * @var Element
	 */
	private $parent = null;

	/**
	 * Constructor
	 * @param string  $name       The tag of the element
	 * @param assoc   $attributes The attributes
	 * @param array   $children   An array of Elements
	 */
	public function __construct($name, $attributes = null, $children = array())
	{
		$this->name = $name;
		$this->attributes = array();
		if ($attributes !== null)
		{
			foreach ($attributes as $key => $value) {
				$this->attributes[$key] = new Attribute($value);
			}
		}
		$this->children = $children;
	}


	public function addAttribute($key, $val)
	{
		if(array_key_exists($key, $this->attributes))
			$this->attributes[$key]->add($val);
		else
			$this->setAttribute($key, $val);
	}

	public function setAttribute($key, $val)
	{
		$this->attributes[$key] = new Attribute($val);
	}

	public function hasAttribute($key)
	{
		return in_array($key, $this->attributes) && $this->getAttribute($key) != '';
	}

	public function hasClass($class)
	{
		return $this->hasAttribute('class');
	}

	public function getAttribute($key)
	{
		$attr = $this->attributes[$key];
		if($attr === null)
			throw new Exception("Attribute $key does not exist");
		return $attr->toString();
	}

	public function addClass($class)
	{
		$this->addAttribute('class', $class);
	}

	public function removeAttribute($key, $val = null)
	{
		if($val !== null)
			unset($this->attributes[$key]);
		else
			$this->attributes[$key]->remove($val);
	}

	public function toHtml($pretty = false, $level = 0)
	{
		$indent = ($pretty) ? str_repeat("\t", $level) : '';
		$newline = ($pretty) ? "\n" : '';
		$attrs = array();
		foreach ($this->attributes as $key => $attr)
		{
			$attrs[] = $key . '="' . $attr->toString() . '"';
		}
		$children = array();
		foreach ($this->children as $child)
		{
			$children[] = $child->toHtml($pretty, $level + 1);
		}
		$html = array();
		$line =  $indent . '<' . $this->getName();
		if(count($attrs) > 0)
			$line .= ' ' . implode($attrs, ' ');
		$line .= '>';
		$html[] = $line;

		$line = implode($children, $newline);
		$html[] = $line;

		$line = $indent . '</' . $this->getName() . '>';
		$html[] = $line;

		return implode($html, $newline);
	}

	public function name()
	{
		return $this->name;
	}
	/**
	 * Remove a class
	 * If the class exists, it will be removed.
	 * Otherwise, it is a no-op
	 * @param $class string
	 **/
	public function removeClass($class)
	{
		return $this->removeAttribute('class', $class);
	}

	public function append(&$node)
	{
		$node->parent = this;
		$this->children[] = $node;
		return $node;
	}

	public function get($index)
	{
		return $this->children[$index];
	}

	public function first()
	{
		return $this->get(0);
	}

	public function last()
	{
		return $this->get(count($this->children) - 1);
	}

	public function count()
	{
		return count($this->children);
	}

	public function parent()
	{
		return $this->parent;
	}

	public function withClass($class)
	{
		return array_filter($this->children, function($child)
		{
			return ($child->hasClass('class'));
		});
	}

	public function addText($text)
	{
		return $this->addChild(new HtmlText($text));
	}

}

class Div extends Element {
	function __construct($attributes = null, $children = array()) {
		parent::__construct('div', $attributes, $children);
	}
}

class Span extends Element {
	function __construct($attributes = null, $children = array()) {
		parent::__construct('span', $attributes, $children);
	}
}

class Table extends Element {
	function __construct($attributes = null, $children = array()) {
		parent::__construct('table', $attributes, $children);
	}
}
class P extends Element {
	function __construct($attributes = null, $children = array()) {
		parent::__construct('p', $attributes, $children);
	}
}
class A extends Element {
	function __construct($attributes = null, $children = array()) {
		parent::__construct('a', $attributes, $children);
	}
}
class Li extends Element {
	function __construct($attributes = null, $children = array()) {
		parent::__construct('li', $attributes, $children);
	}
}
class Ul extends Element {
	function __construct($attributes = null, $children = array()) {
		parent::__construct('ul', $attributes, $children);
	}
}
class Ol extends Element {
	function __construct($attributes = null, $children = array()) {
		parent::__construct('ol', $attributes, $children);
	}
}
