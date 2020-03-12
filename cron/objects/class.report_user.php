<?php
/*
	This SQL query will create the table to store your object.

	CREATE TABLE `report_user` (
	`report_userid` int(11) NOT NULL auto_increment,
	`username` VARCHAR(255) NOT NULL,
	`password` VARCHAR(255) NOT NULL,
	`user_roleid` int(11) NOT NULL,
	`email` VARCHAR(255) NOT NULL,
	`details` TEXT NOT NULL, INDEX(`user_roleid`), PRIMARY KEY  (`report_userid`)) ENGINE=MyISAM;
*/

/**
* <b>report_user</b> class with integrated CRUD methods.
* @author Php Object Generator
* @version POG 3.0e / PHP5
* @copyright Free for personal & commercial use. (Offered under the BSD license)
* @link http://www.phpobjectgenerator.com/?language=php5&wrapper=pog&objectName=report_user&attributeList=array+%28%0A++0+%3D%3E+%27username%27%2C%0A++1+%3D%3E+%27password%27%2C%0A++2+%3D%3E+%27user_role%27%2C%0A++3+%3D%3E+%27email%27%2C%0A++4+%3D%3E+%27details%27%2C%0A++5+%3D%3E+%27report_action%27%2C%0A%29&typeList=array+%28%0A++0+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++1+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++2+%3D%3E+%27BELONGSTO%27%2C%0A++3+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++4+%3D%3E+%27TEXT%27%2C%0A++5+%3D%3E+%27HASMANY%27%2C%0A%29
*/

class report_user extends POG_Base
{
	public $report_userId = '';

	/**
	 * @var VARCHAR(255)
	 */
	public $username;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $password;
	
	/**
	 * @var INT(11)
	 */
	public $user_roleId;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $email;
	
	/**
	 * @var TEXT
	 */
	public $details;
	
	/**
	 * @var private array of report_action objects
	 */
	private $_report_actionList = array();
	
	public $pog_attribute_type = array(
		"report_userId" => array('db_attributes' => array("NUMERIC", "INT")),
		"username" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"password" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"user_role" => array('db_attributes' => array("OBJECT", "BELONGSTO")),
		"email" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
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
	
	function report_user($username='', $password='', $email='', $details='')
	{
		$this->username = $username;
		$this->password = $password;
		$this->email = $email;
		$this->details = $details;
		$this->_report_actionList = array();
	}
	
	
	/**
	* Gets object from database
	* @param integer $report_userId 
	* @return object $report_user
	*/
	function Get($report_userId)
	{
		$connection = Database::Connect();
		$this->pog_query = "select * from `report_user` where `report_userid`='".intval($report_userId)."' LIMIT 1";
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$this->report_userId = $row['report_userid'];
			$this->username = $this->Unescape($row['username']);
			$this->password = $this->Unescape($row['password']);
			$this->user_roleId = $row['user_roleid'];
			$this->email = $this->Unescape($row['email']);
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
	* @return array $report_userList
	*/
	function GetList($fcv_array = array(), $sortBy='', $ascending=true, $limit='')
	{
		$connection = Database::Connect();
		$sqlLimit = ($limit != '' ? "LIMIT $limit" : '');
		$this->pog_query = "select * from `report_user` ";
		$report_userList = Array();
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
			$sortBy = "report_userid";
		}
		$this->pog_query .= " order by ".$sortBy." ".($ascending ? "asc" : "desc")." $sqlLimit";
		//echo $this->pog_query."<br>";
		$thisObjectName = get_class($this);
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$report_user = new $thisObjectName();
			$report_user->report_userId = $row['report_userid'];
			$report_user->username = $this->Unescape($row['username']);
			$report_user->password = $this->Unescape($row['password']);
			$report_user->user_roleId = $row['user_roleid'];
			$report_user->email = $this->Unescape($row['email']);
			$report_user->details = $this->Unescape($row['details']);
			$report_userList[] = $report_user;
		}
		return $report_userList;
	}
	
	
	/**
	* Saves the object to the database
	* @return integer $report_userId
	*/
	function Save($deep = true)
	{
		$connection = Database::Connect();
		$this->pog_query = "select `report_userid` from `report_user` where `report_userid`='".$this->report_userId."' LIMIT 1";
		$rows = Database::Query($this->pog_query, $connection);
		if ($rows > 0)
		{
			$this->pog_query = "update `report_user` set 
			`username`='".$this->Escape($this->username)."', 
			`password`='".$this->Escape($this->password)."', 
			`user_roleid`='".$this->user_roleId."', 
			`email`='".$this->Escape($this->email)."', 
			`details`='".$this->Escape($this->details)."'where `report_userid`='".$this->report_userId."'";
		}
		else
		{
			$this->pog_query = "insert into `report_user` (`username`, `password`, `user_roleid`, `email`, `details`) values (
			'".$this->Escape($this->username)."', 
			'".$this->Escape($this->password)."', 
			'".$this->user_roleId."', 
			'".$this->Escape($this->email)."', 
			'".$this->Escape($this->details)."')";
		}
		
		//echo $this->pog_query."<br>";
		
		$insertId = Database::InsertOrUpdate($this->pog_query, $connection);
		if ($this->report_userId == "")
		{
			$this->report_userId = $insertId;
		}
		if ($deep)
		{
			foreach ($this->_report_actionList as $report_action)
			{
				$report_action->report_userId = $this->report_userId;
				$report_action->Save($deep);
			}
		}
		return $this->report_userId;
	}
	
	
	/**
	* Clones the object and saves it to the database
	* @return integer $report_userId
	*/
	function SaveNew($deep = false)
	{
		$this->report_userId = '';
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
		$this->pog_query = "delete from `report_user` where `report_userid`='".$this->report_userId."'";
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
				$pog_query = "delete from `report_user` where ";
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
	* Associates the user_role object to this one
	* @return boolean
	*/
	function GetUser_role()
	{
		//echo "Getting role <br>";
		$user_role = new user_role();
		return $user_role->Get($this->user_roleId);
	}
	
	
	/**
	* Associates the user_role object to this one
	* @return 
	*/
	function SetUser_role(&$user_role)
	{
		$this->user_roleId = $user_role->user_roleId;
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
		$fcv_array[] = array("report_userId", "=", $this->report_userId);
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
			$report_action->report_userId = '';
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
		$report_action->report_userId = $this->report_userId;
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