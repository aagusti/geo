<?php
$DEBUG = true;

include('config.php');
include('db.php');

# Turn off the warning reporting
if(!$DEBUG) {
	error_reporting(E_ERROR | E_PARSE);
}

session_start();
if (!isset($_SESSION['logged'])) {
      $_SESSION['logged'] = 0;
}

$logged = $_SESSION['logged'];
$msg = "";
if ($_GET){
    if ($_GET['logout']==1){
        $_SESSION['logged'] = 0;
    }
}
if ( $_SESSION['logged']==0 ){
    // # cek apakah request submit?
    #print_r($_POST);
    if ($_POST){
        $uid = $_POST['userid'];
        $pwd = $_POST['passwd']; 
        if ($uid!='' and $pwd!=''){
            $sql = "SELECT * FROM users WHERE user_name = '$uid'";
            $qry = $db->prepare($sql);
            $qry->execute();
            if ($qry){
                $row = $qry->fetch(PDO::FETCH_OBJ);
                if ($row and $row->user_password==$pwd){
                    $msg = "Login Sukses";
                    $_SESSION['logged'] = 1;
                    header("Location: /");
                    die();
                }
            }
            //$qry->execute();
        }
        $msg = "Salah user id atau password";
    }
} else {
    header("Location: http://sig.tangselkota.org");
}
?>
    <form id="loginform" name="loginform" method="POST">
    <table>
        <tr>
        <td colspan="2">
            SIG LOGIN <?=$msg?>
        </td>
        </tr>
        <tr>
            <td>User ID</td>
            <td><input type="text" id="userid" name="userid"></td>
        </tr>
        <tr>
            <td>Password</td>
            <td><input type="password" id="passwd" name="passwd"></td>
        </tr>
        <tr>
        <td colspan="2">
            <input type="submit" value="Login" id="btnlogin" name="btnlogin">
            <input type="button" value="Batal" id=="btncancel" name="btnlogin">
        </td>
        </tr>
    </table>
    </form>
    
    