<?php namespace PHPDOM;
/**
 * @author Adam Schønemann
 * @package adamschoenemann/phpdom
 */

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
