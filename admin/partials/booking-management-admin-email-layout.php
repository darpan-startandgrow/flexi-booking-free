<?php
$dbhandler                 = new BM_DBhandler();
$plugin_path               = plugin_dir_url( __FILE__ );
$message                   = isset( $admin_email_message ) ? wp_kses_post( stripslashes( $admin_email_message ) ) : '';
$source                    = isset( $source ) ? $source : -1;
$mail_type                 = isset( $mail_type ) ? $mail_type : '';
$order_id                  = isset( $order_id ) ? $order_id : 0;
$mesage_header             = esc_html__( 'New Order Received', 'service-booking' );
$mesage_subheader          = esc_html__( 'You have received a new order.', 'service-booking' );
$booking_key               = $dbhandler->get_value( 'BOOKING', 'booking_key', $order_id, 'id' );
$negative_group_discount   = $dbhandler->get_global_option_value( 'negative_group_discount_' . $booking_key, 0 );
$header_class              = 'normal-header';
$total_discounted_infants  = 0;
$total_discounted_children = 0;
$total_discounted_adults   = 0;
$total_discounted_seniors  = 0;
$infants_age_from          = 0;
$children_age_from         = 0;
$adults_age_from           = 0;
$seniors_age_from          = 0;
$infants_age_to            = 0;
$children_age_to           = 0;
$adults_age_to             = 0;
$seniors_age_to            = 0;
$infants_total_discount    = 0;
$children_total_discount   = 0;
$adults_total_discount     = 0;
$seniors_total_discount    = 0;
$infants_total             = 0;
$children_total            = 0;
$adults_total              = 0;
$seniors_total             = 0;
$group_discount            = 0;
$coupon_discount           = 0;
$infants_discount_type     = 'positive';
$children_discount_type    = 'positive';
$adults_discount_type      = 'positive';
$seniors_discount_type     = 'positive';
$discount_type             = 'positive';

if ( $mail_type == 'new_request' ) {
	$mesage_header    = esc_html__( 'New Request Received', 'service-booking' );
	$mesage_subheader = esc_html__( 'You have received a new request.', 'service-booking' );
} elseif ( $mail_type == 'cancel_order' ) {
	$header_class     = 'red-header';
	$mesage_header    = esc_html__( 'Order is Cancelled', 'service-booking' );
	$mesage_subheader = esc_html__( 'An order has been cancelled. The order amount will be refunded within 2-3 working days if any.', 'service-booking' );
} elseif ( $mail_type == 'refund_order' ) {
	$header_class     = 'yellow-header';
	$mesage_header    = esc_html__( 'Order Amount is Refunded', 'service-booking' );
	$mesage_subheader = esc_html__( 'A cancelled order has been refunded.', 'service-booking' );
} elseif ( $mail_type == 'failed_order' ) {
	$header_class     = 'red-header';
	$mesage_header    = esc_html__( 'Order Failed', 'service-booking' );
	$mesage_subheader = esc_html__( 'An order has failed. We order amount will be refunded within 2-3 working days if any.', 'service-booking' );
} elseif ( $mail_type == 'approved_order' ) {
	$header_class     = 'green-header';
	$mesage_header    = esc_html__( 'Order is Confirmed', 'service-booking' );
	$mesage_subheader = esc_html__( 'An order has been confirmed.', 'service-booking' );
} elseif ( $mail_type == 'voucher_redeem' ) {
	$header_class     = 'pink-header';
	$mesage_header    = esc_html__( 'Gift voucher redeemed', 'service-booking' );
	$mesage_subheader = esc_html__( 'A recipient has redeemed a gift voucher successfully.', 'service-booking' );
}

if ( $mail_type == 'new_order' || $mail_type == 'approved_order' ) {
	$voucher_code      = $dbhandler->get_value( 'VOUCHERS', 'code', $order_id, 'booking_id' );
	$total_discount    = $dbhandler->get_value( 'BOOKING', 'disount_amount', $order_id, 'id' );
}

?>

<!DOCTYPE html>
<html>
<style>
   body {
    font-family: Arial, sans-serif;
    background: #f1f1f1;
   }

   .container {
      width: 100%;
      max-width: 600px;
      margin: 0 auto;
      background: #fff;
      border-collapse: collapse;
   }

   .header {
      width: 100%;
      padding: 10px;
      text-align: center;

   }

   .normal-header {
      background-color: #1f86bb;
   }

   .red-header {
      background-color:rgb(209, 32, 8);
   }

   .yellow-header {
      background-color:rgb(227, 213, 11);
   }

   .green-header {
      background-color:rgb(58, 113, 6);
   }

   .pink-header {
      background-color:rgb(228, 41, 175);
   }

   .header h1 {
      margin: 0;
      color: #ffffff;
      font-weight: normal;
   }

   .header p {
      margin: 0px;
      line-height: 1.8;
      font-size: 20px;
      color: #fff;
   }

   .order-details{
      width: 100%;
      padding: 10px;
   }

   .billing-shipping,
   .footer {
      width: 100%;
      padding: 20px;

   }

   .billing-shipping th {
      color: #555;
   }

   .order-details th,
   .order-details td,
   .billing-shipping td {
      border-top: 1px solid #e0e0e0;
      padding: 8px;
      text-align: left;
   }

   .billing-shipping th {
      text-align: left;
   }

   .order-details tr:first-child td {
      border-top: none;
   }

   .order-details p {
      font-size: 15px;
      line-height: 1.6;
      margin: 0px;
   }

   .subheading {
      font-size: 11px;
      color: #555;
      line-height: 1.5;
      width: 33%;
   }

   .subheading span {
      font-size: 13px;
      color: #444;
   }

   .subtotal {
      font-size: 14px;
   }

   .noborder td {
      border: none !important;
      padding-top: 4px;
      padding-bottom: 4px;
      font-size: 14px;
   }

   .addresstext {
      line-height: 1.5;
      color: #555;
      padding-left: 0px !important;
   }

   .discountbox {
      border-collapse: collapse;
   }

   .discount {
      background-color: #efefef;
      padding: 20px;
      text-align: center;
      margin-top: 20px;
   }

   .discountheading {
      color: #555;
      font-size: 18px;
      margin-bottom: 0px;
      font-weight: bold;
   }

   .discountheading span {
      color: #d32f2f;
   }

   .discount a {
      display: inline-block;
      padding: 15px 20px;
      background-color: #fff;
      color: #d32f2f;
      font-weight: bold;
      text-decoration: none;
      margin-top: 10px;
      border: 1px dashed #555;
      width: 90%;
      border-radius: 10px;
   }

   .discountvalue {
      color: #30a7d2;
   }

   .shopnowbtn {
      display: inline-block;
      padding: 15px 20px;
      background-color: #515050;
      border: 1px solid #515050;
      color: #fff;
      font-weight: bold;
      text-decoration: none;
      margin-top: 10px;
      width: 90%;
      border-radius: 10px;
   }

   .thankstext {
      font-size: 13px;
      line-height: 1.5;
      color: #555;
   }

   .footer {
      font-size: 13px;
      color: #555;
      text-align: center;

   }

   .copyright {
      text-align: center;
      font-weight: bold;
      margin-bottom: 15px;
      font-size: 12px;
   }

   .privacytext {
      font-size: 14px;
      font-weight: bold;
      color: #555;
      text-align: left;
      padding-top: 15px;
   }

   .termstext {
      font-size: 14px;
      font-weight: bold;
      padding-top: 15px;
      text-align: right;
   }

   .footer a {
      color: #555;
      text-decoration: none;
      margin: 0 15px;

   }

   .billing-shipping-notification{
    padding: 20px;
    border: 1px solid #ffb7b7 !important;
    margin: 28px !important;
    width: 96%;
    margin: auto !important;
    margin-top: 20px !important;
    border-radius: 8px;
    background: #fff7f7;
    text-align: left;
    margin-bottom: 30px !important;
    border-collapse: initial !important;
   }

   .billing-shipping-notification th{
      padding-left: 0px;
      padding-top: 0px;
      padding-bottom: 16px;
   }

      .billing-shipping-notification .addresstext-notic{
      padding-bottom: 5px !important;
   }

   .billing-shipping-notification .addresstext-notic .fa-hand-o-right{
      margin-right: 4px;
      font-size: 16px;
   }

   .billing-shipping-notification .fa-exclamation-triangle{
      color: #ff3030;
      font-size: 20px;
   }

   .negative_discount {
      color: #FF5733;
      font-weight: bold;
   }

   .positive_discount,
   .postive_price_module_discount {
      color: #27AE60;
      font-weight: bold;
   }
</style>

   <body>
      <table class="container">
         <tr>
            <td>
               <table class="header <?php echo esc_html( $header_class ); ?>">
                  <tr>
                     <td>
                        <p><?php echo esc_html( $mesage_header ); ?></p>
                     </td>
                  </tr>
               </table>
               <?php if ( isset( $voucher_code ) && !empty( $voucher_code ) ) { ?>
                  <table class="order-details">
                     <tr>
                        <td>
                           <p><?php echo sprintf( esc_html__( 'This is a gift and contains a gift voucher - %s', 'service-booking' ), esc_html( $voucher_code ) ); ?></p>
                        </td>
                     </tr>
                  </table>
               <?php } ?>
               <!-- <table class="order-details">
                  <tr>
                     <td colspan="4">
                        <p><?php echo esc_html( $mesage_subheader ); ?></p>
                     </td>
                  </tr>
               </table> -->
               <table class="billing-shipping noborder">
                  <tr>
                     <td class="addresstext">
                        <?php echo wp_kses_post( $message ); ?>
                     </td>
                  </tr>
               </table>
               <?php
				?>
               <!-- <table class="footer">
                  <tr>
                     <td colspan="2" class="copyright"><img src="<?php echo esc_url( $plugin_path . 'images/logo.png' ); ?>" style="width:200px;"/><br/><?php esc_html_e( 'Copyrights Reserved ', 'service-booking' ); ?> &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?></td>
                  </tr>
               </table> -->
            </td>
         </tr>
      </table>
   </body>
</html>
