<?php
/*
	This SQL query will create the table to store your object.

	CREATE TABLE `report_action` (
	`report_actionid` int(11) NOT NULL auto_increment,
	`date_accessed` VARCHAR(255) NOT NULL,
	`report_userid` int(11) NOT NULL,
	`reportid` int(11) NOT NULL,
	`details` TEXT NOT NULL, INDEX(`report_userid`,`reportid`), PRIMARY KEY  (`report_actionid`)) ENGINE=MyISAM;
*/

/**
* <b>report_action</b> class with integrated CRUD methods.
* @author Php Object Generator
* @version POG 3.0e / PHP5
* @copyright Free for personal & commercial use. (Offered under the BSD license)
* @link http://www.phpobjectgenerator.com/?language=php5&wrapper=pog&objectName=report_action&attributeList=array+%28%0A++0+%3D%3E+%27date_accessed%27%2C%0A++1+%3D%3E+%27report_user%27%2C%0A++2+%3D%3E+%27report%27%2C%0A++3+%3D%3E+%27details%27%2C%0A%29&typeList=array+%28%0A++0+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++1+%3D%3E+%27BELONGSTO%27%2C%0A++2+%3D%3E+%27BELONGSTO%27%2C%0A++3+%3D%3E+%27TEXT%27%2C%0A%29
*/

class report_action extends POG_Base
{
	public $report_actionId = '';

	/**
	 * @var VARCHAR(255)
	 */
	public $date_accessed;
	
	/**
	 * @var INT(11)
	 */
	public $report_userId;
	
	/**
	 * @var INT(11)
	 */
	public $reportId;
	
	/**
	 * @var TEXT
	 */
	public $details;
	
	public $pog_attribute_type = array(
		"report_actionId" => array('db_attributes' => array("NUMERIC", "INT")),
		"date_accessed" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"report_user" => array('db_attributes' => array("OBJECT", "BELONGSTO")),
		"report" => array('db_attributes' => array("OBJECT", "BELONGSTO")),
		"details" => array('db_attributes' => array("TEXT", "TEXT")),
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
	
	function report_action($date_accessed='', $details='')
	{
		$this->date_accessed = $date_accessed;
		$this->details = $details;
	}
	
	
	/**
	* Gets object from database
	* @param integer $report_actionId 
	* @return object $report_action
	*/
	function Get($report_actionId)
	{
		$connection = Database::Connect();
		$this->pog_query = "select * from `report_action` where `report_actionid`='".intval($report_actionId)."' LIMIT 1";
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$this->report_actionId = $row['report_actionid'];
			$this->date_accessed = $this->Unescape($row['date_accessed']);
			$this->report_userId = $row['report_userid'];
			$this->reportId = $row['reportid'];
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
	* @return array $report_actionList
	*/
	function GetList($fcv_array = array(), $sortBy='', $ascending=true, $limit='')
	{
		$connection = Database::Connect();
		$sqlLimit = ($limit != '' ? "LIMIT $limit" : '');
		$this->pog_query = "select * from `report_action` ";
		$report_actionList = Array();
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
			$sortBy = "report_actionid";
		}
		$this->pog_query .= " order by ".$sortBy." ".($ascending ? "asc" : "desc")." $sqlLimit";
		$thisObjectName = get_class($this);
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$report_action = new $thisObjectName();
			$report_action->report_actionId = $row['report_actionid'];
			$report_action->date_accessed = $this->Unescape($row['date_accessed']);
			$report_action->report_userId = $row['report_userid'];
			$report_action->reportId = $row['reportid'];
			$report_action->details = $this->Unescape($row['details']);
			$report_actionList[] = $report_action;
		}
		return $report_actionList;
	}
	
	
	/**
	* Saves the object to the database
	* @return integer $report_actionId
	*/
	function Save()
	{
		$connection = Database::Connect();
		$this->pog_query = "select `report_actionid` from `report_action` where `report_actionid`='".$this->report_actionId."' LIMIT 1";
		$rows = Database::Query($this->pog_query, $connection);
		if ($rows > 0)
		{
			$this->pog_query = "update `report_action` set 
			`date_accessed`='".$this->Escape($this->date_accessed)."', 
			`report_userid`='".$this->report_userId."', 
			`reportid`='".$this->reportId."', 
			`details`='".$this->Escape($this->details)."' where `report_actionid`='".$this->report_actionId."'";
		}
		else
		{
			$this->pog_query = "insert into `report_action` (`date_accessed`, `report_userid`, `reportid`, `details` ) values (
			'".$this->Escape($this->date_accessed)."', 
			'".$this->report_userId."', 
			'".$this->reportId."', 
			'".$this->Escape($this->details)."' )";
		}
		$insertId = Database::InsertOrUpdate($this->pog_query, $connection);
		if ($this->report_actionId == "")
		{
			$this->report_actionId = $insertId;
		}
		return $this->report_actionId;
	}
	
	
	/**
	* Clones the object and saves it to the database
	* @return integer $report_actionId
	*/
	function SaveNew()
	{
		$this->report_actionId = '';
		return $this->Save();
	}
	
	
	/**
	* Deletes the object from the database
	* @return boolean
	*/
	function Delete()
	{
		$connection = Database::Connect();
		$this->pog_query = "delete from `report_action` where `report_actionid`='".$this->report_actionId."'";
		return Database::NonQuery($this->pog_query, $connection);
	}
	
	
	/**
	* Deletes a list of objects that match given conditions
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param bool $deep 
	* @return 
	*/
	function DeleteList($fcv_array)
	{
		if (sizeof($fcv_array) > 0)
		{
			$connection = Database::Connect();
			$pog_query = "delete from `report_action` where ";
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
	
	
	/**
	* Associates the report_user object to this one
	* @return boolean
	*/
	function GetReport_user()
	{
		$report_user = new report_user();
		return $report_user->Get($this->report_userId);
	}
	
	
	/**
	* Associates the report_user object to this one
	* @return 
	*/
	function SetReport_user(&$report_user)
	{
		$this->report_userId = $report_user->report_userId;
	}
	
	
	/**
	* Associates the report object to this one
	* @return boolean
	*/
	function GetReport()
	{
		$report = new report();
		return $report->Get($this->reportId);
	}
	
	
	/**
	* Associates the report object to this one
	* @return 
	*/
	function SetReport(&$report)
	{
		$this->reportId = $report->reportId;
	}
}
?>