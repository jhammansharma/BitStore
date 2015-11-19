<?php if ( ! defined('DHS_DEFINES')) exit('Session is expired. Please do login again.');?>
<?php $store_key = $_SESSION['mystoreid'];?>
<table class="table table-stripped" style="margin-top:30px;">
	<tr>
		<th>S. No.</th>
		<th>Vendor Name</th>
        <th>BillNo.</th>
        <th>Total Amount</th> 
        <th>Pending Amount</th>
		<th>Payment Date</th>
        <th>stock Arrive Date</th>
        <th>&nbsp;</th>
	</tr>
	
	<?php
	
    $sql= "SELECT VP.ven_pay_id,VN.Name,VP.BillNo,VP.Amount,VP.PendiangAmount,VP.paymentDate,VP.date FROM  vendor_payment AS VP  
            INNER JOIN vendors AS VN ON VN.Id=VP.ven_id
            WHERE VN.store_key='".$store_key."'";
    
   	
    // ***** Pagination Work Start ******
		$pageSql = $sql;
		$pageResult = mysqli_query($con,$pageSql);
		
		$count = $pageResult->num_rows;
		if($count > 0){
		      $paginationCount = DhsHelper::getPagination($count);
              
		}
        
       // ***** Pagination Work End ******
		
		if(isset($_GET['page_id']) && !empty($_GET['page_id'])){
		   $page_id = $_GET['page_id'];
		}else{
		   $page_id = '0';
		}
		
		$pageLimit = PAGE_PER_NO * $page_id;
		$sql .= " limit $pageLimit,".PAGE_PER_NO;
		$result=mysqli_query($con,$sql);
		
        $n = 0;
        while($row=mysqli_fetch_assoc($result)){	
        
	?>
        <tr>
               <td><?php echo '#'.++$n;?></td>
                <td><?php echo $row['Name']; ?></td>
                <td><?php echo $row['BillNo']; ?></td>
                <td><?php echo $row['Amount']; ?></td>

            <?php 
            if($row['PendiangAmount']=="0"){
                echo '<td style="color:green;">';
            }
            else{
                echo '<td style="color:red;">';
            }
                echo $row['PendiangAmount'];
            echo "</td>"    
                ?>
                <td><?php echo $row['paymentDate']; ?></td>
                <td><?php echo $row['date']; ?></td>
                <td>

                    <button type="button" class="btn btn-primary vendor-paid-histroy" value="<?php echo $row['ven_pay_id']; ?>"  >View payments </button>
                    <button type="button" class="btn btn-info pay-vendor-bill"  value="<?php echo $row['ven_pay_id']."_".$row['PendiangAmount']; ?>">  Payment </button>
                	
                </td>
            </tr>
	<?php }?>
</table>
<?php if($count > 0):?>
 <div class="pagination text-center">
	<ul>
	    <?php 
	    for($i = 0; $i < $paginationCount; $i++):?>
	        <li class="<?php echo ($page_id == $i) ? 'active' : 'disabled';?>">
	          <a  href="<?php echo DHS_ROOT;?>index.php?view=paid_bills&menu=billing&page_id=<?php echo $i;?>">
	              <?php echo $i + 1;?>
	          </a>
	    </li>
	    <?php endfor;?>
	</ul>
</div>

<?php endif;?>


<!-- vendor bill payment and view payemnt  -->

<div id="vendor-bill" class="modal hide fade" tabindex="-1" data-width="760">
    <div class=" btn btn-warning vendor-bill-header" style="background-color: #0480be; margin-left: 40%;">
    </div>
    <div class="vendor-bill-body">
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn  btn-primary " onclick="window.location.reload(true);">Close</button>
        <button type="button" class="btn  btn-warning vendor-payment-details">submit</button>
    </div>
</div>



<script type="text/javascript">

 // show vendor payment 
$('.pay-vendor-bill').click(function() {
    var data = $(this).val().split('_');
    var htm = '<div class="table-responsive"><table class="table table-bordered table-striped table-condensed vendor-payment-table"><tbody>';
            htm += '<tr><td>Pending Amount</td>';
            htm += '<td><b id="pending_amount">' + data[1] + '</b></td></tr>';
    htm += '<tr><td>Paid Amount</td><td><input type="number" value=""  id="paid_amount" style="height:30px;" placeholder="payment amount"/></td></tr>';
    htm += '<tr><td>Remark</td><td><input type="text" value=""  id="remark" style="height:30px;" placeholder="add Remark Here"/></td></tr>';
    htm += '</tbody></table>';
    htm  += '<div id="vendor-payemnt-status"> </div>'

    htm +='</div>';
    htm += '<input type="hidden" value="'+data[0]+'" id="ven_payment_id" />';   
    $('.vendor-bill-body').html(htm);
    $('.vendor-bill-header').html('<b>Vendor Payment</b>');
    $('#vendor-bill').modal('show');
});

// vendor Payment Dialog 
$('.vendor-payment-details').click( function(){
var flag=0;
var ven_pay_id=$('#ven_payment_id').val();
var pending_amount=$('#pending_amount').html().trim();
var pend_amount=parseFloat(pending_amount) > 0 ? parseFloat(pending_amount) : flag++; 
var paid_amount=$('#paid_amount').val();
var amount=parseFloat(paid_amount)>0 ? parseFloat(paid_amount):flag++;
var remark=$('#remark').val();

var newAmount=pend_amount-amount;
// chk payment amount is not greater than pending amount 
if(amount > pend_amount){
    flag++;
}
if(flag==0){

$.ajax({
type:'POST',
url:'admin/db/vendor_payment.php',
data:{
    id: ven_pay_id,
    pend_amount:pend_amount,
    paid_amount:amount,
    remark:remark
},

success:function(html){
    var result = JSON.parse(html);
                if (result['status'] == 'success') {
                $('#remark').val(''); // clear reamrk 
                $('#paid_amount').val('');  // clear paid amount 
                $('#pending_amount').html(newAmount);
            $('#vendor-payemnt-status').html('<p class="text-success text-center" > Payment Success  </p> ');
                            }
                            else {
            $('#vendor-payemnt-status').html('<p class="text-warning text-center"> payment fail </p> ');
                            }
}

});
} // if end 
else{
$('#vendor-payemnt-status').html('<p class="text-warning center text-center"> Please Enter Valid Amount  </p> ');
}
});



///// show vendor paid Histroy 


$('.vendor-paid-histroy').click(function(){

var Id=$(this).val(); // vendor payment Id 

$.ajax({

type:'POST',
url:'admin/db/vendor_payment_histroy.php',
data:{
    Id:Id
},
success:function(html){
var result=JSON.parse(html);
if(result['status']=='success'){

 $('.vendor-bill-body').html(result['data']);
    $('.vendor-bill-header').html('<b> Payment Histroy</b>');
    $('#vendor-bill').modal('show');
}else{

 $('.vendor-bill-body').html('<p class="text-warning text-center">NO DATA !!');
    $('.vendor-bill-header').html('<b> Payment Histroy</b>');
    $('#vendor-bill').modal('show');

}

}

});


});





</script>








