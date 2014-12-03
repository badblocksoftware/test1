<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */
/*
Template Name: FGUpdateAddress
*/
//sk_redirectnonadmin(); 

//include_once (dirname(__FILE__).'/fg_processing.php');
//include_once (dirname(__FILE__).'/fg_widgetuser.php');
//include_once (dirname(__FILE__).'/fg_widgetadmin.php');
include_once (dirname(__FILE__).'/fg_billing.php');
include_once (dirname(__FILE__).'/BTInclude.php');


// user needs to be logged in and have a Pro account
$userid = fg_redirectNonProUser();

global $g_blogurl;
global $g_FG_Pro_PlanID_ExVat;
global $g_FG_Pro_VAT_AddOnID;

//global $g_imagesfolder;

//$useremail = "davefrancis111@gmail.com";
global $user_email;
get_currentuserinfo();

 
session_start();

$cust = fg_getCustomerDetails($userid);

$doingupdate = false;

get_header();


if (array_key_exists("updateaddress",$_POST)) {
   // do the update at BT
   $doingupdate = true;
   $success = true;
 
   try {
      $result = Braintree_Address::update($cust->BT_UserID,$cust->BT_AddrID,array(
         'streetAddress'     => $_POST['street_address'],
         'extendedAddress'   => $_POST['extended_address'],
         'locality'          => $_POST['locality'],
         'region'            => $_POST['region'],
         'postalCode'        => $_POST['postal_code'],
         'countryCodeAlpha2' => $_POST['countryCodeAlpha2']
      ));

      if ($result->success != 1) 
         $success = false;
   } catch ( Exception $e ) {
        $success = false;
   }
   

//   $success = false; //TEST

   if ($success) {

      // BT address succeeeded, so update the customer country in our DB
      $cust->Country = $_POST['countryCodeAlpha2'];
      fg_updateCustomer($cust);

      fg_updateCustomerVAT($cust, $cust->Country);
   }
   else
   {
      // update failed. log the error
      fg_addBillingError("Updating address","addr",$userid,$cust->BT_UserID,null);
   }

}
else
{
   // get address details from BT
   $success = true;

   try {
      $addr = Braintree_Address::find($cust->BT_UserID,$cust->BT_AddrID);

      $subs = fg_getActiveSubscriptions($cust->UserID);
   }
   catch (Exception $e)
   {
      $success = false;
      fg_addBillingError("Retrieving address","addr",$userid,$cust->BT_UserID,null);
   }
}


// if there ever was a validated VAT number, we lose that and start again when the form is reloaded
if (isset($_SESSION['valid_vat_number'])) {
   unset($_SESSION['valid_vat_number']);
}

 
//get_header(); 


?>

</div>

<?php if ($doingupdate) {
   
 //  printf("addr=%s<br>",$cust->BT_AddrID);

   if ($success) { ?>
      <div id="fg-header2">
         <h3>Your Address was updated</h3>
      </div>
      <div id="fg-content">
         <a href="<?php echo $g_blogurl ?>/myaccount/">Back to your Account</a><br><br>
      </div>
   <?php } else { ?>
      <div id="fg-header2">
         <h3>The update failed</h3>
      </div>
      <div id="fg-content">
         Probably best to try again. If it fails repeatedly, contact us at support@feedgrabbr.com<br><br>
         The reason given (this would be useful if you contact us) <?php echo $result->address->status ?><br><br>
         <a href="<?php echo $g_blogurl ?>/myaccount/">Back to your Account</a><br><br>
      </div>
   <?php  } ?>
<?php } else {
// show the address edit page
 //  print_r($subs);
 //  printf("userid=%s<br>",$cust->BT_UserID);
?>

<div id="fg-header2">
   <h1>Your Billing Address</h1>
</div>
<?php if (!$success) { ?>
   <div id="fg-content">
      <div>
         <span>There was a problem retrieving your billing address. ID=<?php $cust->BT_AddrID; ?>. Better let us know about it at support@feedgrabbr.com</span>
      </div>
   </div>
<?php } else { ?>
<div id="fg-content">
<div>
<div style="text-align:center"><br>Edit your billing address using the form below.<br><br></div>

  <form id='payment-form' action='' method='POST' autocomplete="off">
      <input type="hidden" name="updateaddress" value="">
      <div>
   		<div class="pf-col-m">
   			<label for='billing_street_address'>Street Address</label><br>
   			<input class="reqfield" type='text' name='street_address' id='billing_street_address' value="<?php echo $addr->streetAddress; ?>"></input><br>
            <div class="sep1"></div>
         </div>
         
         <div class="clearall"></div>

         <div class="pf-col-m">
           <!--<label for='billing_extended_address'>Extended Address</label>-->
   			<input type='text' name='extended_address' id='billing_extended_address' value="<?php echo $addr->extendedAddress; ?>"></input>
         </div>
         <span class="sublabel-r">(optional)</span>

   		<div class="clearall"></div><br>
   		<div class="pf-col-s">
   			<label for='billing_locality'>Town or City</label><br>
   			<input class="reqfield" type='text' name='locality' id='billing_locality' value="<?php echo $addr->locality; ?>"></input>
   		</div>
   		<div class="pf-col-s">
   			<label for='billing_region'>State or Region</label><br>
   			<input type='text' name='region' id='billing_region' value="<?php echo $addr->region; ?>"></input>
   		</div>
   		<div class="pf-col-s">
   			<label for='billing_postal_code'>Post Code</label><br>
   			<input class="reqfield" type='text' name='postal_code' id='billing_postal_code' value="<?php echo $addr->postalCode; ?>"></input>
   		</div>
   		<div class="clearall"></div><br>
   		<label for='country-list'>Country</label><br>
   		<select id="country-list" name="countryCodeAlpha2" tabindex="10"><option value="AF">Afghanistan</option><option value="AL">Albania</option><option value="DZ">Algeria</option><option value="AS">American Samoa</option><option value="AD">Andorra</option><option value="AO">Angola</option><option value="AI">Anguilla</option><option value="AQ">Antarctica</option><option value="AG">Antigua and Barbuda</option><option value="AR">Argentina</option><option value="AM">Armenia</option><option value="AW">Aruba</option><option value="AU">Australia</option><option value="AT">Austria</option><option value="AZ">Azerbaijan</option><option value="BS">Bahamas</option><option value="BH">Bahrain</option><option value="BD">Bangladesh</option><option value="BB">Barbados</option><option value="BY">Belarus</option><option value="BE">Belgium</option><option value="BZ">Belize</option><option value="BJ">Benin</option><option value="BM">Bermuda</option><option value="BT">Bhutan</option><option value="BO">Bolivia</option><option value="BA">Bosnia and Herzegovina</option><option value="BW">Botswana</option><option value="BV">Bouvet Island</option><option value="BR">Brazil</option><option value="VG">British Virgin Islands</option><option value="BN">Brunei</option><option value="BG">Bulgaria</option><option value="BF">Burkina Faso</option><option value="BI">Burundi</option><option value="KH">Cambodia</option><option value="CM">Cameroon</option><option value="CA">Canada</option><option value="CV">Cape Verde</option><option value="KY">Cayman Islands</option><option value="CF">Central African Republic</option><option value="TD">Chad</option><option value="CL">Chile</option><option value="CN">China</option><option value="CX">Christmas Island</option><option value="CC">Cocos Islands</option><option value="CO">Colombia</option><option value="KM">Comoros</option><option value="CG">Congo - Brazzaville</option><option value="CD">Congo - Kinshasa</option><option value="CK">Cook Islands</option><option value="CR">Costa Rica</option><option value="HR">Croatia</option><option value="CY">Cyprus</option><option value="CZ">Czech Republic</option><option value="DK">Denmark</option><option value="DJ">Djibouti</option><option value="DM">Dominica</option><option value="DO">Dominican Republic</option><option value="EC">Ecuador</option><option value="EG">Egypt</option><option value="SV">El Salvador</option><option value="GQ">Equatorial Guinea</option><option value="ER">Eritrea</option><option value="EE">Estonia</option><option value="ET">Ethiopia</option><option value="FK">Falkland Islands</option><option value="FO">Faroe Islands</option><option value="FJ">Fiji</option><option value="FI">Finland</option><option value="FR">France</option><option value="GF">French Guiana</option><option value="PF">French Polynesia</option><option value="GA">Gabon</option><option value="GM">Gambia</option><option value="GE">Georgia</option><option value="DE">Germany</option><option value="GH">Ghana</option><option value="GI">Gibraltar</option><option value="GR">Greece</option><option value="GL">Greenland</option><option value="GD">Grenada</option><option value="GP">Guadeloupe</option><option value="GU">Guam</option><option value="GT">Guatemala</option><option value="GN">Guinea</option><option value="GW">Guinea-Bissau</option><option value="GY">Guyana</option><option value="HT">Haiti</option><option value="HN">Honduras</option><option value="HK">Hong Kong</option><option value="HU">Hungary</option><option value="IS">Iceland</option><option value="IN">India</option><option value="ID">Indonesia</option><option value="IQ">Iraq</option><option value="IE">Ireland</option><option value="IM">Isle of Man</option><option value="IL">Israel</option><option value="IT">Italy</option><option value="CI">Ivory Coast</option><option value="JM">Jamaica</option><option value="JP">Japan</option><option value="JO">Jordan</option><option value="KZ">Kazakhstan</option><option value="KE">Kenya</option><option value="KI">Kiribati</option><option value="KW">Kuwait</option><option value="KG">Kyrgyzstan</option><option value="LA">Laos</option><option value="LV">Latvia</option><option value="LB">Lebanon</option><option value="LS">Lesotho</option><option value="LR">Liberia</option><option value="LY">Libya</option><option value="LI">Liechtenstein</option><option value="LT">Lithuania</option><option value="LU">Luxembourg</option><option value="MO">Macau</option><option value="MK">Macedonia</option><option value="MG">Madagascar</option><option value="MW">Malawi</option><option value="MY">Malaysia</option><option value="MV">Maldives</option><option value="ML">Mali</option><option value="MT">Malta</option><option value="MH">Marshall Islands</option><option value="MQ">Martinique</option><option value="MR">Mauritania</option><option value="MU">Mauritius</option><option value="YT">Mayotte</option><option value="MX">Mexico</option><option value="FM">Micronesia</option><option value="MD">Moldova</option><option value="MC">Monaco</option><option value="MN">Mongolia</option><option value="ME">Montenegro</option><option value="MS">Montserrat</option><option value="MA">Morocco</option><option value="MZ">Mozambique</option><option value="MM">Myanmar</option><option value="NA">Namibia</option><option value="NR">Nauru</option><option value="NP">Nepal</option><option value="NL">Netherlands</option><option value="AN">Netherlands Antilles</option><option value="NC">New Caledonia</option><option value="NZ">New Zealand</option><option value="NI">Nicaragua</option><option value="NE">Niger</option><option value="NG">Nigeria</option><option value="NU">Niue</option><option value="NF">Norfolk Island</option><option value="MP">Northern Mariana Islands</option><option value="NO">Norway</option><option value="OM">Oman</option><option value="PK">Pakistan</option><option value="PW">Palau</option><option value="PS">Palestinian Territory</option><option value="PA">Panama</option><option value="PG">Papua New Guinea</option><option value="PY">Paraguay</option><option value="PE">Peru</option><option value="PH">Philippines</option><option value="PN">Pitcairn</option><option value="PL">Poland</option><option value="PT">Portugal</option><option value="PR">Puerto Rico</option><option value="QA">Qatar</option><option value="RE">Reunion</option><option value="RO">Romania</option><option value="RU">Russia</option><option value="RW">Rwanda</option><option value="SH">Saint Helena</option><option value="KN">Saint Kitts and Nevis</option><option value="LC">Saint Lucia</option><option value="PM">Saint Pierre and Miquelon</option><option value="VC">Saint Vincent and the Grenadines</option><option value="WS">Samoa</option><option value="SM">San Marino</option><option value="ST">Sao Tome and Principe</option><option value="SA">Saudi Arabia</option><option value="SN">Senegal</option><option value="RS">Serbia</option><option value="SC">Seychelles</option><option value="SL">Sierra Leone</option><option value="SG">Singapore</option><option value="SK">Slovakia</option><option value="SI">Slovenia</option><option value="SB">Solomon Islands</option><option value="SO">Somalia</option><option value="ZA">South Africa</option><option value="KR">South Korea</option><option value="ES">Spain</option><option value="LK">Sri Lanka</option><option value="SR">Suriname</option><option value="SJ">Svalbard and Jan Mayen</option><option value="SZ">Swaziland</option><option value="SE">Sweden</option><option value="CH">Switzerland</option><option value="TW">Taiwan</option><option value="TJ">Tajikistan</option><option value="TZ">Tanzania</option><option value="TH">Thailand</option><option value="TG">Togo</option><option value="TK">Tokelau</option><option value="TO">Tonga</option><option value="TT">Trinidad and Tobago</option><option value="TN">Tunisia</option><option value="TR">Turkey</option><option value="TM">Turkmenistan</option><option value="TC">Turks and Caicos Islands</option><option value="TV">Tuvalu</option><option value="VI">U.S. Virgin Islands</option><option value="UG">Uganda</option><option value="UA">Ukraine</option><option value="AE">United Arab Emirates</option><option value="GB">United Kingdom</option><option selected="selected" value="US">United States</option><option value="UY">Uruguay</option><option value="UZ">Uzbekistan</option><option value="VU">Vanuatu</option><option value="VA">Vatican</option><option value="VE">Venezuela</option><option value="VN">Vietnam</option><option value="WF">Wallis and Futuna</option><option value="EH">Western Sahara</option><option value="YE">Yemen</option><option value="ZM">Zambia</option><option value="ZW">Zimbabwe</option></select>		
         
            <?php 
            /*
            <div id="vatfields" class="purchaseconfbox">
               <span style="font-weight:bold">VAT</span><br>
               <span id="vat-info-gb">VAT is charged at 20% to customers in the UK</span>
               <div id="vat-info-eu">VAT is charged at 20% to customers that are in the EU and do not have a valid EU VAT number.
                  <br><br>
                  If you change your VAT number click 'Validate' to exempt yourself from VAT.</span><br>
                  <span id="vatccode"><?php echo substr($cust->VATNum,0,2) ?></span><input type="text" name="euvatnumber" id="vatnumber" value="<?php echo substr($cust->VATNum,2); ?>"><a href="#" class="greybutton-sm" id="validatebtn">Validate</a><div class="smallajaxloader" style="margin-top:4px;" id="vatloader"></div>
                  <div class="clearall"></div>
               </div>
               <span>
               <?php if ($cust->VAT==1)
                  echo "<br><br>Currently, your country is set to ".fg_codeToCountry($cust->Country)." and you are charged VAT<br>";
               else
                  echo "<br><br>Currently, your country is set to ".fg_codeToCountry($cust->Country)." and you are NOT charged VAT<br>";
               ?>
               </span>
            </div>
            */
            ?>
      </div>
      <div>
      <br>
      <div class="clearall"></div><br>
	</div>

               <div class="purchaseconfbox">
                  <span class="pay-bigtext">Your Purchase: Feedgrabbr Pro, monthly subscription</span>
                  <div class="divider"></div>

                  <?php /*
                  <div id="payment-total-withvat">
                     <div class="pf-col-ss">
                        <span class="pay-bigtext">You pay:</span>
                     </div>
                     <div class="pf-col-m">
                        <span class="pay-bigtext">$5.95 per month</span><br>
                        <span>($4.95 + $1 of UK VAT at 20%)</span>
                     </div>
                     <div class="clearall"></div>
                  </div>
                  */ ?>
                  <div id="payment-total-novat">
                     <div class="pf-col-ss">
                        <span class="pay-bigtext">You pay:</span>
                     </div>
                     <div class="pf-col-m">
                        <span class="pay-bigtext">$4.95 per month</span>
                     </div>
                     <div class="clearall"></div>
                  </div>
                  <div class="divider"></div>
                  <?php $billday =  $subs[0]->BillingDay; ?>
                  <span>You are charged on the <?php echo fg_getNumberWithSuffix($billday) ?> of every month <?php if ($billday > 28) echo ", or the end of the month, if it doesn't contain ".$billday." days" ?>.<br><br>
                  </span>
               </div>

            <br>
   <a href="#" class='greybutton bigbtn' id="subform">Update</a>
            <div class="clearall"></div><br>

   </form>


<br>
         </div>

</div>


<div id="errpopup1">
   <span>You need to fill in this field</span>
</div>



<script>
var errpopup1;
<?php
//var eucodes = ["AT","BE","BG","CY","CZ","DE","DK","EE","ES","FI","FR","GB","GR","HU","IE","IT","LV","LT","LU","MT","NL","PL","PT","RO","SE","SK","SI"];
//var validatedVATNum = false;
?>
var ajax1;


function init_pagescript()
{
   <?php 
   /*
   $("country-list").addEvent("change",function(e){
      var CC = this.value;
      if (eucodes.contains(CC)) 
      {
         $("vatfields").show();

         if (CC=="GR") 
            CC="EL";

         $("vatccode").set('text',CC);

         displayVATCost();

         if (CC=="GB") 
         {
            $("vat-info-eu").hide();
            $("vat-info-gb").show();
         }
         else {
            $("vat-info-eu").show();
            $("vat-info-gb").hide();
         }
      }
      else
      {
         // outside EU, so no VAT
         $("vatfields").hide();
         $("payment-total-withvat").hide();
         $("payment-total-novat").show();
      }
   });
   */
   ?>

   $("country-list").set("value","<?php echo $addr->countryCodeAlpha2 ?>");
   $("country-list").fireEvent("change");

   /*
   // allow only numbers and spaces in the CC number field
   $("credit_card_number").addEvent("keydown",function(e){
//      if (e.key != 'v' || !e.control) {
         if (!validnums.contains(e.key)) {
            e.stop();
         }
//      }
   });
   */

   <?php 
   /*
   $("vatnumber").addEvent("keydown",function(e){
      this.removeClass("vatvalidated");
   });
   */
   ?>

   $("subform").addEvent("click",function(e){
      e.stop();
      var allok=true;

      
      // check required fields
      var rfields = $("payment-form").getElements(".reqfield");
      for(var i=0;i<rfields.length;i++)
      {
         var el = rfields[i];
         if (el.value.trim()=="") {
            el.value = "";
            new Fx.Scroll(window).toElementCenter(el,"y");
            el.focus();
            errpopup1.show(el,"This is a required field");
            allok = false;
            break;
         }
      }

      

      if (allok) {
         $('payment-form').submit();
      }

   });


   errpopup1 = new quickpopup($("errpopup1"),null,{below:true, arrow:true, permanent:true, width: 200, formerror:true, clicktohide:true, contclass:"formerrpopup"});

   <?php
   /*
   ajax1 = new Request.JSON({method:'get',onComplete: ajaxComplete });
   */
   /*
   $("validatebtn").addEvent("click",function(e){
      e.stop();
      if (!this.hasClass('btndisabled')) {

         this.addClass('btndisabled');
         this.set('text',"Validating...");
         $("vatloader").show();
       //  new quickpopup($("vatpopup1"),$("validatebtn"),{below:true, arrow:true, permanent:true, width: 300, formerror:true, clicktohide:true, contclass:"formerrpopup"}).show();

         // Greece is a special case - its country code is GR and its VAT code is EL
         var CC = $("country-list").value;
         if (CC=="GR") 
            CC="EL";

         var sURL = "<?php echo site_url(); ?>/fg-doajax/?op=validatevat&vatnum="+CC+$("vatnumber").value;
         ajax1.send({url: sURL});
      }
   });
   */  
   ?>
}

<?php
/*
function ajaxComplete(retdata)
{
   $("validatebtn").removeClass("btndisabled");
   $("validatebtn").set('text',"Validate");
   $("vatloader").hide();


   switch(retdata)
   {
   case "ok":
      alert ("VAT number validated. VAT will be excluded from the cost of purchase.");
      validatedVATNum = true;
      $("vatnumber").addClass("vatvalidated");
      break;
   default:
      alert (retdata);
      validatedVATNum = false;
      break;
   }

   displayVATCost();
}

function displayVATCost()
{
   if (validatedVATNum) {
      $("payment-total-withvat").hide();
      $("payment-total-novat").show();
   }
   else {
      $("payment-total-withvat").show();
      $("payment-total-novat").hide();
   }
}
*/
?>


</script>
<?php } ?>
<?php } /* end of the address editing page */ ?>

<?php get_footer(); ?>

