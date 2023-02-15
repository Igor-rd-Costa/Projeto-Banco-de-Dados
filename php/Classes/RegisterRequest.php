<?php 
class RegisterRequest
{
    public $RegisterStatus = false;
    public $LoginRequest = array("usernameLogin" => null, "passwordLogin" => null);
    protected $Connection;
    protected $FirstName;
    protected $LastName;
    protected $Email;
    protected $Username;
    protected $Password;
    protected $RegisterError;
    
    function __construct(array $UserData)
    {
            $this->Connection = SQL_Connect('db_users');
            if(
            $this->ValidateNames($UserData["REG_FirstName"], $UserData["REG_LastName"]) &&
            $this->ValidateEmail($UserData['REG_Email']) &&
            $this->ValidateUsername($UserData['REG_Username']) &&
            $this->ValidatePassword($UserData['REG_Password']))
            {
                $this->LoginRequest["usernameLogin"] = $UserData['REG_Username'];
                $this->LoginRequest["passwordLogin"] = $UserData['REG_Password'];
                $this->SendRegistrationRequest();
            }
    }
    
    private function ValidateUsername($Username)
    {
        if(!is_numeric($Username[0]) && !preg_match('/[^a-zA-Z0-9_]+/', $Username) && strlen($Username) >= 3)
        {
            $this->Username = $Username;
            return true;
        }
        else return false;
    }
    
    private function ValidatePassword($Password)
    {
        if(strlen($Password) >= 8)
        {
            $salt = sprintf('$2y$%02d$', 10) . bin2hex(random_bytes(32));
            $Password = crypt($Password, $salt) . "@" . $salt;
            $this->Password = $Password;
            return true;
        }
        else return false;
    }
    
    private function ValidateEmail($Email)
    {
        if(substr($Email, -strlen(".com")) === ".com" && strpos($Email, "@") != 0)
        {
            $this->Email = $Email;
            return true;
        }
        else { return false; }
    }

    private function ValidateNames($FirstName, $LastName)
    {
        if(!is_numeric($FirstName[0]) && !is_numeric($LastName[0]))
        {
            $this->FirstName = $FirstName;
            $this->LastName = $LastName;
            return true;
        }
        else return false;
    }

    private function SendRegistrationRequest()
    {
        $query = "CREATE USER '$this->Username'@'localhost' IDENTIFIED BY '$this->Password';";
        $query.= "CREATE DATABASE $this->Username;";
        $query.= "GRANT SELECT, INSERT, UPDATE, DELETE ON $this->Username.* to $this->Username@'localhost';";
        $query.= "INSERT INTO users (FirstName, LastName, Email, Username, Password) 
                  VALUES ('$this->FirstName', '$this->LastName', '$this->Email', '$this->Username', '$this->Password');";
        try 
        {
            mysqli_multi_query($this->Connection, $query);
            $this->RegisterStatus = true;
            
        }
        catch(Exception)
        {
            $this->RegisterStatus = false;
        }
    }
}
?>