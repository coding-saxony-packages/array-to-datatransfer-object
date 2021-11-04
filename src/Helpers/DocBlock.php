<?php

namespace CodingSaxony\ArrayToDataTransferObject\Helpers;

use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;

/**
 * Parse the docblock of a function or method
 *
 * @author Paul Scott <paul@duedil.com>
 * {@link http://www.github.com/icio/PHP-DocBlock-Parser}
 */
class DocBlock
{
	/**
	 * Tags in the docblock that have a whitespace-delimited number of parameters
	 * (such as `@param type var desc` and `@return type desc`) and the names of
	 * those parameters.
	 *
	 * @type array
	 */
	public static array $vectors = [
		'param'  => [
			'type',
			'var',
			'desc',
		],
		'return' => [
			'type',
			'desc',
		],
	];

	/**
	 * The description of the symbol
	 *
	 * @type string
	 */
	public string $desc;

	/**
	 * The tags defined in the docblock.
	 *
	 * The array has keys which are the tag names (excluding the @) and values
	 * that are arrays, each of which is an entry for the tag.
	 *
	 * In the case where the tag name is defined in {@see DocBlock::$vectors} the
	 * value within the tag-value array is an array in itself with keys as
	 * described by {@see DocBlock::$vectors}.
	 *
	 * @type array
	 */
	public array $tags;

	/**
	 * The entire DocBlock comment that was parsed.
	 *
	 * @type string
	 */
	public string $comment;

	/**
	 * The DocBlock constructor
	 *
	 * @param String|null $comment The text of the docblock
	 */
	public function __construct(?string $comment = null)
	{
		if ($comment) {
			$this->setComment($comment);
		}
	}

	/**
	 * The docblock of a class.
	 *
	 * @param string $class The class name
	 *
	 * @return DocBlock|null
	 * @throws ReflectionException
	 */
	public static function ofClass(string $class): ?DocBlock
	{
		return DocBlock::of(new ReflectionClass($class));
	}

	/**
	 * The docblock of a class property.
	 *
	 * @param String $class    The class on which the property is defined
	 * @param String $property The name of the property
	 *
	 * @return DocBlock|null
	 * @throws ReflectionException
	 */
	public static function ofProperty(string $class, string $property): ?DocBlock
	{
		return DocBlock::of(new ReflectionProperty($class, $property));
	}

	/**
	 * The docblock of a function.
	 *
	 * @param string $function The name of the function
	 *
	 * @return DocBlock|null
	 * @throws ReflectionException
	 */
	public static function ofFunction(string $function): ?DocBlock
	{
		return DocBlock::of(new ReflectionFunction($function));
	}

	/**
	 * The docblock of a class method.
	 *
	 * @param string $class  The class on which the method is defined
	 * @param string $method The name of the method
	 *
	 * @return DocBlock|null
	 * @throws ReflectionException
	 */
	public static function ofMethod(string $class, string $method): ?DocBlock
	{
		return DocBlock::of(new ReflectionMethod($class, $method));
	}

	/**
	 * The docblock of a reflection.
	 *
	 * @param Reflector $ref A reflector object defining `getDocComment`.
	 *
	 * @return DocBlock|null
	 */
	public static function of(Reflector $ref): ?DocBlock
	{
		if (method_exists($ref, 'getDocComment')) {
			return new DocBlock($ref->getDocComment());
		}

		return null;
	}

	/**
	 * Set and parse the docblock comment.
	 *
	 * @param String $comment The docblock
	 */
	public function setComment(string $comment)
	{
		$this->desc    = '';
		$this->tags    = [];
		$this->comment = $comment;

		$this->parseComment($comment);
	}

	/**
	 * Parse the comment into the component parts and set the state of the object.
	 *
	 * @param string $comment The docblock
	 */
	protected function parseComment(string $comment)
	{
		// Strip the opening and closing tags of the docblock
		$comment = substr($comment, 3, -2);

		// Split into arrays of lines
		$comment = preg_split('/\r?\n\r?/', $comment);

		// Trim asterisks and whitespace from the beginning and whitespace from the end of lines
		$comment = array_map(function ($line) {
			return ltrim(rtrim($line), "* \t\n\r\0\x0B");
		}, $comment);

		// Group the lines together by @tags
		$blocks = [];
		$b      = -1;

		foreach ($comment as $line) {
			if (self::isTagged($line)) {
				$b++;
				$blocks[] = [];
			} else if ($b == -1) {
				$b        = 0;
				$blocks[] = [];
			}

			$blocks[$b][] = $line;
		}

		// Parse the blocks
		foreach ($blocks as $block => $body) {
			$body = trim(implode("\n", $body));

			if ($block == 0 && !self::isTagged($body)) {
				// This is the description block
				$this->desc = $body;
				continue;
			} else {
				// This block is tagged
				$tag  = substr(self::strTag($body), 1);
				$body = ltrim(substr($body, strlen($tag) + 2));

				if (isset(self::$vectors[$tag])) {
					// The tagged block is a vector
					$count = count(self::$vectors[$tag]);

					if ($body) {
						$parts = preg_split('/\s+/', $body, $count);
					} else {
						$parts = [];
					}

					// Default the trailing values
					$parts = array_pad($parts, $count, null);

					// Store as a mapped array
					$this->tags[$tag][] = array_combine(
						self::$vectors[$tag],
						$parts
					);
				} else {
					// The tagged block is only text
					$this->tags[$tag][] = $body;
				}
			}
		}
	}

	/**
	 * Whether a docblock contains a given @tag.
	 *
	 * @param string $tag The name of the @tag to check for
	 *
	 * @return bool
	 */
	public function hasTag(string $tag): bool
	{
		return array_key_exists($tag, $this->tags);
	}

	/**
	 * The value of a tag
	 *
	 * @param string $tag
	 *
	 * @return array|null
	 */
	public function tag(string $tag): ?array
	{
		return $this->hasTag($tag) ? $this->tags[$tag] : null;
	}

	/**
	 * The value of a tag (concatenated for multiple values)
	 *
	 * @param string $tag
	 * @param string $sep The separator for concatenating
	 *
	 * @return string|null
	 */
	public function tagImplode(string $tag, string $sep = ' '): ?string
	{
		return $this->hasTag($tag) ? implode($sep, $this->tags[$tag]) : null;
	}

	/**
	 * The value of a tag (merged recursively)
	 *
	 * @param string $tag
	 *
	 * @return array|null
	 */
	public function tagMerge(string $tag): ?array
	{
		return $this->hasTag($tag) ? array_merge_recursive($this->tags[$tag]) : null;
	}

	/**
	 * Whether a string begins with a @tag
	 *
	 * @param string $str
	 *
	 * @return bool
	 */
	public static function isTagged(string $str): bool
	{
		return isset($str[1]) && $str[0] == '@' && ctype_alpha($str[1]);
	}

	/**
	 * The tag at the beginning of a string
	 *
	 * @param string $str
	 *
	 * @return String|null
	 */
	public static function strTag(string $str): ?string
	{
		if (preg_match('/^@[a-z0-9_]+/', $str, $matches)) {
			return $matches[0];
		}

		return null;
	}
}