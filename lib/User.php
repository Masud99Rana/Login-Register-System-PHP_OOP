<?php 
	include_once 'Session.php';
	include 'Database.php';
class User{
	private $db;

	function __construct(){
		$this->db = new Database2();
	}
	public function userRegistration($data){
		$name 	  = $data['name'];
		$username = $data['username'];
		$email 	  = $data['email'];
		$password = $data['password'];

		$chk_email = $this->emailCheck($email);

		if ($name =="" OR $username=="" OR $email=="" OR $password=="") {
			$msg = "<div class='alert alert-danger'><strong>Error !</strong> Field must not be Empty</div>";
			return $msg;
		}
		if (strlen($name) < 3) {
			$msg = "<div class='alert alert-danger'><strong>Error !</strong> User Name is too Short!</div>";
			return $msg;
		}
		if (strlen($password) < 6) {
			$msg = "<div class='alert alert-danger'><strong>Error !</strong> Password is too Short!</div>";
			return $msg;			
		}elseif (preg_match('/[^a-z0-9_-]+/i', $username)) {
			$msg = "<div class='alert alert-danger'><strong>Error !</strong> User Name must only contain alphanumerical, dashes and underscores!</div>";
			return $msg;
		}
		if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
			$msg = "<div class='alert alert-danger'><strong>Error !</strong> Email address is not valid!</div>";
			return $msg;			
		}

		if ($chk_email == true) {
			$msg = "<div class='alert alert-danger'><strong>Error !</strong> The Email address already Exist!</div>";
			return $msg;			
		}

		$password = md5($data['password']);

		$sql = "INSERT INTO tble_user (name, username, email, password) VALUES(:name, :username, :email, :password)";
		$query = $this->db->pdo->prepare($sql);
		$query->bindValue(':name', $name);
		$query->bindValue(':username', $username);
		$query->bindValue(':email', $email);
		$query->bindValue(':password', $password);
		$result = $query->execute();
		if ($result){
			$msg = "<div class='alert alert-success'><strong>Success !</strong> Thank You, You have been registered.</div>";
			return $msg;
		}else{
			$msg = "<div class='alert alert-success'><strong>Sorry!</strong> There has been problem inserting your details.</div>";
			return $msg;
		}
	}
	public function emailCheck($email){
		$sql = "SELECT email FROM tble_user WHERE email= :email";
		$query = $this->db->pdo->prepare($sql);
		$query->bindValue(':email', $email);
		$query->execute();
		if ($query->rowcount() > 0) {
			return true;
		}else{
			return false;
		}
	}
	public function getLoginUser($email,$password){
		$sql = "SELECT * FROM tble_user WHERE email= :email AND password = :password LIMIT 1";
		$query = $this->db->pdo->prepare($sql);
		$query->bindValue(':email', $email);
		$query->bindValue(':password', $password);
		$query->execute();
		$result = $query->fetch(PDO::FETCH_OBJ);
		return $result;
	}
	public function userLogin($data){

		$email 	  = $data['email'];
		$password = md5($data['password']);

		$chk_email = $this->emailCheck($email);

		if ($email=="" OR $password=="") {
			$msg = "<div class='alert alert-danger'><strong>Error !</strong> Field must not be Empty</div>";
			return $msg;
		}
		if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
			$msg = "<div class='alert alert-danger'><strong>Error !</strong> Email address is not valid!</div>";
			return $msg;			
		}

		if ($chk_email == false) {
			$msg = "<div class='alert alert-danger'><strong>Error !</strong> The Email addres not Exist!</div>";
			return $msg;			
		}
		$result =$this->getLoginUser($email,$password);

		if ($result) {
			Session::init();
			session::set("login", true);
			session::set("id", $result->id);
			session::set("name", $result->name);
			session::set("username", $result->username);
			session::set("loginmsg","<div class='alert alert-success'><strong>success !</strong>You are loggedIn!</div>");
			header("Location: index.php");
		}else{
			$msg = "<div class='alert alert-danger'><strong>Error !</strong> Data not found</div>";
			return $msg;	
		}
	}

	public function getUserData(){
		$sql = "SELECT * FROM tble_user ORDER BY id DESC";
		$query = $this->db->pdo->prepare($sql);
		$query->execute();
		$result = $query->fetchAll();
		return $result;
	}
	public function getUserById($id){
		$sql = "SELECT * FROM tble_user WHERE id = :id LIMIT 1";
		$query = $this->db->pdo->prepare($sql);
		$query->bindValue(':id', $id);
		$query->execute();
		$result = $query->fetch(PDO::FETCH_OBJ);
		return $result;
	}

	public function updateUserData($id,$data){
		$name 	  = $data['name'];
		$username = $data['username'];
		$email 	  = $data['email'];



		if ($name =="" OR $username=="" OR $email=="") {
			$msg = "<div class='alert alert-danger'><strong>Error !</strong> Field must not be Empty</div>";
			return $msg;
		}
		
		
		$sql = "UPDATE tble_user set 
						name		=:name,
						username	=:username,
						email		=:email
						WHERE id	= :id";
		$query = $this->db->pdo->prepare($sql);
		$query->bindValue(':name', $name);
		$query->bindValue(':username', $username);
		$query->bindValue(':email', $email);
		$query->bindValue(':id', $id);
		$result = $query->execute();
		if ($result){
			$msg = "<div class='alert alert-success'><strong>Success !</strong> user data updated successfully.</div>";
			return $msg;
		}else{
			$msg = "<div class='alert alert-success'><strong>Sorry!</strong> user data not updated.</div>";
			return $msg;
		}
	}
	private function checkPassword( $id, $old_pass){
		$password = md5($old_pass);
		
		$sql = "SELECT password FROM tble_user WHERE id= :id AND password = :password";
		$query = $this->db->pdo->prepare($sql);
		$query->bindValue(':id', $id);
		$query->bindValue(':password', $password);
		$query->execute();
		if ($query->rowcount() > 0) {
			return true;
		}else{
			return false;
		}
	}	

	public function updatePassword($id,$data){
		$old_pass = $data['old_pass'];
		$new_pass = $data['password'];
		$chk_pass = $this->checkPassword($id, $old_pass); 
		

		if ($old_pass =="" OR $new_pass=="") {
			$msg = "<div class='alert alert-danger'><strong>Error !</strong> Field must not be Empty</div>";
			return $msg;
		}
	
		



			if ($chk_pass == false) {
				$msg = "<div class='alert alert-danger'><strong>Error !</strong> Old Password not exist</div>";
			return $msg;
			}
			if (strlen($new_pass) <2 ) {
				$msg = "<div class='alert alert-danger'><strong>Error !</strong> Old Password too short</div>";
			return $msg;
			}


			$password  = md5($new_pass);

			$sql = "UPDATE tble_user set 
					password  = :password
					WHERE id  = :id";
		$query = $this->db->pdo->prepare($sql);

		$query->bindValue(':password', $password);
		$query->bindValue(':id', $id);
		$result = $query->execute();

		if ($result){
			$msg = "<div class='alert alert-success'><strong>Success !</strong> user Password updated successfully.</div>";
			return $msg;
		}else{
			$msg = "<div class='alert alert-success'><strong>Sorry!</strong> user Password not updated.</div>";
			return $msg;
		}

	}
 }
?>