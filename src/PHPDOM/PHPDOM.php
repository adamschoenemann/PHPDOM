<?php namespace PHPDOM;
/**
 * @author Adam Schønemann
 * @package adamschoenemann/phpdom
 * @version 0.0.1
 */

/**
 * Represents an attribute
 */
class Attribute
{
	/**
	 * The values of the attribute
	 * @var array<string>
	 */
	private $vals;

	function __construct($vals)
	{
		$this->set($vals);
	}

	/**
	 * Returns true if the attribute contains $val
	 * @param  string $val
	 * @return boolean
	 */
	public function contains($val)
	{
		return (in_array($val, $this->vals));
	}

	/**
	 * Add a value, or values to the attribute
	 * @param string|array<string> $vals A string, or array of strings containing the values
	 */
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

	/**
	 * Sets the value(s) of the attribute, overwriting any existing values
	 * @param string|array<string> $vals
	 */
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

	/**
	 * Removes a value from the attribute
	 * @param  string $val
	 */
	public function remove($val)
	{
		$this->vals = array_filter($this->vals, function($x)
		{
			return ($x != $val);
		});
	}

	/**
	 * Converts the attribute to a string, with values separated by spaces
	 * @return string
	 */
	public function toString()
	{
		return implode($this->vals, ' ');
	}

}

/**
 * An interface describing things that can be converted to markup
 */
interface ToMarkup
{
	/**
	 * Converts the Text element to markup (string)
	 * @param  boolean $pretty
	 * @param  integer $level
	 * @return string          The generated markup
	 */
	public function toMarkup($pretty, $level);
}

/**
 * A text element
 */
class Text implements ToMarkup
{
	private $text;

	/**
	 * Construct a new Text element
	 * @param string $text
	 */
	public function __construct($text)
	{
		$this->text = $text;
	}

	/**
	 * Converts the Text element to markup (string)
	 * @param  boolean $pretty
	 * @param  integer $level
	 * @return string          The generated markup
	 */
	public function toMarkup($pretty = false, $level = 0)
	{
		return str_repeat("\t", $level) . $this->text;
	}
}

/**
 * A DOM element ("Tag")
 */
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

	/**
	 * Adds an attribute. If it already exists, the value will added with a space prepended
	 * @param string $key The attribute's name
	 * @param string $val The attribute's value
	 */
	public function addAttribute($key, $val)
	{
		if(array_key_exists($key, $this->attributes))
			$this->attributes[$key]->add($val);
		else
			$this->setAttribute($key, $val);
	}

	/**
	 * Set an attribute. If it already exists, its value will be overwritten
	 * @param string $key The attribute's name
	 * @param string $val The attribute's value
	 */
	public function setAttribute($key, $val)
	{
		$this->attributes[$key] = new Attribute($val);
	}

	/**
	 * Returns true if the attribute exists on the element, and is non-empty
	 * @param  string  $key The attribute's name
	 * @return boolean      If it exists or not
	 */
	public function hasAttribute($key)
	{
		return in_array($key, $this->attributes) && trim($this->getAttribute($key)) != '';
	}

	/**
	 * Returns true if the Element has the class
	 * @param  string  $class The name of the class
	 * @return boolean        If it exists or not
	 */
	public function hasClass($class)
	{
		return $this->hasAttribute('class');
	}

	/**
	 * Returns the attribute with name $key.
	 * Throws an exception if it does not exists and no default is given.
	 * @param  string $key 		The name of the attribute
	 * @param  string $default  The default value to return if it does not exist
	 * @return string      		The value of the attribute
	 */
	public function getAttribute($key, $default = null)
	{
		$attr = $this->attributes[$key];
		if ($attr === null)
		{
			if ($default === null)
				throw new Exception("Attribute $key does not exist");
			else return $default;
		}

		return $attr->toString();
	}

	/**
	 * Adds a class to the Element
	 * @param string $class The name of the class
	 */
	public function addClass($class)
	{
		$this->addAttribute('class', $class);
	}

	/**
	 * Removes an attribute from the Element
	 * @param  string $key The name of the attribute
	 * @param  string $val Optionally, the only value of the attribute to remove
	 */
	public function removeAttribute($key, $val = null)
	{
		if($val !== null)
			unset($this->attributes[$key]);
		else
			$this->attributes[$key]->remove($val);
	}

	/**
	 * Converts the element and all its children to markup
	 * @param  boolean $pretty If newlines and indentation should be added
	 * @param  integer $level  What level of indentation are we at? Mostly used privately
	 * @return string          The generated markup
	 */
	public function toMarkup($pretty = false, $level = 0)
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
			$children[] = $child->toMarkup($pretty, $level + 1);
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

	/**
	 * Returns the name of the Element
	 * @return string The name of the element
	 */
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

	/**
	 * Append an Element to the node
	 * @param  Element $node The element to append
	 * @return Element       The calling instance for chaining
	 */
	public function append(&$node)
	{
		$node->parent = this;
		$this->children[] = $node;
		return this;
	}

	/**
	 * Get the child node at $index
	 * @param  int $index The index
	 * @return Element    The child ant $index
	 */
	public function get($index)
	{
		return $this->children[$index];
	}

	/**
	 * Returns the first child
	 * @return Element The first child
	 */
	public function first()
	{
		return $this->get(0);
	}

	/**
	 * Returns the last element
	 * @return Element
	 */
	public function last()
	{
		return $this->get(count($this->children) - 1);
	}

	/**
	 * Returns the number of child Elements
	 * @return int
	 */
	public function count()
	{
		return count($this->children);
	}

	/**
	 * Returns the parent of this Element, or null
	 * @return Element
	 */
	public function parent()
	{
		return $this->parent;
	}

	/**
	 * Returns all child elements with class $class
	 * @param  string $class
	 * @return array<Element>
	 */
	public function withClass($class)
	{
		return array_filter($this->children, function($child)
		{
			return ($child->hasClass('class'));
		});
	}

	/**
	 * Adds a text element with $text
	 * @param string $text
	 */
	public function addText($text)
	{
		return $this->addChild(new Text($text));
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
