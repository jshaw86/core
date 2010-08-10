<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests the Config lib that's shipped with kohana
 *
 * @group kohana
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @author     Matt Button <matthew@sigswitch.com>
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
Class Kohana_ConfigTest extends Kohana_Unittest_TestCase
{

	/**
	 * When a config object is initially created there should be
	 * no readers attached
	 *
	 * @test
	 * @covers Kohana_Config
	 */
	public function test_initially_there_are_no_readers()
	{
		$config = new Kohana_Config;

		$this->assertAttributeSame(array(), '_readers', $config);
	}

	/**
	 * Test that calling attach() on a kohana config object
	 * adds the specified reader to the config object
	 *
	 * @test
	 * @covers Kohana_Config::attach
	 */
	public function test_attach_adds_reader_and_returns_this()
	{
		$config = new Kohana_Config;
		$reader = $this->getMockForAbstractClass('Kohana_Config_Reader');

		$this->assertSame($config, $config->attach($reader));

		$this->assertAttributeContains($reader, '_readers', $config);
	}

	/**
	 * By default (or by passing TRUE as the second parameter) the config object
	 * should prepend the reader to the front of the readers queue
	 *
	 * @test
	 * @covers Kohana_Config::attach
	 */
	public function test_attach_adds_reader_to_front_of_queue()
	{
		$config  = new Kohana_Config;

		$reader1 = $this->getMockForAbstractClass('Kohana_Config_Reader');
		$reader2 = $this->getMockForAbstractClass('Kohana_Config_Reader');

		$config->attach($reader1);
		$config->attach($reader2);

		// Rather than do two assertContains we'll do an assertSame to assert
		// the order of the readers
		$this->assertAttributeSame(array($reader2, $reader1), '_readers', $config);

		// Now we test using the second parameter
		$config = new Kohana_Config;

		$config->attach($reader1);
		$config->attach($reader2, TRUE);

		$this->assertAttributeSame(array($reader2, $reader1), '_readers', $config);
	}

	/**
	 * Calling detach() on a config object should remove it from the queue of readers
	 *
	 * @test
	 * @covers Kohana_Config::detach
	 */
	public function test_detach_removes_reader_and_returns_this()
	{
		$config  = new Kohana_Config;

		// Due to the way phpunit mock generator works if you try and mock a class
		// that has already been used then it just re-uses the first's name
		//
		// To get around this we have to specify a totally random name for the second mock object
		$reader1 = $this->getMockForAbstractClass('Kohana_Config_Reader');
		$reader2 = $this->getMockForAbstractClass('Kohana_Config_Reader', array(), 'MY_AWESOME_READER');
		
		$config->attach($reader1);
		$config->attach($reader2);

		$this->assertSame($config, $config->detach($reader1));

		$this->assertAttributeNotContains($reader1, '_readers', $config);
		$this->assertAttributeContains($reader2, '_readers', $config);

		$this->assertSame($config, $config->detach($reader2));

		$this->assertAttributeNotContains($reader2, '_readers', $config);
	}

	/**
	 * detach() should return $this even if the specified reader does not exist
	 *
	 * @test
	 * @covers Kohana_Config::detach
	 */
	public function test_detach_returns_this_even_when_reader_dnx()
	{
		$config = new Kohana_Config;
		$reader = $this->getMockForAbstractClass('Kohana_Config_Reader');

		$this->assertSame($config, $config->detach($reader));
	}
}
