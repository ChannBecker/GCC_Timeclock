<?php
/**********************************************************************
  require_once( 'GCCL/Lib/class_EmailList_v5.php3' );

  // Load Email List
  $EML = new EmailList();
  $list = $EML->getList('listname');

**********************************************************************/

require_once( 'gccl/db/pdc_conn_gccl_data.php3' );
require_once( 'Lib/class_fncResult.php3' );

class EmailList {
  private $db;
  private $key;
  private $group;
  
  /**
   * __construct
   *
   * @return void
   */
  public function __construct() {
    try {
      $this->db = new PDO(PDO_GCCL_DATA_CONN,PDO_GCCL_DATA_USER,PDO_GCCL_DATA_PASS);
      $this->db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
      $this->db->setAttribute(pdo::ATTR_DEFAULT_FETCH_MODE,pdo::FETCH_ASSOC);
  
  
    } catch (PDOException $e) {
      echo "<pre>". print_r($e,1). "</pre>";
      die(__LINE__. " :: FATAL ERROR Instanciating EmailList");
    } 
  }

  public function __call( $name=null, $arguments ) {
    if ( $name == 'GetAddresses' ) {
        return $this->getList($arguments[0]);
    }
  }

  public function addToQueue($groupID=null, $subject=null, $message=null) {
    
  }

  /**
   * getList
   *
   * @param  mixed $key
   * @return void
   */
  public function getList( $key=null ) {
    $result = new fncResult();
    if ( !is_null( $key ) ) {
        echo "KEY: ". print_r($key,1). "<br>";
        $query = "
            SELECT
                EU.userID,
                EG.groupName, 
                EU.FName, 
                EU.LName, 
                EU.emailAddress
            FROM 
                emailgroup EG 
                INNER JOIN emaillink EL ON EL.groupID = EG.groupID AND EG.groupkey = :Key
                INNER JOIN emailuser EU ON EU.userID = EL.userid
        ";
        try {
            $stm = $this->db->prepare( $query );
            $stm->bindParam( ':Key', $key );
            $stm->execute();
            $data = $stm->fetchALL();

            echo "data: ". print_r($data,1);

            if ( !empty( $data ) ) {
                $result->result = true;
                foreach ( $data as $k=>$d ) {
                    $result->data[] = array(
                        'userID'=>$d['userID'],
                        'FName'=>$d['FName'],
                        'LName'=>$d['LName'],
                        'EMail'=>$d['emailAddress']
                    );
                }
            }
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = print_r( $e, 1 );
            $result->misc = 'Line: __LINE__';
        }
        
    }
    return $result;
  }
  
  /**
   * isMemberByUserID
   *
   * @param  mixed $groupID
   * @param  mixed $userID
   * @return void
   */
  public function isMemberByUserID( $groupID=null, $userID=null ) {
    $result = new fncResult();

    if ( !is_null($groupID) && !is_null($userID) ) {
        $query = "
        SELECT
            EU.userID
        FROM 
            emailgroup EG 
            INNER JOIN emaillink EL ON EL.groupID = EG.groupID AND EL.groupID = :groupID
            INNER JOIN emailuser EU ON EU.userID = EL.userid
        ";
        try {
            $stm = $this->db->prepare($query);
            $stm->bindParam(':groupID',$groupID,);
            $stm->bindParam(':userID',$userID,);
            $stm->execute();
            $data = $stm->fetch();
        
            if ( !empty( $data ) ) {
                $result->result = true;
                $result->data = $data;
            }
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = print_r($e,1);
            $result->misc = 'Line: __LINE__';
        }
    }
    return $result;
  }
  
  /**
   * getAllUsers
   *
   * @return void
   */
  public function getAllUsers( ) {
    $result = new fncResult();
    $query = "
        SELECT
            EU.userID,
            EU.FName, 
            EU.LName, 
            EU.emailAddress,
            CONCAT(EU.FName, ' ', EU.LName) FullName
        FROM 
            emailuser EU
    ";
    try {
        $stm = $this->db->prepare( $query );
        $stm->execute();
        $data = $stm->fetchALL();
        
        if ( !empty( $data ) ) {
            $result->result = true;
            foreach ( $data as $k=>$d ) {
                $result->data[] = array(
                    'userID'=>$d['userID'],
                    'FName'=>$d['FName'],
                    'LName'=>$d['LName'],
                    'EMail'=>$d['emailAddress'],
                    'FullName'=>$d['FullName']
                );
            }
        }
    } catch (PDOException $e) {
        $result->error = true;
        $result->error_desc = print_r( $e, 1 );
        $result->misc = 'Line: __LINE__';
    }
        
    return $result;
  }
  
  /**
   * getGroupMembersByID
   *
   * @param  mixed $groupID
   * @return void
   */
  public function getGroupMembersByID( $groupID=null ) {
    $result = new fncResult();
    if ( !is_null( $groupID ) ) {
        $result->data2 = $groupID;

        $query = "
            SELECT
                EU.userID,
                EG.groupName, 
                EU.FName, 
                EU.LName, 
                EU.emailAddress,
                EG.groupID
            FROM 
                emailgroup EG 
                INNER JOIN emaillink EL ON EL.groupID = EG.groupID AND EL.groupID = :groupID
                INNER JOIN emailuser EU ON EU.userID = EL.userid
        ";
        try {
            $stm = $this->db->prepare( $query );
            $stm->bindParam( ':groupID', $groupID );
            $stm->execute();
            $data = $stm->fetchALL();
            
            if ( !empty( $data ) ) {
                $result->result = true;
                foreach ( $data as $k=>$d ) {
                    $result->data[] = array(
                        'userID'=>$d['userID'],
                        'FName'=>$d['FName'],
                        'LName'=>$d['LName'],
                        'EMail'=>$d['emailAddress'],
                        'groupID'=>$d['groupID']
                    );
                }
            }
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = print_r( $e, 1 );
            $result->misc = 'Line: __LINE__';
        }
        
    }
    return $result;
  }
  
  /**
   * getGroupByID
   *
   * @param  mixed $id
   * @return void
   */
  public function getGroupByID( $id = null ) {
    $result = new fncResult();
    if ( !is_null( $id ) ) {
        $result->data2 = $id;

        $query = "
            SELECT
                EG.groupID,
                EG.groupName,
                EG.groupKey
            FROM 
                emailgroup EG
            WHERE
                EG.GroupID = :gID
        ";
        try {
            $stm = $this->db->prepare( $query );
            $stm->bindParam( ':gID', $id );
            $stm->execute();
            $data = $stm->fetchALL();
            
            if ( !empty( $data ) ) {
                $result->result = true;
                foreach ( $data as $k=>$d ) {
                    $result->data[] = array(
                        'groupID' => $d['groupID'],
                        'groupName' => $d['groupName'],
                        'groupKey' => $d['groupKey']
                    );
                }
            }
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = print_r( $e, 1 );
            $result->misc = 'Line: __LINE__';
        }
        
    }
    return $result;
  }
    
  /**
   * getGroups
   *
   * @return void
   */
  public function getGroups( ) {
    $result = new fncResult();
        $query = "
            SELECT
                EG.*
            FROM 
                emailgroup EG
            ORDER BY
                EG.groupName
        ";
        try {
            $stm = $this->db->prepare( $query );
            $stm->execute();
            $data = $stm->fetchALL();
            
            if ( !empty( $data ) ) {
                $result->result = true;
                foreach ( $data as $k=>$d ) {
                    $result->data[] = array(
                        'groupID' => $d['groupID'],
                        'groupName' => $d['groupName'],
                        'groupKey' => $d['groupKey']
                    );
                }
            }
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = print_r( $e, 1 );
            $result->misc = 'Line: __LINE__';
        }
        
    return $result;
  }
  
  /**
   * getGroupByKey
   *
   * @param  mixed $key
   * @return void
   */
  public function getGroupByKey( $key = null ) {
    $result = new fncResult();
    if ( !is_null( $key ) ) {
        $result->data2 = $key;

        $query = "
            SELECT
                EG.groupID,
                EG.groupName,
                EG.groupKey
            FROM 
                emailgroup EG
            WHERE
                EG.GroupKey = :Key
        ";
        try {
            $stm = $this->db->prepare( $query );
            $stm->bindParam( ':Key', $key );
            $stm->execute();
            $data = $stm->fetchALL();
            
            if ( !empty( $data ) ) {
                $result->result = true;
                foreach ( $data as $k=>$d ) {
                    $result->data[] = array(
                        'groupID' => $d['groupID'],
                        'groupKey' => $d['groupKey'],
                        'groupName' => $d['groupName']
                    );
                }
            }
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = print_r( $e, 1 );
            $result->misc = 'Line: __LINE__';
        }
        
    }
    return $result;
  }
  
  /**
   * getKeyByGroupID
   *
   * @param  mixed $groupID
   * @return void
   */
  public function getKeyByGroupID( $groupID = null ) {
    $result = new fncResult();
    if ( !is_null( $groupID ) ) {
        $result->data2 = $groupID;

        $query = "
            SELECT
                EG.groupKey,
                EG.groupID,
                EG.groupName
            FROM 
                emailgroup EG
            WHERE
                EG.GroupIUD = :groupID
        ";
        try {
            $stm = $this->db->prepare( $query );
            $stm->bindParam( ':groupID', $groupID );
            $stm->execute();
            $data = $stm->fetchALL();
            
            if ( !empty( $data ) ) {
                $result->result = true;
                foreach ( $data as $k=>$d ) {
                    $result->data[] = array(
                        'groupID' => $d['groupID'],
                        'groupKey' => $d['groupKey'],
                        'groupName' => $d['groupName']
                    );
                }
            }
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = print_r( $e, 1 );
            $result->misc = 'Line: __LINE__';
        }
        
    }
    return $result;
  }
  
  /**
   * getUserByID
   *
   * @param  mixed $id
   * @return void
   */
  public function getUserByID( $id = null ) {
    $result = new fncResult();
    if ( !is_null( $id ) ) {
        $result->data2 = $id;

        $query = "
            SELECT
                EU.userID,
                EU.lname,
                EU.fname,
                EU.emailAddress
            FROM 
                emailUser EU
            WHERE
                EU.userID = :uID
        ";
        try {
            $stm = $this->db->prepare( $query );
            $stm->bindParam( ':uID', $id );
            $stm->execute();
            $data = $stm->fetchALL();
            
            if ( !empty( $data ) ) {
                $result->result = true;
                foreach ( $data as $k=>$d ) {
                    $result->data[] = array(
                        'userID' => $d['userID'],
                        'lname' => $d['lname'],
                        'fname' => $d['fname'],
                        'emailAddress' => $d['emailAddress']
                    );
                }
            }
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = print_r( $e, 1 );
            $result->misc = 'Line: __LINE__';
        }
        
    }
    return $result;
  }
  
  /**
   * addUser
   *
   * @param  mixed $fname
   * @param  mixed $lname
   * @param  mixed $emailAddress
   * @return void
   */
  public function addUser( $fname=null, $lname=null, $emailAddress=null ) {
    $result = new fncResult();
    if ( !is_null( $fname ) && !is_null( $lname ) && !is_null( $emailAddress ) ) {
        $query = 
        "
        INSERT
            emailUser
            (lname, fname, emailAddress)
        VALUES
        (:lname, :fname, :emailAddress)
        ";
        try {
            $stm = $this->db->prepare($query);
            $stm->bindParam(":lname",$lname);
            $stm->bindParam(":fname",$fname);
            $stm->bindParam(":emailAddress",$emailAddress);
            $stm->execute();

            $insertID = $this->db->lastInsertID();
            if ( $insertID > 0 ) {
                $result->result = true;
                $result->data = $insertID;
            }
            
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = $e;
        }

    }
    return $result;
  }
  
  /**
   * addGroup
   *
   * @param  mixed $groupKey
   * @param  mixed $groupName
   * @return void
   */
  public function addGroup( $groupKey = null, $groupName=null ) {
    $result = new fncResult();
    if ( !is_null( $groupKey ) && !is_null( $groupName ) ) {
        $query = 
        "
        INSERT
            emailGroup
            (groupKey, groupName)
        VALUES
        (:groupKey, :groupName)
        ";
        try {
            $stm = $this->db->prepare($query);
            $stm->bindParam(":groupName",$groupName);
            $stm->bindParam(":groupKey",$groupKey);
            $stm->execute();

            $insertID = $this->db->lastInsertID();
            if ( $insertID > 0 ) {
                $result->result = true;
                $result->data = $insertID;
            }
            
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = $e;
        }

    }
    return $result;
  }
  
  /**
   * addLink
   *
   * @param  mixed $userID
   * @param  mixed $groupID
   * @return void
   */
  public function addLink( $userID = null, $groupID=null ) {
    $result = new fncResult();
    if ( !is_null( $userID ) && !is_null( $groupID ) ) {
        $query = 
        "
        INSERT
            emailLink
            (userID, groupID)
        VALUES
        (:userID, :groupID)
        ";
        try {
            $stm = $this->db->prepare($query);
            $stm->bindParam(":groupID",$groupID);
            $stm->bindParam(":userID",$userID);
            $stm->execute();

            $insertID = $this->db->lastInsertID();
            if ( $insertID > 0 ) {
                $result->result = true;
                $result->data = $insertID;
            }
            
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = $e;
        }

    }
    return $result;
  }
  
  /**
   * deleteLink
   *
   * @param  mixed $linkID
   * @return void
   */
  public function deleteLink( $linkID = null ) {
    $result = new fncResult();
    if ( !is_null( $linkID ) ) {
        $query = 
        "
        DELETE FROM
            emailLink
        WHERE
            linkID = :linkID
        ";
        try {
            $stm = $this->db->prepare($query);
            $stm->bindParam(":linkID",$linkID);
            $stm->execute();

            $affectedRows = $stm->rowCount();
            if ( $affectedRows > 0 ) {
                $result->result = true;
                $result->data = $affectedRows;
            }
            
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = $e;
        }

    }
    return $result;
  }
  
  /**
   * deleteUser
   *
   * @param  mixed $userID
   * @return void
   */
  public function deleteUser( $userID = null ) {
    $result = new fncResult();
    if ( !is_null( $userID ) ) {
        $query = 
        "
        DELETE FROM
            emailUser
        WHERE
            userID = :userID
        ";
        try {
            $stm = $this->db->prepare($query);
            $stm->bindParam(":userID",$userID);
            $stm->execute();

            $affectedRows = $stm->rowCount();
            if ( $affectedRows > 0 ) {
                $result->result = true;
                $result->data = $affectedRows;
            }
            
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = $e;
        }

    }
    return $result;
  }
  
  /**
   * deleteGroup
   *
   * @param  mixed $groupID
   * @return void
   */
  public function deleteGroupByID( $groupID = null ) {
    $result = new fncResult();
    if ( !is_null( $groupID ) ) {
        // Delete any associated links to group
        $removedLinks = $this->deleteFromGroupByGroupID( $groupID );

        // Delete Group
        $query = 
        "
        DELETE FROM
            emailGroup
        WHERE
            groupID = :groupID
        ";
        try {
            $stm = $this->db->prepare($query);
            $stm->bindParam(":groupID",$groupID);
            $stm->execute();

            $affectedRows = $stm->rowCount();
            if ( $affectedRows > 0 ) {
                $result->result = true;
                $result->data = $affectedRows;
                $result->result2 = $removedLinks->result;
                $result->data2 = $removedLinks->data;
            }
            
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = $e;
        }

    }
    return $result;
  }
  
  /**
   * deleteFromGroupByGroupID
   *
   * @param  mixed $groupID
   * @return void
   */
  public function deleteFromGroupByGroupID( $groupID=null ) {
    $result = new fncResult();
    if ( !is_null( $groupID ) ) {
        // Delete any associated links to group

        // Delete Group
        $query = 
        "
        DELETE FROM
            emailLink
        WHERE
            groupID = :groupID
        ";
        try {
            $stm = $this->db->prepare($query);
            $stm->bindParam(":groupID",$groupID);
            $stm->execute();

            $affectedRows = $stm->rowCount();
            if ( $affectedRows > 0 ) {
                $result->result = true;
                $result->data = $affectedRows;
            }
            
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = $e;
        }

    }
    return $result;

  }
  
  /**
   * removeFromGroupByGroupID
   *
   * @param  mixed $groupID
   * @return void
   */
  public function removeFromGroupByGroupID( $groupID = null ) {
    $result = new fncResult();
    if ( !is_null( $groupID ) ) {
        $query = 
        "
        DELETE FROM
            emailLink
        WHERE
            groupID = :groupID
        ";
        try {
            $stm = $this->db->prepare($query);
            $stm->bindParam(":groupID",$groupID);
            $stm->execute();

            $affectedRows = $stm->rowCount();
            if ( $affectedRows > 0 ) {
                $result->result = true;
                $result->data = $affectedRows;
            }
            
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = $e;
        }

    }
    return $result;
  }
    
  /**
   * removeFromGroupByGroupIDUserID
   *
   * @param  mixed $groupID
   * @param  mixed $userID
   * @return void
   */
  public function removeFromGroupByGroupIDUserID( $groupID = null, $userID = null ) {
    $result = new fncResult();
    if ( !is_null( $groupID ) && !is_null( $userID ) ) {
        $query = 
        "
        DELETE FROM
            emailLink
        WHERE
            groupID = :groupID
            and userID = :userID
        ";
        try {
            $stm = $this->db->prepare($query);
            $stm->bindParam(":groupID",$groupID);
            $stm->bindParam(":userID",$userID);
            $stm->execute();

            $affectedRows = $stm->rowCount();
            if ( $affectedRows > 0 ) {
                $result->result = true;
                $result->data = $affectedRows;
            }
            
        } catch (PDOException $e) {
            $result->error = true;
            $result->error_desc = $e;
        }

    }
    return $result;
  }


  
}

echo date('Y-m-d H:i:s');
ini_set("display_errors","on");
$EML = new EmailList();
$list = $EML->GetAddresses('TESTTEST','two','three');
echo "<pre>". print_r($list,1). "</pre>";
echo "done";
?>