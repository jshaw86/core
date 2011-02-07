<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests Kohana Form helper
 *
 * @group kohana
 * @group kohana.form
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_FormTest extends Kohana_Unittest_Testcase
{
	/**
	 * Defaults for this test
	 * @var array
	 */
	protected $environmentDefault = array(
		'Kohana::$base_url' => '/',
		'HTTP_HOST' => 'kohanaframework.org',
	);

	/**
	 * Provides test data for test_open()
	 * 
	 * @return array
	 */
	function provider_open()
	{
		return array(
			// $value, $result
			#array(NULL, NULL, '<form action="/" method="post" accept-charset="utf-8">'), // Fails because of Request::$current
			array('foo', NULL),
			array('', NULL),
			array('foo', array('method' => 'get')),
		);
	}

	/**
	 * Tests Form::open()
	 *
	 * @test
	 * @dataProvider provider_open
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function test_open($action, $attributes)
	{
		$tag = Form::open($action, $attributes);

		$matcher = array(
			'tag' => 'form',
			'attributes' => array(
				'method' => 'post',
				'accept-charset' => 'utf-8',
			),
		);
		
		if($attributes !== NULL)
			$matcher['attributes'] = $attributes + $matcher['attributes'];
		
		$this->assertTag($matcher, $tag);
	}

	/**
	 * Tests Form::close()
	 *
	 * @test
	 */
	function test_close()
	{
		$this->assertSame('</form>', Form::close());
	}

	/**
	 * Provides test data for test_input()
	 * 
	 * @return array
	 */
	function provider_input()
	{
		return array(
			// $value, $result
			array('input',    'foo', 'bar', NULL, 'input'),
			array('input',    'foo',  NULL, NULL, 'input'),
			array('hidden',   'foo', 'bar', NULL, 'hidden'),
			array('password', 'foo', 'bar', NULL, 'password'),
		);
	}

	/**
	 * Tests Form::input()
	 *
	 * @test
	 * @dataProvider provider_input
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function test_input($type, $name, $value, $attributes)
	{
		$matcher = array(
			'tag' => 'input',
			'attributes' => array('name' => $name, 'type' => $type)
		);

		// Form::input creates a text input
		if($type === 'input')
			$matcher['attributes']['type'] = 'text';

		// NULL just means no value
		if($value !== NULL)
			$matcher['attributes']['value'] = $value;

		// Add on any attributes
		if(is_array($attributes))
			$matcher['attributes'] = $attributes + $matcher['attributes'];

		$tag = Form::$type($name, $value, $attributes);

		$this->assertTag($matcher, $tag, $tag);
	}

	/**
	 * Provides test data for test_file()
	 * 
	 * @return array
	 */
	function provider_file()
	{
		return array(
			// $value, $result
			array('foo', NULL, '<input type="file" name="foo" />'),
		);
	}

	/**
	 * Tests Form::file()
	 *
	 * @test
	 * @dataProvider provider_file
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function test_file($name, $attributes, $expected)
	{
		$this->assertSame($expected, Form::file($name, $attributes));
	}

	/**
	 * Provides test data for test_check()
	 * 
	 * @return array
	 */
	function provider_check()
	{
		return array(
			// $value, $result
			array('checkbox', 'foo', NULL, FALSE, NULL),
			array('checkbox', 'foo', NULL, TRUE, NULL),
			array('checkbox', 'foo', 'bar', TRUE, NULL),
			
			array('radio', 'foo', NULL, FALSE, NULL),
			array('radio', 'foo', NULL, TRUE, NULL),
			array('radio', 'foo', 'bar', TRUE, NULL),
		);
	}

	/**
	 * Tests Form::check()
	 *
	 * @test
	 * @dataProvider provider_check
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function test_check($type, $name, $value, $checked, $attributes)
	{
		$matcher = array('tag' => 'input', 'attributes' => array('name' => $name, 'type' => $type));

		if($value !== NULL)
			$matcher['attributes']['value'] = $value;

		if(is_array($attributes))
			$matcher['attributes'] = $attributes + $matcher['attributes'];

		if($checked === TRUE)
			$matcher['attributes']['checked'] = 'checked';

		$tag = Form::$type($name, $value, $checked, $attributes);
		$this->assertTag($matcher, $tag, $tag);
	}

	/**
	 * Provides test data for test_text()
	 * 
	 * @return array
	 */
	function provider_text()
	{
		return array(
			// $value, $result
			array('textarea', 'foo', 'bar', NULL),
			array('textarea', 'foo', 'bar', array('rows' => 20, 'cols' => 20)),
			array('button', 'foo', 'bar', NULL),
			array('label', 'foo', 'bar', NULL),
			array('label', 'foo', NULL, NULL),
		);
	}

	/**
	 * Tests Form::textarea()
	 *
	 * @test
	 * @dataProvider provider_text
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function test_text($type, $name, $body, $attributes)
	{
		$matcher = array(
			'tag' => $type,
			'attributes' => array(),
			'content' => $body,
		);

		if($type !== 'label')
			$matcher['attributes'] = array('name' => $name);
		else
			$matcher['attributes'] = array('for' => $name);


		if(is_array($attributes))
			$matcher['attributes'] = $attributes + $matcher['attributes'];

		$tag = Form::$type($name, $body, $attributes);

		$this->assertTag($matcher, $tag, $tag);
	}


	/**
	 * Provides test data for test_select()
	 * 
	 * @return array
	 */
	function provider_select()
	{
		return array(
			// $value, $result
			array('foo', NULL, NULL, "<select name=\"foo\"></select>"),
			array('foo', array('bar' => 'bar'), NULL, "<select name=\"foo\">\n<option value=\"bar\">bar</option>\n</select>"),
			array('foo', array('bar' => 'bar'), 'bar', "<select name=\"foo\">\n<option value=\"bar\" selected=\"selected\">bar</option>\n</select>"),
			array('foo', array('bar' => array('foo' => 'bar')), NULL, "<select name=\"foo\">\n<optgroup label=\"bar\">\n<option value=\"foo\">bar</option>\n</optgroup>\n</select>"),
			array('foo', array('bar' => array('foo' => 'bar')), 'foo', "<select name=\"foo\">\n<optgroup label=\"bar\">\n<option value=\"foo\" selected=\"selected\">bar</option>\n</optgroup>\n</select>"),
			// #2286
			array('foo', array('bar' => 'bar', 'unit' => 'test', 'foo' => 'foo'), array('bar', 'foo'), "<select name=\"foo\" multiple=\"multiple\">\n<option value=\"bar\" selected=\"selected\">bar</option>\n<option value=\"unit\">test</option>\n<option value=\"foo\" selected=\"selected\">foo</option>\n</select>"),
		);
	}

	/**
	 * Tests Form::select()
	 *
	 * @test
	 * @dataProvider provider_select
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function test_select($name, $options, $selected, $expected)
	{
		// Much more efficient just to assertSame() rather than assertTag() on each element
		$this->assertSame($expected, Form::select($name, $options, $selected));
	}

	/**
	 * Provides test data for test_submit()
	 * 
	 * @return array
	 */
	function provider_submit()
	{
		return array(
			// $value, $result
			array('foo', 'Foobar!', '<input type="submit" name="foo" value="Foobar!" />'),
		);
	}

	/**
	 * Tests Form::submit()
	 *
	 * @test
	 * @dataProvider provider_submit
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function test_submit($name, $value, $expected)
	{
		$matcher = array(
			'tag' => 'input',
			'attributes' => array('name' => $name, 'type' => 'submit', 'value' => $value)
		);
			
		$this->assertTag($matcher, Form::submit($name, $value));
	}


	/**
	 * Provides test data for test_image()
	 * 
	 * @return array
	 */
	function provider_image()
	{
		return array(
			// $value, $result
			array('foo', 'bar', array('src' => 'media/img/login.png'), '<input type="image" name="foo" value="bar" src="/media/img/login.png" />'),
		);
	}

	/**
	 * Tests Form::submit()
	 *
	 * @test
	 * @dataProvider provider_image
	 * @param boolean $input  Input for File::mime
	 * @param boolean $expected Output for File::mime
	 */
	function test_image($name, $value, $attributes, $expected)
	{
		$this->assertSame($expected, Form::image($name, $value, $attributes));
	}
}
