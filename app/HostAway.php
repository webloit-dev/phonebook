<?php

namespace App;

use App\HelperTrait;

use PDO;
/**********************************************************************¦
            /\                                                         ¦
           /  \                                                        ¦
          /    \                                                       ¦
         /      \                                                      ¦
        /        \                                                     ¦
	   /          \                                                    ¦
	  /            \                                                   ¦
	 /              \                                                  ¦
	/    SIRMEKUS  	 \                                                 ¦
   /     Coded        \                                                ¦
  /                    \                                               ¦
 /                      \                                              ¦
/_______________________ \                                             ¦
Written By: SIRMEKUS                                                   ¦
copyright 2018                                                         !
                                                                    ¦
************************************************************************/

class HostAway
{
	use HelperTrait;
	
	public $pdo;
	public $mysqli;
	
	// These ones are initialised immediately after calling the class.
	public $dsn;
	public $db_server;
	public $db_user;
	public $db_password;
	public $db_name;
	//The name of database we'll be working with.
	//End of necessary initialisation.
	
	public $key;//this will be needed incase we wanna decrypt/encrypt in a table that involves the operation
	
	public $error_msg = "<div class='alert alert-danger alert-dismissable fade show'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'> &times; </button>Something went wrong. Please try again.</div>";
	
	public function __construct($engine="")
	{
		$formatted = cred();
		
		if(!empty($engine))
		{
			$this->db_engine = $engine;
		}
		else
		{
			$this->db_engine = $formatted["database"][0]["engine"];
		}
		
		//This key is for encryption and it's been initialised here so that inherited classes can use it when necessary.
		$this->key = $formatted["auth"][0]["encryption_key"];
		
		$this->db_server = $formatted["database"][0]["db_server"];
		
		$this->db_user = $formatted["database"][0]["username"];
		
		$this->db_password = $formatted["database"][0]["db_password"];
		
		$this->db_name = $formatted["database"][0]["databasename"];
		
		//Only one engine can be initialised now.
		//$this->start_engine($this->db_engine);
		$this->start_engine("mysqli");
		$this->start_engine("pdo");
	}
//=============================== END OF CONSTRUCTION FUNCTION ===========================================================================	
	
	public function start_engine($type)
	{
		switch($type)
		{
			case "mysqli":
			    if(empty($this->mysqli))
				{
					try 
		            {
						$this->mysqli = new \mysqli($this->db_server, $this->db_user, $this->db_password, $this->db_name);
                    }
		            catch (Exception $e ) 
		            {
						echo "<p class='alert alert-danger'>A network error was encountered. Please try again. Connection failed.</p>";
                        exit;
                    }
				}
				
				break;
				
			case "pdo":
			    if(empty($this->pdo))
		        {
		            $this->dsn = "mysql:host=".$this->db_server.";dbname=".$this->db_name."";//Used by PDO.
		 
		            //Attempt to connect to a database using PDO
		            try
		            {
						$this->pdo = new PDO($this->dsn, $this->db_user, $this->db_password, array(PDO::ATTR_PERSISTENT => true));
		            }
		            //if there's a connection error we do this.
		            catch (PDOException $e) 
		            {
						$utility = new Utility();
			            $utility->ajax($this->error_msg);
			            $_SESSION["error"] = $this->error_msg;
			            
						if(isset($_SERVER["HTTP_REFERER"]))
			            {
							header("Location: ".$_SERVER["HTTP_REFERER"]."");
			            }
			            else
			            {
							header("Location: /");
			            }
                    }
		        }
				break;
				
			default:
			    $this->start_engine($type="mysqli");
		}
	}
	
	
	//This can be called manually
	public function close_engine($type)
	{
		switch($type)
		{
			case "mysqli":
		        mysqli_close($this->mysqli);
				
				break;
				
			case "pdo":
		        $this->pdo = null;
				
				break;
				
			default:
			    //should never be
		}
	}
	
	
	public function __destruct()
	{
		mysqli_close($this->mysqli);
		$this->pdo = null;
	}

//=============================================== We utilise "prepared statement" here. ===================================================
        //1st Param: The template query.
		//2nd Param: The values to bind to the template.
		//3rd Param(optional): If set to true it means we're making a "select" query to fetch goods else we're just making an "insert"
		//query and don't expect any result.
        function prepare($query, $param, $fetch=false)
		{
			if(empty($this->pdo))
		    {
				$this->start_engine($type="pdo");
		    }
			//Attempt to start executing the query making sure all query is executed 
			try
			{
				$db = $this->pdo;
				$db->beginTransaction();
			    $sth = $db->prepare($query);
				$sth->execute($param);
				
			    //it means we want the result of this query so fetch it.
			    if($fetch==true)
			    {
					$result = $sth->fetchall();
				    $db->commit();
					//var_dump($result);exit;
				    return $result;
			     }
				 else
				 {
					 //At this junction our query was successful so we just return control to the script/page.
				     //No return value is sent here.
			         $db->commit();
			         return;
				 }
			}
			
			
			//At this point the query wasn't executed correctly so we roll back everything.
			catch (Exception $e) 
			{
				$db->rollBack();
				
				$utility = new Utility();
                //$utility->ajax(json_encode($db->errorInfo()));
				$utility->ajax($this->error_msg);
				$_SESSION["error"] = $this->error_msg;// $db->errorInfo();
				if(isset($_SERVER["HTTP_REFERER"]))
				{
					header("Location: ".$_SERVER["HTTP_REFERER"]."");
			    }
			    else
				{
					header("Location: /");
                }
		    }
		}
//=========================================== END OF PREPARED STATEMENT ====================================================================
        
		
		
//======================================= FOR MAKING MULTIPLE QUERIES USING PDO FOR OPTIMIZATION ========================================
        //This method create instances of PDO and its Statement Obj, prepares the query(thereby creating a query template), begins the 
		//transaction but doesn't commit it to the database yet. It returns the instances(described above) created so that the template 
        //can be used while substituting its parameter value as featured in a Prepared Statement query.		
		function multi_prepare($query)
		{
			//Attempt to start full operation here.
			try
			{	
				$db = $this->pdo;
				
				//We begin the trasanction and the prepared statement
				$db->beginTransaction();
			    $sth = $db->prepare($query);
				$obj = [$db,$sth];
				
				//the return value on success is an array with two values containing objects;
				//The PDO obj[0]; Shall be used to commit the values to the already prepared statement 
				//The Statement obj[1]: Shall be used to bind/execute the values to the prepared statement.
				return $obj;
			 }
			
			//At this point the query was executed correctly so we roll back everything.
			catch (Exception $e) 
			{
				$db->rollBack();
				$utility = new utility();
				//$utility->ajax(json_encode($db->errorInfo()));
				$utility->ajax($this->error_msg);
				$_SESSION["error"] = $this->error_msg;//$db->errorInfo();
				if(isset($_SERVER["HTTP_REFERER"]))
				{
					header("Location: ".$_SERVER["HTTP_REFERER"]."");
			    }
			    else
				{
					header("Location: /");
                }
            }
		}
//============================ END OF QUERY TEMPLATE FOR A MULTIPLE SQL QUERY OPERATION ON A DATABASE ====================================
		
		
		
//================================== FOR MAKING MULTIPLE EXECUTION OF QUERY TEMPLATE ======================================================
        //This method attempts to make a multiple execution of a query template created via PDO.
	    //1st arg: An  array containing instances as described in the method
		//2nd arg: When set to true it means we're done with the query running so we wanna commit it to the database. At this junction
		//the return value is either null or when the fourth parameter is set to true the it will be the result of a database query 
		//which will normally be of the "select" clause.
		//3rd arg: The values we wanna bind to the already created query that will be passed to either of the obj in the 1st arg. This is ignored if the 4th query is set to true.
		//4th arg: If specified it means we made a multi "select" query and so we need the result.
		function multi_execute($obj, $commit=false, $param=[], $fetch=false)
		{
			//the first parameter is an array with two values containing objects which must have been created via PDO.
			//The PDO obj[0]; Shall be used to commit the values to the already prepared statement 
			//The Statement obj[1]: Shall be used to bind/execute the values to the prepared statement.
			if($commit == true)
			{
				try
				{
					//It means the User expect a result so we fetch it. It is typically a "select" statement.
					if($fetch==true)
					{
						$result = $obj[1]->fetchall();
				        $obj[0]->commit();//the statement obj is used for the committance
				        return $result;
			         }
					 else
					 {
						 //This is typically an insert statement
					     $obj[0]->commit();
					     return;
					 }
				 }
			     catch (Exception $e)
				 {
					 $obj[0]->rollBack();
					 
					 $utility = new utility();
					 //$utility->ajax(json_encode($obj[1]->errorInfo()));
				     //$_SESSION["error"] = $obj[1]->errorInfo();
				     $utility->ajax($this->error_msg);
				     $_SESSION["error"] = $this->error_msg;
				     if(isset($_SERVER["HTTP_REFERER"]))
					 {
						 header("Location: ".$_SERVER["HTTP_REFERER"]."");
			         }
					 else
					 {
						 header("Location: /");
                     }
                }
			}
			else
			{
				//We keep adding without committing so that we can roll back when necessary. This ensures that all queries are entered successfully
				try
			    {
					$obj[1]->execute($param);
				    return;
			    }
			    catch (Exception $e) 
			    {
					$obj[0]->rollBack();
					
					$utility = new utility();
					//$utility->ajax(json_encode($obj[1]->errorInfo()));
				    //$_SESSION["error"] = $obj[1]->errorInfo();
					$utility->ajax($this->error_msg);
				    $_SESSION["error"] = $this->error_msg;
				    if(isset($_SERVER["HTTP_REFERER"]))
					{
						header("Location: ".$_SERVER["HTTP_REFERER"]."");
			        }
					else
					{
						header("Location: /");
                    }
                }
			}
		}
//================================== END OF MAKING MULTIPLE EXECUTION OF QUERY TEMPLATE =================================================
			
	

		
		
/*Function to query a database. Returns true if successful.
** It also has a second parameter that if specified will return the fetched data back to the caller 
*****************************************************************************************************************/
		function register($query, $bool=false)
		{
			if(empty($this->mysqli))
		    {
				$this->start_engine($type="mysqli");
		    }
			
		    $db = $this->mysqli; 
			$db->autocommit(FALSE);
			$result = $db->query($query);
			if($result)// if query is successful
			{
				$db->commit();
				if($bool==true)//it means we want the result of this query so fetch it.
				{
				    $item = $result->fetch_all(MYSQLI_ASSOC);
					return $item;
				    exit;
					
					if(!$item)// if we couldn't fetch the result for any reason(as requested) then execute this block
					{
						$utility = new utility();
						$utility->ajax($this->error_msg);
				        $_SESSION["error"] = $this->error_msg;
						
				        if(isset($_SERVER["HTTP_REFERER"]))
						{
							header("Location: ".$_SERVER["HTTP_REFERER"]."");
			            }
					    else
					    {
							header("Location: /");
                        }
						return;
					}
				}
				//when we return it means the query was successful. Normally this block is used for insertion.
				return;
			}
			else//if query was unsuccessful for any reason
			{
				//echo $db->connect_error;
				$db->rollback();
				$utility = new utility();
				$utility->ajax($this->error_msg);
				$_SESSION["error"] = $this->error_msg;
				if(isset($_SERVER["HTTP_REFERER"]))
				{
					header("Location: ".$_SERVER["HTTP_REFERER"]."");
			    }
				else
				{
					header("Location: /");
                }
    
				return;
			}
		}
//========================================================== END OF REGISTER FUNCTION ====================================================



        public function multi_queries($query,$bool=false)
		{
			//Multi-query doesn't work if a single query is in progress or has been used previously in the same connection context. To remediate this issue we have to close the global connection each time this function is called and then reopen the connection just incase the script isn't done using the connection.
			if(!empty($this->mysqli))
			{
				mysqli_close($this->mysqli);
			}
			$this->mysqli = new mysqli($this->db_server, $this->db_user, $this->db_password, $this->db_name);
		      
			//$this->mysqli->autocommit(FALSE);
			  
			$result = $this->mysqli->multi_query($query);
			  
			if($result)// if multi_query is successful
			{
				//$this->mysqli->commit();
				
				if($bool==true)//if set to true then the user needs the data or response
				{
					$res = [];
					do 
					{
						/* store first result set */
						if ($result = $this->mysqli->store_result()) 
						{
							while ($row = $result->fetch_all(MYSQLI_ASSOC))
							{
								$res []= $row;
							}
                            $result->free();
                        }
						 
						if ($this->mysqli->more_results()) 
						{
							//do nothing for now;
					    }
					} 
					 
					while ($this->mysqli->next_result());
					 
					return $res;
                } 
			}  
			else
			{
				echo $this->error_msg;
				exit;
			}
		}

        //Phone Numbers must be unique. If we encounter a number that already exists we stop execution of this script.
		public function CheckPhoneNumber($phone, $id="")
		{
			////////////////////////////////////////// The table(s) we'll work with in this script /////////////////////////////////////////////
            $phonebook = cred($index='tables', $key='phonebook');//$db->get_value($table_name="phonebook");
            ///////////////////////////////////////////////// END OF TABLE(s) //////////////////////////////////////////////////////////////////

			$param = [$phone];

			if(empty($id))
			{
				$query = "SELECT JSON_EXTRACT(data, '$.phone') AS phone FROM $phonebook WHERE JSON_EXTRACT(data, '$.phone') = ?";
			}
			else
			{
				$query = "SELECT JSON_EXTRACT(data, '$.phone') AS phone FROM $phonebook WHERE JSON_EXTRACT(data, '$.phone') = ? AND id != ?";

				$param []= $id;
			}

            $result = $this->prepare($query, $param, true);

            if(!empty($result))
			{
				(new Utility())->ajax("<p class='alert alert-danger'>Phone number already exists.</p>", $return=false);
            }
		}
}
?>