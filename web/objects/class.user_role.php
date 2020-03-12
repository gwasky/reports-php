<?php
/*
	This SQL query will create the table to store your object.

	CREATE TABLE `user_role` (
	`user_roleid` int(11) NOT NULL auto_increment,
	`name` VARCHAR(255) NOT NULL,
	`description` VARCHAR(255) NOT NULL,
	`access` VARCHAR(255) NOT NULL, PRIMARY KEY  (`user_roleid`)) ENGINE=MyISAM;
*/

/**
* <b>user_role</b> class with integrated CRUD methods.
* @author Php Object Generator
* @version POG 3.0e / PHP5
* @copyright Free for personal & commercial use. (Offered under the BSD license)
* @link http://www.phpobjectgenerator.com/?language=php5&wrapper=pog&objectName=user_role&attributeList=array+%28%0A++0+%3D%3E+%27name%27%2C%0A++1+%3D%3E+%27description%27%2C%0A++2+%3D%3E+%27access%27%2C%0A++3+%3D%3E+%27report_user%27%2C%0A%29&typeList=array+%28%0A++0+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++1+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++2+%3D%3E+%27VARCHAR%28255%29%27%2C%0A++3+%3D%3E+%27HASMANY%27%2C%0A%29
*/

class user_role extends POG_Base
{
	public $user_roleId = '';

	/**
	 * @var VARCHAR(255)
	 */
	public $name;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $description;
	
	/**
	 * @var VARCHAR(255)
	 */
	public $access;
	
	/**
	 * @var private array of report_user objects
	 */
	private $_report_userList = array();
	
	public $pog_attribute_type = array(
		"user_roleId" => array('db_attributes' => array("NUMERIC", "INT")),
		"name" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"description" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"access" => array('db_attributes' => array("TEXT", "VARCHAR", "255")),
		"report_user" => array('db_attributes' => array("OBJECT", "HASMANY")),
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
	
	function user_role($name='', $description='', $access='')
	{
		$this->name = $name;
		$this->description = $description;
		$this->access = $access;
		$this->_report_userList = array();
	}
	
	
	/**
	* Gets object from database
	* @param integer $user_roleId 
	* @return object $user_role
	*/
	function Get($user_roleId)
	{
		$connection = Database::Connect();
		$this->pog_query = "select * from `user_role` where `user_roleid`='".intval($user_roleId)."' LIMIT 1";
		$cursor = Database::Reader($this->pog_query, $connection);
		
		//echo $this->pog_query."<br>";
		
		while ($row = Database::Read($cursor))
		{
			$this->user_roleId = $row['user_roleid'];
			$this->name = $this->Unescape($row['name']);
			$this->description = $this->Unescape($row['description']);
			$this->access = $this->Unescape($row['access']);
		}
		return $this;
	}
	
	
	/**
	* Returns a sorted array of objects that match given conditions
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param string $sortBy 
	* @param boolean $ascending 
	* @param int limit 
	* @return array $user_roleList
	*/
	function GetList($fcv_array = array(), $sortBy='', $ascending=true, $limit='')
	{
		$connection = Database::Connect();
		$sqlLimit = ($limit != '' ? "LIMIT $limit" : '');
		$this->pog_query = "select * from `user_role` ";
		$user_roleList = Array();
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
			$sortBy = "user_roleid";
		}
		$this->pog_query .= " order by ".$sortBy." ".($ascending ? "asc" : "desc")." $sqlLimit";
		$thisObjectName = get_class($this);
		$cursor = Database::Reader($this->pog_query, $connection);
		while ($row = Database::Read($cursor))
		{
			$user_role = new $thisObjectName();
			$user_role->user_roleId = $row['user_roleid'];
			$user_role->name = $this->Unescape($row['name']);
			$user_role->description = $this->Unescape($row['description']);
			$user_role->access = $this->Unescape($row['access']);
			$user_roleList[] = $user_role;
		}
		return $user_roleList;
	}
	
	
	/**
	* Saves the object to the database
	* @return integer $user_roleId
	*/
	function Save($deep = true)
	{
		$connection = Database::Connect();
		$this->pog_query = "select `user_roleid` from `user_role` where `user_roleid`='".$this->user_roleId."' LIMIT 1";
		$rows = Database::Query($this->pog_query, $connection);
		if ($rows > 0)
		{
			$this->pog_query = "update `user_role` set 
			`name`='".$this->Escape($this->name)."', 
			`description`='".$this->Escape($this->description)."', 
			`access`='".$this->Escape($this->access)."'where `user_roleid`='".$this->user_roleId."'";
		}
		else
		{
			$this->pog_query = "insert into `user_role` (`name`, `description`, `access`) values (
			'".$this->Escape($this->name)."', 
			'".$this->Escape($this->description)."', 
			'".$this->Escape($this->access)."')";
		}
		$insertId = Database::InsertOrUpdate($this->pog_query, $connection);
		if ($this->user_roleId == "")
		{
			$this->user_roleId = $insertId;
		}
		if ($deep)
		{
			foreach ($this->_report_userList as $report_user)
			{
				$report_user->user_roleId = $this->user_roleId;
				$report_user->Save($deep);
			}
		}
		return $this->user_roleId;
	}
	
	
	/**
	* Clones the object and saves it to the database
	* @return integer $user_roleId
	*/
	function SaveNew($deep = false)
	{
		$this->user_roleId = '';
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
			$report_userList = $this->GetReport_userList();
			foreach ($report_userList as $report_user)
			{
				$report_user->Delete($deep, $across);
			}
		}
		$connection = Database::Connect();
		$this->pog_query = "delete from `user_role` where `user_roleid`='".$this->user_roleId."'";
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
				$pog_query = "delete from `user_role` where ";
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
	* Gets a list of report_user objects associated to this one
	* @param multidimensional array {("field", "comparator", "value"), ("field", "comparator", "value"), ...} 
	* @param string $sortBy 
	* @param boolean $ascending 
	* @param int limit 
	* @return array of report_user objects
	*/
	function GetReport_userList($fcv_array = array(), $sortBy='', $ascending=true, $limit='')
	{
		$report_user = new report_user();
		$fcv_array[] = array("user_roleId", "=", $this->user_roleId);
		$dbObjects = $report_user->GetList($fcv_array, $sortBy, $ascending, $limit);
		return $dbObjects;
	}
	
	
	/**
	* Makes this the parent of all report_user objects in the report_user List array. Any existing report_user will become orphan(s)
	* @return null
	*/
	function SetReport_userList(&$list)
	{
		$this->_report_userList = array();
		$existingReport_userList = $this->GetReport_userList();
		foreach ($existingReport_userList as $report_user)
		{
			$report_user->user_roleId = '';
			$report_user->Save(false);
		}
		$this->_report_userList = $list;
	}
	
	
	/**
	* Associates the report_user object to this one
	* @return 
	*/
	function AddReport_user(&$report_user)
	{
		$report_user->user_roleId = $this->user_roleId;
		$found = false;
		foreach($this->_report_userList as $report_user2)
		{
			if ($report_user->report_userId > 0 && $report_user->report_userId == $report_user2->report_userId)
			{
				$found = true;
				break;
			}
		}
		if (!$found)
		{
			$this->_report_userList[] = $report_user;
		}
	}
}
?>