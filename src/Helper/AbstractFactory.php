<?php //strict

namespace LayoutCore\Helper;

use Plenty\Plugin\Application;

/**
 * Class AbstractFactory
 * @package LayoutCore\Helper
 */
class AbstractFactory
{
	/**
	 * @var Application
	 */
	private $app;

    /**
     * AbstractFactory constructor.
     * @param Application $app
     */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

    /**
     * Create a class instance
     * @param string $className
     * @return mixed
     * @throws \Exception
     */
	public function make(string $className)
	{
		$instance = $this->app->make($className);
		if(!$instance instanceof $className)
		{
			throw new \Exception("Cannot create instance of class: " . $className);
		}
		return $instance;
	}
}
