<?php
/**
 * Example of component
 *
 * @package BornDigital
 */

namespace BD\ExampleComponent;

/**
 * Class to setup the component
 */
class Setup
{
	/**
	 * Current module directory
	 *
	 * @var string
	 */
	private $dir;

	/**
	 * Current module url
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Setup the flow
	 */
	public function __construct()
	{
		$this->dir = MODULES_DIR . '/sample';
		$this->url = MODULES_URL . '/sample';
	}
}

new Setup();
