<?php namespace PHPDOM;
/**
 * @package adamschoenemann/phpdom
 * @author Adam Schønemann
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
