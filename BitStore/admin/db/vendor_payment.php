<?php
session_start();
include("../../config.php");

$store_id=$_SESSION['mystoreid'];

$Id=$_POST['id'];
$pend_amount=$_POST['pend_amount'];
$paid_amount=$_POST['paid_amount'];
$remark=$_POST['remark'];
$amount=$pend_amount-$paid_amount;

// UPDATING PEND AMOUNT 
$sql= " UPDATE `vendor_payment` SET `PendiangAmount` ='".$amount."'  WHERE `ven_pay_id`='".$Id."' ";

if(mysqli_query($con,$sql))
{
// INSERT INTO VENDOR TRANS

$query="INSERT INTO `vendor_tran`(`van_tran_id`, `ven_pay_id`, `amount`, `date`, `remark`) 
	VALUES (null,'".$Id."', '".$paid_amount."' ,'".date('Y-m-d')."','".$remark."')";
$res=mysqli_query($con,$query);

if($res){
	 $arr['status']='success';
	 echo json_encode($arr);
	 exit;
}
}


$arr['status']='fail';
echo json_encode($arr);
exit;


?>

