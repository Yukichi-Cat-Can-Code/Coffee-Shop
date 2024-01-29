<?php

    require "../includes/header.php";
    require "../config/config.php";
    require "../auth/not-access.php";
?>
<?php
	if(!isset($_SERVER['HTTP_REFERER'])){
		header("location:".APPURl."");
		exit;
	}
    if(!isset($_SESSION['payable_total_cost'])){
        header("location:".APPURl." ");
    }
    $payable_total_cost = $_SESSION['payable_total_cost'];
    $user_id = $_SESSION['user_id'];

?>

    <div class="container mt-5">
    <div class="row">
      <div class="col-md-6 offset-md-3 mt-5">
      <h2 class='text-center mt-5'>Pay with PayPal</h2>
        <!-- Replace "test" with your own sandbox Business account app client ID -->
        <script src="https://www.paypal.com/sdk/js?client-id=test&currency=USD"></script>
        <!-- Set up a container element for the button -->
        <div id="paypal-button-container"></div>
        
        <script>

            paypal.Buttons({
            // Sets up the transaction when a payment button is clicked
            createOrder: (data, actions) => {
                return actions.order.create({
                purchase_units: [{
                    amount: {
                    value: '<?php echo $payable_total_cost; ?>' // Can also reference a variable or function
                    }
                }]
                });
            },
            // Finalize the transaction after payer approval
            onApprove: (data, actions) => {
                return actions.order.capture().then(function(orderData) {
            
                window.location.href='../index.php';
                });
            }
            }).render('#paypal-button-container');
        </script>
      </div>
    </div>
  </div>





<?php require "../includes/footer.php"; ?>