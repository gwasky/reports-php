<?php
/*
	This SQL query will create the table to store your object.

	CREATE TABLE `report` (
	`reportid` int(11) NOT NULL auto_increment,
	`type` VARCHAR(255) NOT NULL,
	`reportname` VARCHAR(255) NOT NULL,
	`name` VARCHAR(255) NOT NULL,
	`details` TEXT NOT NULL, PRIMARY KEY  (`reportid`)) ENGINE=MyISAM;
*/

/**
* <b>report</b> class with integrated CRUD methods.
* @author Php Object Generator
* @version POG 3.0e / PHP5
* @copyright Free for personal & commercial use. (Offered under the BSD license)
* @link http://www.phpobjectgenerator.com/?language=php5&wrapper=pog&objectName=report&attributeList=array+%28%0A++0+%3D%3E+%27type%27%2C%0A++1+%3D%3E+%27reportname%27%2C%0A++2+%3D%3E+%27name%27%2C%0A++3+%3D%3E+%27details%27%2C%0A++4+%3D%3E+%27report_action%27%2C%0A%29&typeList=array+%28%0A++0+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++1+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++2+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++3+%3D%3E+%27TEXT%27%2C%0A++4+%3D%3E+%27HASMANY%27%2C%0A%29
*/

class report extends POG_Base
{
	public $reportId = '';

	/**
	 * @var VARCHAR(255)
	 */
	public $type;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $reportname;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $name;
	
	/**
	 * @var TEXT
	 */
	public $details;
	
	/**
	 * @var private array of report_action objects
	 */
	private $_report_actionList = array();
	
	public $pog_attribute_type = array(
		"reportId" => array('db_attributes' => array("NUMERIC", "INT")),
		"type" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"reportname" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"name" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"details" => array('db_attributes' => array("TEXT", "TEXT")),
		"report_action" => array('db_attributes' => array("OBJECT", "HASMANY")),
		);
	public $pog_query;
	
	
	/**
	* Getter for some private attributes
	* @return mixed $attribute
	*/
	public function __get($attribute)
	{
		if (isset($this->{"_".$attribute}))
		{
			return $this->{"_".$attribute};
		}
		else
		{
			return false;
		}
	}
	
	function report($type='', $reportname='', $name='', $details='')
	{
		$this->type = $type;
		$this->reportname = $reportname;
		$this->name = $name;
		$this->details = $details;
		$this->_report_actionList = array();
	}
	
	
	/**
	* Gets object from database
	* @param integer $reportId 
	* @return object $report
	*/
	function Get($reportId)
	{
		$connection = Database::Connect();
		$this->pog_query = "select * from `report` where `reportid`='".intval($reportId)."' LIMIT 1";
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$this->reportId = $row['reportid'];
			$this->type = $this->Unescape($row['type']);
			$this->reportname = $this->Unescape($row['reportname']);
			$this->name = $this->Unescape($row['name']);
			$this->details = $this->Unescape($row['details']);
		}
		return $this;
	}
	
	
	/**
	* Returns a sorted array of objects that match given conditions
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param string $sortBy 
	* @param boolean $ascending 
	* @param int limit 
	* @return array $reportList
	*/
	function GetList($fcv_array = array(), $sortBy='', $ascending=true, $limit='')
	{
		$connection = Database::Connect();
		$sqlLimit = ($limit != '' ? "LIMIT $limit" : '');
		$this->pog_query = "select * from `report` ";
		$reportList = Array();
		if (sizeof($fcv_array) > 0)
		{
			$this->pog_query .= " where ";
			for ($i=0, $c=sizeof($fcv_array); $i<$c; $i++)
			{
				if (sizeof($fcv_array[$i]) == 1)
				{
					$this->pog_query .= " ".$fcv_array[$i][0]." ";
					continue;
				}
				else
				{
					if ($i > 0 && sizeof($fcv_array[$i-1]) != 1)
					{
						$this->pog_query .= " AND ";
					}
					if (isset($this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes']) && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'SET')
					{
						if ($GLOBALS['configuration']['db_encoding'] == 1)
						{
							$value = POG_Base::IsColumn($fcv_array[$i][2]) ? "BASE64_DECODE(".$fcv_array[$i][2].")" : "'".$fcv_array[$i][2]."'";
							$this->pog_query .= "BASE64_DECODE(`".$fcv_array[$i][0]."`) ".$fcv_array[$i][1]." ".$value;
						}
						else
						{
							$value =  POG_Base::IsColumn($fcv_array[$i][2]) ? $fcv_array[$i][2] : "'".$this->Escape($fcv_array[$i][2])."'";
							$this->pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." ".$value;
						}
					}
					else
					{
						$value = POG_Base::IsColumn($fcv_array[$i][2]) ? $fcv_array[$i][2] : "'".$fcv_array[$i][2]."'";
						$this->pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." ".$value;
					}
				}
			}
		}
		if ($sortBy != '')
		{
			if (isset($this->pog_attribute_type[$sortBy]['db_attributes']) && $this->pog_attribute_type[$sortBy]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$sortBy]['db_attributes'][0] != 'SET')
			{
				if ($GLOBALS['configuration']['db_encoding'] == 1)
				{
					$sortBy = "BASE64_DECODE($sortBy) ";
				}
				else
				{
					$sortBy = "$sortBy ";
				}
			}
			else
			{
				$sortBy = "$sortBy ";
			}
		}
		else
		{
			$sortBy = "reportid";
		}
		$this->pog_query .= " order by ".$sortBy." ".($ascending ? "asc" : "desc")." $sqlLimit";
		$thisObjectName = get_class($this);
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$report = new $thisObjectName();
			$report->reportId = $row['reportid'];
			$report->type = $this->Unescape($row['type']);
			$report->reportname = $this->Unescape($row['reportname']);
			$report->name = $this->Unescape($row['name']);
			$report->details = $this->Unescape($row['details']);
			$reportList[] = $report;
		}
		return $reportList;
	}
	
	
	/**
	* Saves the object to the database
	* @return integer $reportId
	*/
	function Save($deep = true)
	{
		$connection = Database::Connect();
		$this->pog_query = "select `reportid` from `report` where `reportid`='".$this->reportId."' LIMIT 1";
		$rows = Database::Query($this->pog_query, $connection);
		if ($rows > 0)
		{
			$this->pog_query = "update `report` set 
			`type`='".$this->Escape($this->type)."', 
			`reportname`='".$this->Escape($this->reportname)."', 
			`name`='".$this->Escape($this->name)."', 
			`details`='".$this->Escape($this->details)."'where `reportid`='".$this->reportId."'";
		}
		else
		{
			$this->pog_query = "insert into `report` (`type`, `reportname`, `name`, `details`) values (
			'".$this->Escape($this->type)."', 
			'".$this->Escape($this->reportname)."', 
			'".$this->Escape($this->name)."', 
			'".$this->Escape($this->details)."')";
		}
		$insertId = Database::InsertOrUpdate($this->pog_query, $connection);
		if ($this->reportId == "")
		{
			$this->reportId = $insertId;
		}
		if ($deep)
		{
			foreach ($this->_report_actionList as $report_action)
			{
				$report_action->reportId = $this->reportId;
				$report_action->Save($deep);
			}
		}
		return $this->reportId;
	}
	
	
	/**
	* Clones the object and saves it to the database
	* @return integer $reportId
	*/
	function SaveNew($deep = false)
	{
		$this->reportId = '';
		return $this->Save($deep);
	}
	
	
	/**
	* Deletes the object from the database
	* @return boolean
	*/
	function Delete($deep = false, $across = false)
	{
		if ($deep)
		{
			$report_actionList = $this->GetReport_actionList();
			foreach ($report_actionList as $report_action)
			{
				$report_action->Delete($deep, $across);
			}
		}
		$connection = Database::Connect();
		$this->pog_query = "delete from `report` where `reportid`='".$this->reportId."'";
		return Database::NonQuery($this->pog_query, $connection);
	}
	
	
	/**
	* Deletes a list of objects that match given conditions
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param bool $deep 
	* @return 
	*/
	function DeleteList($fcv_array, $deep = false, $across = false)
	{
		if (sizeof($fcv_array) > 0)
		{
			if ($deep || $across)
			{
				$objectList = $this->GetList($fcv_array);
				foreach ($objectList as $object)
				{
					$object->Delete($deep, $across);
				}
			}
			else
			{
				$connection = Database::Connect();
				$pog_query = "delete from `report` where ";
				for ($i=0, $c=sizeof($fcv_array); $i<$c; $i++)
				{
					if (sizeof($fcv_array[$i]) == 1)
					{
						$pog_query .= " ".$fcv_array[$i][0]." ";
						continue;
					}
					else
					{
						if ($i > 0 && sizeof($fcv_array[$i-1]) !== 1)
						{
							$pog_query .= " AND ";
						}
						if (isset($this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes']) && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'NUMERIC' && $this->pog_attribute_type[$fcv_array[$i][0]]['db_attributes'][0] != 'SET')
						{
							$pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." '".$this->Escape($fcv_array[$i][2])."'";
						}
						else
						{
							$pog_query .= "`".$fcv_array[$i][0]."` ".$fcv_array[$i][1]." '".$fcv_array[$i][2]."'";
						}
					}
				}
				return Database::NonQuery($pog_query, $connection);
			}
		}
	}
	
	
	/**
	* Gets a list of report_action objects associated to this one
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param string $sortBy 
	* @param boolean $ascending 
	* @param int limit 
	* @return array of report_action objects
	*/
	function GetReport_actionList($fcv_array = array(), $sortBy='', $ascending=true, $limit='')
	{
		$report_action = new report_action();
		$fcv_array[] = array("reportId", "=", $this->reportId);
		$dbObjects = $report_action->GetList($fcv_array, $sortBy, $ascending, $limit);
		return $dbObjects;
	}
	
	
	/**
	* Makes this the parent of all report_action objects in the report_action List array. Any existing report_action will become orphan(s)
	* @return null
	*/
	function SetReport_actionList(&$list)
	{
		$this->_report_actionList = array();
		$existingReport_actionList = $this->GetReport_actionList();
		foreach ($existingReport_actionList as $report_action)
		{
			$report_action->reportId = '';
			$report_action->Save(false);
		}
		$this->_report_actionList = $list;
	}
	
	
	/**
	* Associates the report_action object to this one
	* @return 
	*/
	function AddReport_action(&$report_action)
	{
		$report_action->reportId = $this->reportId;
		$found = false;
		foreach($this->_report_actionList as $report_action2)
		{
			if ($report_action->report_actionId > 0 && $report_action->report_actionId == $report_action2->report_actionId)
			{
				$found = true;
				break;
			}
		}
		if (!$found)
		{
			$this->_report_actionList[] = $report_action;
		}
	}
}
?>