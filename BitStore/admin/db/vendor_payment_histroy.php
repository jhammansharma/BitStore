<?php
session_start();
include("../../config.php");

$store_id=$_SESSION['mystoreid'];

$Id=$_POST['Id'];
$htm="";

$sql= " SELECT DATE_FORMAT(VT.date,'%d %b %Y') AS 'date',VT.amount FROM vendor_tran AS VT WHERE VT.ven_pay_id='".$Id."' ";
$result=mysqli_query($con,$sql);

if($result && $result->num_rows > 0 )
{
$htm .='<div class="table-responsive"><table class="table table-bordered table-striped table-condensed vendor-payment-table">
<thead><th>Date </th> <th>Amount </th><tbody>';
while($row=mysqli_fetch_assoc($result)){
$htm .='<tr><td>'.$row['date'].'</td><td>'.$row['amount'].'</td></tr>';
}
$htm .='</tbody></table></div>';
	 $arr['status']='success';
	 $arr['data']=$htm;
	 echo json_encode($arr);
	 exit;
} // If end 




$arr['status']='fail';
echo json_encode($arr);
exit;


?>

