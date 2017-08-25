<?php

class GrassBladeAddons {
	private $addons;
	
	function __construct() 
	{
		if(!defined("GRASSBLADE_ADDON_DIR"))
		define("GRASSBLADE_ADDON_DIR", dirname(__FILE__)."/addons");
		
		$this->addons = $this->GetAddons();
	}
	
	public function GetAddons()
	{
		if(!empty($this->addons))
		return $this->addons;
		
		$list = scandir(dirname(__FILE__));

		$addons = array();
		
		foreach($list as $list_item)
		{
			if(is_dir(GRASSBLADE_ADDON_DIR."/".$list_item) && $list_item != "." && $list_item != "..")
			$addons[] = $list_item;
		}

		$this->addons = $addons;
		return $addons;
	}
	
	public function GetAddonFile($addon, $filepath)
	{
		$file = GRASSBLADE_ADDON_DIR."/".$addon."/".$filepath;

		if(file_exists($file))
		return $file;
		else
		return false;
	}
	public function GetHelpFile($addon)
	{
		return $this->GetAddonFile($addon, "help.php");
	}
	public function GetFunctionFile($addon)
	{
		return $this->GetAddonFile($addon, "functions.php");
	}
	
	public function IncludeFile($file)
	{
		if(file_exists($file))
		{
			include($file);
			return true;
		}
		else
			return false;
	}
	
	public function IncludeFunctionFiles()
	{
		$addons = $this->addons;
		if(count($addons))
		foreach($addons as $addon)
		{
			$this->IncludeFile($this->GetFunctionFile($addon));
		}
	}	
	
	public function IncludeHelpFiles()
	{
		$addons = $this->addons;

		if(count($addons))
		foreach($addons as $addon)
		{
			$this->IncludeFile($this->GetHelpFile($addon));
		}
	}
	
}
