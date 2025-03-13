<?php
class timeclock {
	private $user;
	private $pass;
	public $valid = false;
	private $tcParams;
	private $db;
	private $initialized = false;
	
	public function __construct() {
	}
	
	public function dumpParams() { echo "<pre>". print_r($this->tcParams,1); }
	
	public function initialize($user=null,$pass=null) {

        require_once('GCCL/DB/PDO_CONN_GCCL_hr.php3');
		
		$this->valid = false;
		
		if (!is_null($user) && !is_null($pass)) {
			// check against the HR database
			// pass must match
            $this->db = new PDO(PDO_GCCL_hr_CONN,PDO_GCCL_hr_USER,PDO_GCCL_hr_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
            $this->db->setAttribute(pdo::ATTR_DEFAULT_FETCH_MODE,pdo::FETCH_ASSOC);
                    
			$query = "
			SELECT 1 as valid
  		FROM employee_data
			where 1 = 1 and active <> 0 and employee_number = :employeeNumber and right(ssn,4) = :pass and active <> 0
			";
			try {
				$stm = $this->db->prepare($query);
				$stm->bindParam(":employeeNumber",$user);
				$stm->bindParam(":pass",$pass);
				$stm->execute();
				$data = $stm->fetchAll();
				if (count($data) > 0) {
					$this->valid = true;
					$this->user  = $user;
					$this->getTCParams();
					$this->setSession();
				}
			} catch (PDOException $e) {
				$this->valid = false;
			}
		}
	}
	
	private function getTCParams() {
		
		$query = "
			SELECT
				id eid
				,fname
				,lname
				,hire_hours
				,rhire_miles hire_miles
				,employee_number
				,timeClockType
				,MPHAlert
				,lunchAlert
				,mobileAllowed
				,milesAlert
				,position
				,category
			FROM
				employee_data
			WHERE
				employee_number = :user and active <> 0
		";
		
			try {
				$stm = $this->db->prepare($query);
				$stm->bindParam(":user",$this->user);
				$stm->execute();
				$data = $stm->fetch();
				if (count($data) > 0) {
					$this->tcParams = $data;
					$_SESSION['tc_eid']           = $data['eid'];
					$_SESSION['tc_fname']         = $data['fname'];
					$_SESSION['tc_lname']         = $data['lname'];
					$_SESSION['tc_hire_hours']    = $data['hire_hours'];
					$_SESSION['tc_hire_miles']    = $data['hire_miles'];
					$_SESSION['tc_timeClockType'] = $data['timeClockType'];
					$_SESSION['tc_MPHAlert']      = $data['MPHAlert'];
					$_SESSION['tc_lunchAlert']    = $data['lunchAlert'];
					$_SESSION['tc_mobileAllowed'] = $data['mobileAllowed'];
					$_SESSION['tc_milesAlert']    = $data['milesAlert'];
					$_SESSION['tc_position']      = $data['position'];
					$_SESSION['tc_category']      = $data['category'];
				}
			} catch (PDOException $e) {
				$this->valid = false;
			}
	}
	
	private function setSession() {
		$_SESSION['tc_valid'] = $this->valid;
		$_SESSION['tc_user']  = $this->user;
	}
	
	public function getEmployeeInformation () {
		
	}
	
	
	
}
?>