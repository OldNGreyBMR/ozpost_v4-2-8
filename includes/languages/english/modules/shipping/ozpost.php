<?php
/*
  $Id: ozpost.php,v4.2.8 2022-09-28
 *  //BMH 2020-11-08
  Copyright (c) 2007-2013 Rod Gasson / VCSWEB
  Copyright (c) 2020 Cronomic
  Released under the GNU General Public License
*/ // BMH 2022-03-16 defines for 157d php8
if (!defined('MODULE_SHIPPING_OZPOST_SORT_ORDER')) { define('MODULE_SHIPPING_OZPOST_SORT_ORDER',''); }
if (!defined('MODULE_SHIPPING_OZPOST_STATUS')) { define('MODULE_SHIPPING_OZPOST_STATUS',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TAX_CLASS')) { define('MODULE_SHIPPING_OZPOST_TAX_CLASS',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_LETTERS')) { define('MODULE_SHIPPING_OZPOST_TYPE_LETTERS',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_APP')) { define('MODULE_SHIPPING_OZPOST_TYPE_APP',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_APS')) { define('MODULE_SHIPPING_OZPOST_TYPE_APS',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_TNT')) { define('MODULE_SHIPPING_OZPOST_TYPE_TNT',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_STA')) { define('MODULE_SHIPPING_OZPOST_TYPE_STA',''); }
if (!defined('REMOTE_HOST')) { define('REMOTE_HOST',''); }


define('MODULE_SHIPPING_OZPOST_TEXT_TITLE'	, 'Ozpost MultiQuote');
define('MODULE_SHIPPING_OZPOST_AP_TEXT'         , 'Australia Post');
// define('MODULE_SHIPPING_OZPOST_AP_TEXT'         , 'AustPost'); //  short version //
define('MODULE_SHIPPING_OZPOST_TNT_TEXT'	, 'TNT');
define('MODULE_SHIPPING_OZPOST_FASTWAY_TEXT'	, 'Fastway Couriers');
define('MODULE_SHIPPING_OZPOST_TRANSD_TEXT'	, 'Transdirect');
define('MODULE_SHIPPING_OZPOST_EGO_TEXT'	, 'E-go');
define('MODULE_SHIPPING_OZPOST_STAR_TEXT'	, 'StarTrack');
define('MODULE_SHIPPING_OZPOST_CPL_TEXT'        ,'Couriers Please') ; 
define('MODULE_SHIPPING_OZPOST_SMS_TEXT'        ,'SmartSend') ; 
define('MODULE_SHIPPING_OZPOST_SKP_TEXT'        , 'Skippy Post') ; 
  
define('MODULE_SHIPPING_OZPOST_HANDLING1_TEXT'	, ' Includes ');
define('MODULE_SHIPPING_OZPOST_HANDLING2_TEXT'	, ' Packaging & Handling. ');
define('MODULE_SHIPPING_OZPOST_EST_DEL_TEXT'	, ' Days Estimated Delivery.');
define('MODULE_SHIPPING_OZPOST_GUESTMSG'	, 'Please enter your country or postcode');
define('MODULE_SHIPPING_OZPOST_ERROR_TEXT1'	, ' Unexpected error. No valid method. Using static rates.');
define('MODULE_SHIPPING_OZPOST_ERROR_TEXT2'     , '<strong>Temporary Quote Error. Static rates currently apply</strong> (or try again later).') ;
define('MODULE_SHIPPING_OZPOST_ERROR_TEXT3'     , '<div class=messageStackWarning>An error occured retrieving shipping costs.</br>Please Contact owner for pricing</div>') ;
define('MODULE_SHIPPING_OZPOST_ERROR_TEXT4'     , '<div class=messageStackWarning>One or more products in the cart have been flagged as unshippable. Please Contact owner for more information</div>') ;

/*
  The following defines are examples of how to override the default *descriptions*
  of the various shipping methods. This is useful if you wish to obfuscate identifying
  any particular carrier based on this information.
 The  examples listed are used to prevent the EGO methods from being displayed as
 "EGO EGO Parcel"  &  "EGO EGO Insured Parcel"
 It does this by simply changing "EGO Parcel to just "Parcel".
 To add/use/create your own overrides you will first need to identify the methodID code
 then append "_description" to this code (as per examples).
 You should be able identify the methodID's by looking at the 'case' commands midway in
 the code /includes/modules/shipping/ozpost.php. There are over 100 of them!
*/
 //define('EGOi_description', 'Insured Parcel') ;
//  define('EGO_description' , 'Parcel') ;
//  define('TRD_description' , 'Parcel') ;
//  define('RPP_description' , 'OzPostParcel') ;


//  GAM was here 2013-08-24: Added description defines for each Fastway label (FWL). Note that FWS__description is not enabled in code.
//  define('FWS1_description' , 'tracked & signed') ;    // Fastway Satchel override
//  define('FWS3o_description' , 'tracked, signed & insured') ;    // Fastway Satchel override
//  define('FWS3b_description' , 'tracked, signed & insured') ;    // Fastway Satchel override
//  define('FWS5_description' , 'tracked, signed & insured') ;    // Fastway Satchel override
//
//  define('FWLlm_description' , 'Lime (tracked, signed & insured)') ;    // Fastway Label override
//  define('FWLpk_description' , 'Pink (tracked, signed & insured)') ;    // Fastway Label override
//  define('FWL1_description' , 'Red (tracked, signed & insured)') ;    // Fastway Label override
//  define('FWL2_description' , 'Orange (tracked, signed & insured)') ;    // Fastway Label override
//  define('FWL3_description' , 'Green (tracked, signed & insured)') ;    // Fastway Label override
//  define('FWL4_description' , 'White (tracked, signed & insured)') ;    // Fastway Label override
//  define('FWL5_description' , 'Grey (tracked, signed & insured)') ;    // Fastway Label override
//  define('FWLbk_description' , 'Black (tracked, signed & insured)') ;    // Fastway Label override
//  define('FWLbl_description' , 'Blue (tracked, signed & insured)') ;    // Fastway Label override
//  define('FWLyl_description' , 'Yellow (tracked, signed & insured)') ;    // Fastway Label override


// Satchels – Australia Post (national) //
//define('PPS3_description', 'Parcel Post Satchel (tracked only)') ;
//define('PPS3r_description', 'Parcel Post Satchel (tracked & signed)') ;
//define('PPS3i_description', 'Parcel Post Satchel (tracked, signed & insured)') ;
//
//define('PPS5_description', 'Parcel Post Satchel (tracked only)') ;
//define('PPS5r_description', 'Parcel Post Satchel (tracked & signed)') ;
//define('PPS5i_description', 'Parcel Post Satchel (tracked, signed & insured)') ;
//
//define('PPS5K_description', 'Parcel Post Satchel (tracked only)') ;
//define('PPS5Kr_description', 'Parcel Post Satchel (tracked & signed)') ;
//define('PPS5Ki_description', 'Parcel Post Satchel (tracked, signed & insured)') ;
//
//define('PPSE3_description', 'Express Post Satchel (tracked only)') ;
//define('PPSE5_description', 'Express Post Satchel (tracked only)') ;
//define('PPSE5K_description', 'Express Post Satchel (tracked only)') ;
//
//define('PPSE3XL_description', 'Express Post Satchel (tracked only)') ;
//define('PPSE5KXL_description', 'Express Post Satchel (tracked only)') ;
//
//define('PPSP3_description', 'Express Post Satchel (tracked & signed)') ;
//define('PPSP5_description', 'Express Post Satchel (tracked & signed)') ;
//define('PPSP5K_description', 'Express Post Satchel (tracked & signed)') ;
//
//define('PPSP3i_description', 'Express Post Satchel (tracked, signed & insured)') ;
//define('PPSP5i_description', 'Express Post Satchel (tracked, signed & insured)') ;
//define('PPSP5Ki_description', 'Express Post Satchel (tracked, signed & insured)') ;
//
//// Parcels– Australia Post (national) //
//define('REG_description', 'Parcel Post (tracked & signed)') ;
//define('RPP_description', 'Parcel Post (tracked only)') ;
//define('RPPi_description', 'Parcel Post (tracked, signed & insured)') ;
//define('EXP_description', 'Express Post Parcel (tracked only)') ;
//define('EXPi_description', 'Express Post Parcel (tracked, signed & insured)') ;
//define('PLT_description', 'Express Post Parcel (tracked & signed)') ;
//define('PLTi_description', 'Express Post Parcel (tracked, signed & insured)') ;
//
//// Parcels– Australia Post (international) //
//define('EPI_description', 'Express Post International (Tracked & Signed)') ;
//define('EXP_description', 'Express Post International (Tracked & Signed)') ;
//define('EXPi_description', 'Express Post International (Tracked, Signed & Insured)') ;
//define('EPIi_description', 'Express Post International (Tracked, Signed & Insured)') ;
//
//define('AIR_description', 'Air Mail (NO TRACKING)') ;
//define('AIRi_description', 'Air Mail (NO TRACKING, Signed & Insured)') ;
//define('AIRr_description', 'Air Mail (NO TRACKING, Signed)') ;
//define('PAT_description', 'Pack and Track International (Tracked only)') ;
//define('SEA_description', 'Sea Mail (NO TRACKING)') ;
//define('SEAi_description', 'Sea Mail (NO TRACKING, Signed & Insured)') ;
//define('ECIm_description', 'Express Courier International (ECI) Merchandise (Tracked & Signed)') ;
//define(' ECId_description', 'Express Courier International (ECI) Documents (Tracked & Signed)') ;
//define('ECImi_description', 'Express Courier International (ECI) Merchandise (Tracked, Signed & Insured)') ;
//define('ECIdi_description', 'Express Courier International (ECI) Documents (Tracked, Signed & Insured)') ;
//
//// Satchels & Boxes– Australia Post (international) //
//define('RPIPP5_description', 'Registered Post International (Air) 500gm Prepaid bag (NO TRACKING, Signed)') ;
//define('RPIPP1_description', 'Registered Post International (Air) 1kg Prepaid bag (NO TRACKING, Signed)') ;
//
//define('ECIP500g_description', 'ECI 500gm Prepaid Satchel (Tracked & Signed)') ;
//define('ECIP1_description', 'ECI 1Kg Prepaid Satchel (Tracked & Signed)') ;
//define('ECIP2_description', 'ECI 2Kg Prepaid Satchel (Tracked & Signed)') ;
//define('ECIP3_description', 'ECI 3Kg Prepaid Satchel (Tracked & Signed)') ;
//define('ECIP5k_description', 'ECI 5Kg Prepaid Box (Tracked & Signed)') ;
//define('ECIP10_description', 'ECI 10Kg Prepaid Box (Tracked & Signed)') ;
//define('ECIP20_description', 'ECI 20Kg Prepaid Box (Tracked & Signed)') ;
//
//define('EPIP2_description', 'EPI 2Kg Prepaid Satchel (Tracked & Signed)') ;
//define('EPIP3_description', 'EPI 3Kg Prepaid Satchel (Tracked & Signed)') ;
//define('EPIP5k_description', 'EPI 5Kg Prepaid Box (Tracked & Signed)') ;
//define('EPIP10_description', 'EPI 10Kg Prepaid Box (Tracked & Signed)') ;
//define('EPIP20_description', 'EPI 20Kg Prepaid Box (Tracked & Signed)') ;
//
//// LETTERS– Australia Post (national & international) //
//define('LETTER_description', 'Letter-Regular (NO TRACKING)') ;
//define('REGLETTER_description', 'Letter-Registered Post (Tracked & Signed)') ;
//define('INSLETTER_description', 'Letter-Registered Post (Tracked, Signed & Insured)') ;
//define('EXLETTER_description', 'Letter-Express Post (Tracked)') ;

?>