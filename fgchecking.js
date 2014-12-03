var errpopup1;
//var eucodes = ["AT","BE","BG","CY","CZ","DE","DK","EE","ES","FI","FR","GB","GR","HU","IE","IT","LV","LT","LU","MT","NL","PL","PT","RO","SE","SK","SI"];
var validnums = ["0","1","2","3","4","5","6","7","8","9","space","v"]; // 'v' so they can ctrl-v to paste
//var validatedVATNum = false;
var ajax1;
var g_monthly = true;


// Daves first update

function init_pagescript()
{
   var cvvbox = $("cvvpopup").asBox(true);
   $("cvvinfo").addEvent("click",function(e){
      e.stop();
      cvvbox.show();
   });

 //  $("country-list").addEvent("change",function(e){
 //     setCountryVAT(this.value);
//
//   });

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

 //  $("vatnumber").addEvent("keydown",function(e){
 //     this.removeClass("vatvalidated");
 //  });


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

      // check cc number isn't too long or short
      if (allok) {
         var str = $("credit_card_number").value.replace(/\s/g,'');// remove spaces
         if (str.length < 13 || str.length > 16) {
            $("credit_card_number").focus();
            errpopup1.show($("credit_card_number"),"The credit card number should be between 13 - 16 digits");
            allok = false;
         }
      }

      // make sure cvv is 3 or 4 chars
      if (allok) {
         if ($("credit_card_cvv").value.length < 3) {
            $("credit_card_cvv").focus();
            errpopup1.show($("credit_card_cvv"),"This should be a 3 or 4 digit number");
            allok = false;
         }
      }

      // make sure the postal code is letters, numbers, spaces and hyphens - Braintree will reject otherwise
      var letters = /^[0-9a-zA-Z \-]+$/;
      if(!$("billing_postal_code").value.match(letters))
      {
         $("billing_postal_code").focus();
         errpopup1.show($("billing_postal_code"),"Letters, numbers, spaces and hyphens only please");
         allok = false;
      }

      // make sure the expiry date is in the future
      if (allok) {
         var dt = new Date();
         var yr = dt.getFullYear();
         var mn = dt.getMonth()+1;
         var exyr = parseInt($("credit_card_exp_year").value);
         var exmn = parseInt($("credit_card_exp_month").value);
         if (yr > exyr ) {
            allok = false;
            errpopup1.show($("credit_card_exp_year"),"The expiry date should be in the future");
         }
         else
         {
            if (yr == exyr) {
               if (mn >= exmn) {
                  allok = false;
                  errpopup1.show($("credit_card_exp_month"),"The expiry date should be in the future");
               }
            }
         }

      }
      

      if (allok) {
         $('payment-form').submit();
      }

   });


   errpopup1 = new quickpopup($("errpopup1"),null,{below:true, arrow:true, permanent:true, width: 200, formerror:true, clicktohide:true, contclass:"formerrpopup"});

//   ajax1 = new Request.JSON({method:'get',onComplete: ajaxComplete });
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

         var sURL = g_blogurl+"/fg-doajaxssl/?op=validatevat&vatnum="+CC+$("vatnumber").value;
         ajax1.send({url: sURL});
      }
   });
*/

   var dt = new Date();
   $("credit_card_exp_year").set("value",dt.getFullYear().toString());

   // preselect country if this is on the CC or address update page
   if (typeof(g_countryCode) !== "undefined") 
   {
      $("country-list").set("value",g_countryCode);
     // setCountryVAT(g_countryCode);
   }

   // they've selected a monthly or yearly option, so show-hide billing period descriptions 
   $$(".planradio").addEvent("change",function(e){
      g_monthly = (this.value.substring(0,1)=="m") ? true : false;
      displayMonthYearDesc();
   });
}

/*
function setCountryVAT(ccode)
{
   if (eucodes.contains(ccode)) 
   {
      $("vatfields").show();

      $("vatccode").set('text',ccode);


      if (ccode=="GB") 
      {
         displayVATCost(true);
         $("vat-info-eu").hide();
         $("vat-info-gb").show();
      }
      else {
         displayVATCost(!g_validatedVATNum);
         $("vat-info-eu").show();
         $("vat-info-gb").hide();
      }
   }
   else
   {
      // outside EU, so no VAT
      $("vatfields").hide();
      displayVATCost(false);
   }

}
*/
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
      g_validatedVATNum = true;
      $("vatnumber").addClass("vatvalidated");
      break;
   default:
      alert (retdata);
      g_validatedVATNum = false;
      $("vatnumber").removeClass("vatvalidated");
      break;
   }

   displayVATCost(!g_validatedVATNum);
}
*/
/*
function displayVATCost(showvat)
{
   if (showvat) {
      $("payment-total-withvat").show();
      $("payment-total-novat").hide();
      $("monthlyvat").set("checked",true);
   }
   else {
      $("payment-total-withvat").hide();
      $("payment-total-novat").show();
      $("monthlynovat").set("checked",true);
   }

   g_monthly = true;
   displayMonthYearDesc();
}
*/
function displayMonthYearDesc()
{
   if (g_monthly) {
      $("yearly-desc").hide();
      $("monthly-desc").show();
   }
   else
   {
      $("yearly-desc").show();
      $("monthly-desc").hide();
   }
}


