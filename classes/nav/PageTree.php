<?php

namespace nav;

require_once 'classes/nav/Page.php';
require_once 'classes/nav/Root.php';

class PageTree {
	private static $shared = null;

	static public function getTree()
	{
		if(self::$shared == null)
		{
			self::$shared = new PageTree(Root::getPage());
		}
		return self::$shared;
	}

	protected $root;

	protected function __construct($root=null)
	{
		if(isset($root))
		{
			$this->root = $root;
		}
	}

	static public function componentsFromPath($path)
	{
		$res = array();
		if($path==null)
		{
			return $res;
		}
		$names = explode("/", $path);
		foreach($names as $name)
		{
			if($name!="")
			{
				$res[] = $name;
			}
		}
		return $res;
	}

	public function pageWithPath($path)
	{
		$names = $this->componentsFromPath($path);
		$page = $this->root;
		foreach($names as $name)
		{
			$page = $page->childWithName($name);
			if($page==null)
			{
				return null;
			}
		}
		return $page;
	}
}

?>