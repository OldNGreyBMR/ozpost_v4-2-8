<?php // Zencart v1.3.7, -> v1.5.8    Note: The "// Zencart" is used as an initial test for a valid update file (only needs to read a few chars)
// BMH 2023-02 zc158 php8.1
// v 4.2.8
/*
  Copyright (c) 2007-2017 Rod Gasson / VCSWEB
  Copyright (c) 2020 Cronomic

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
// BMH remove old comments

$Id: ozpost.php V4.2.7  Jun 10th 2020
        Updates for Zencart 1.5.6c
        Updates for PHP 7 and upwards
 *
*/
// BMH 2020-11-12
// BMH 2021-10-07 changed ozpost::ozp_cfg_select_option to ozpost::ozp_cfg
//					line 5986 make functions static
// BMH 2022-03-16   line undefined array key 3581 3586 5875
// BMH 2022-04-03   line 49 to 59 define all constants - PHP8.0
//                  line 669 and then line 678 define $Dsub 
// BMH 2022-09-13   declare vars ln75 & ln 220; ln5987 Undefined index: set
//                  initialise quotes array ln 239
// BMH 2022-09-26   ln287 index postcode
// BMH 2023-02-05   php8.1 compliant

//Report all errors except warnings.
//error_reporting(E_ALL ^ E_WARNING)
	
if (!defined('MODULE_SHIPPING_OZPOST_SORT_ORDER'))  {define('MODULE_SHIPPING_OZPOST_SORT_ORDER','') ; }
if (!defined('MODULE_SHIPPING_OZPOST_SDL'))  {define('MODULE_SHIPPING_OZPOST_SDL','') ; }

if (!defined('DIR_FS_CATALOG_MODULES')) {define('DIR_FS_CATALOG_MODULES', DIR_FS_CATALOG . 'includes/modules/'); }
// BMH if (!defined('REMOTE-HOST')) {define('REMOTE_HOST', ''); }

if (!defined('MODULE_SHIPPING_OZPOST_STATUS')) { define('MODULE_SHIPPING_OZPOST_STATUS',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TAX_CLASS')) { define('MODULE_SHIPPING_OZPOST_TAX_CLASS',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_LETTERS')) { define('MODULE_SHIPPING_OZPOST_TYPE_LETTERS',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_APP')) { define('MODULE_SHIPPING_OZPOST_TYPE_APP',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_APS')) { define('MODULE_SHIPPING_OZPOST_TYPE_APS',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_TNT')) { define('MODULE_SHIPPING_OZPOST_TYPE_TNT',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_STA')) { define('MODULE_SHIPPING_OZPOST_TYPE_STA',''); }

if (!defined('MODULE_SHIPPING_OZPOST_TYPE_FW')) { define('MODULE_SHIPPING_OZPOST_TYPE_FW',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_TRD')) { define('MODULE_SHIPPING_OZPOST_TYPE_TRD',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_EGO')) { define('MODULE_SHIPPING_OZPOST_TYPE_EGO',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_CPL')) { define('MODULE_SHIPPING_OZPOST_TYPE_CPL',''); }

if (!defined('MODULE_SHIPPING_OZPOST_TYPE_SMS')) { define('MODULE_SHIPPING_OZPOST_TYPE_SMS',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_APOS')) { define('MODULE_SHIPPING_OZPOST_TYPE_APOS',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_SKP')) { define('MODULE_SHIPPING_OZPOST_TYPE_SKP',''); }
if (!defined('MODULE_SHIPPING_OZPOST_TYPE_HX')) { define('MODULE_SHIPPING_OZPOST_TYPE_HX',''); }

if (!defined('MODULE_SHIPPING_OZPOST_DEBUG')) { define('MODULE_SHIPPING_OZPOST_DEBUG',''); }


// BMH bof  ************************
if (!defined('AUD')) {define('AUD',''); }
if (!defined('AUS')) {define('AUS',''); }
if (!defined('DIR_FS_ADMIN')) {define('DIR_FS_ADMIN', '/BMHAdmin/');}
//if (!defined('VERSION')) { define('VERSION',''); }
//if (!defined('HOST')) { define('HOST',''); }

// BMH eof *********** 

/**  * Class ozpost  */
class ozpost extends base
{
    public $allowed;
    public $allowed_methods;
    public $aus_rate;           //
    public $_check;
    public $code;              // 
    public $description;       // 
    public $enabled;           // 
    public $flags;             // 
    public $HOST;
    public $icon;              // 
    public $ipath;
    public $logo;
    public $quotes =[];
    public $Osub;              // 
    public $sort_order;         // 
    public $tax_class;          //
    public $title;             // 
    public $VERSION;

    
    
    
	//var $code, $title, $description, $icon, $enabled;
	//var $Osub = '',
    //    $flags = '';      // BMH
	
	public function __construct()
	{
		global $ipath, $Ozfiles;
		$ipath = DIR_WS_IMAGES . "icons/ozpost/";
		
		$this->code = 'ozpost';
		$this->VERSION = "4.2.8";
		$this->HOST = urlencode(preg_replace('/[^A-Za-z0-9\s\s+\.\'\"\-\&]/', '', STORE_NAME));
		$this->title = MODULE_SHIPPING_OZPOST_TEXT_TITLE;
		$this->description = "<a href =\"https://www.ozpost.net\" target=\"_blank\"/><img src=\"https://svr0.ozpost.net/favicon.ico.gif\" alt=\"https://www.ozpost.net\"></a> V$this->VERSION";
		$this->sort_order = MODULE_SHIPPING_OZPOST_SORT_ORDER;
		
		if (zen_get_shipping_enabled($this->code)) {
			$this->enabled = ((MODULE_SHIPPING_OZPOST_STATUS == 'True') ? true : false);
		}
		
		$this->logo = "<a href =\"https://www.ozpost.net\" target=\"_blank\"/>" . zen_image(
				$ipath . 'ozpost_logo.png',
				'ozpost_logo.png'
			) . "</a>";
		
		$this->tax_class = MODULE_SHIPPING_OZPOST_TAX_CLASS;
		$this->allowed_methods = array_merge(
			explode(", ", MODULE_SHIPPING_OZPOST_TYPE_LETTERS),
			explode(", ", MODULE_SHIPPING_OZPOST_TYPE_APP),
			explode(", ", MODULE_SHIPPING_OZPOST_TYPE_APS),
			explode(", ", MODULE_SHIPPING_OZPOST_TYPE_FW),
			explode(", ", MODULE_SHIPPING_OZPOST_TYPE_TNT),
			explode(", ", MODULE_SHIPPING_OZPOST_TYPE_STA),
			explode(", ", MODULE_SHIPPING_OZPOST_TYPE_TRD),
			explode(", ", MODULE_SHIPPING_OZPOST_TYPE_EGO),
			explode(", ", MODULE_SHIPPING_OZPOST_TYPE_CPL),
			explode(", ", MODULE_SHIPPING_OZPOST_TYPE_SMS),
			explode(", ", MODULE_SHIPPING_OZPOST_TYPE_APOS),
			explode(", ", MODULE_SHIPPING_OZPOST_TYPE_SKP),
			explode(", ", MODULE_SHIPPING_OZPOST_TYPE_HX)
		);
		
		if (!defined('DIR_WS_MODULES')) {
			define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');
		} // For V1.3.7 compatibility
		if (!defined('DIR_WS_TEMPLATES')) {
			define('DIR_WS_TEMPLATES', DIR_WS_INCLUDES . 'templates/');
		} // For V1.3.7 compatibility
		
		if (!defined('DIR_FS_SQL_CACHE')) {
			define('DIR_FS_SQL_CACHE', "cache/");
		}
		if (!defined('AUD')) {
			define('AUD','');
		}
		if (!defined('AUS')) {
			define('AUS','');
		}
		
		if (!defined('ME')) {
			define('ME', DIR_FS_CATALOG . DIR_WS_MODULES . "shipping/ozpost.php");
		}
		if (!defined('DB_PREFIX')) {
			define('DB_PREFIX', '');
		}
		if (!defined('TABLE_OZPOST_CACHE')) {
			define('TABLE_OZPOST_CACHE', DB_PREFIX . 'ozpost_cache');
		}
		
		if (!defined('MODULE_SHIPPING_OZPOST_STAR_TEXT')) {
			define('MODULE_SHIPPING_OZPOST_STAR_TEXT', 'StarTrack');
		}
		if (!defined('MODULE_SHIPPING_OZPOST_CPL_TEXT')) {
			define('MODULE_SHIPPING_OZPOST_CPL_TEXT', 'Couriers Please');
		}
		if (!defined('MODULE_SHIPPING_OZPOST_SMS_TEXT')) {
			define('MODULE_SHIPPING_OZPOST_SMS_TEXT', 'SmartSend');
		}
		if (!defined('MODULE_SHIPPING_OZPOST_SKP_TEXT')) {
			define('MODULE_SHIPPING_OZPOST_SKP_TEXT', 'Skippy Post');
		}
		if (!defined('MODULE_SHIPPING_OZPOST_HX_TEXT')) {
			define('MODULE_SHIPPING_OZPOST_HX_TEXT', 'Hunter Express');
		}
		if (!defined('MODULE_SHIPPING_OZPOST_ERROR_TEXT3')) {
			define(
				'MODULE_SHIPPING_OZPOST_ERROR_TEXT3',
				'<div class=messageStackWarning>An error occurred retrieving shipping costs.</br>Please Contact owner for pricing</div>'
			);
		}
		if (!defined('MODULE_SHIPPING_OZPOST_ERROR_TEXT4')) {
			define(
				'MODULE_SHIPPING_OZPOST_ERROR_TEXT4',
				'<div class=messageStackWarning>One or more products in the cart have been flagged as unshippable. Please Contact owner for more information</div>'
			);
		}

		/*
			The following defines are examples of how to override the default *descriptions*
			of the various shipping methods. This is useful if you wish to obfuscate identifying
			any particular carrier based on this information.
			The  examples listed are used to prevent the EGO methods from being displayed as
			"EGO EGO Parcel"  &  "EGO EGO Insured Parcel"
			It does this by changing "EGO Parcel to "Parcel".
			The EGO *carrier* names are defined in the /languages/../ozpost.php file.
			If you wish to change the example defines, and/or add you own you should copy them
			into the /languages/...../ozpost.php file so as to avoid your changes being
			overwritten with the next update.
			To add/use/create your own overrides you will first need to identify the methodID code
			then append "_description" to this code (as per examples).
			You should be able identify the methodID's by looking at the 'case' commands midway in
			this code.
		*/
		if (!defined('EGOi_description')) { define('EGOi_description', 'Insured Parcel'); }
		if (!defined('EGO_description')) { define('EGO_description', 'Parcel'); }
		//  define('TRD_description' , 'Parcel') ;
		//  define('RPP_description' , 'OzPostParcel') ;
		//  define('FWS_description' , 'SlowWay Sat') ;    // Fastway Satchel override
		//  define('FWL_description' , 'SlowWay Labels') ;    // Fastway label override
		///////////   end of defines ///
		$Ozfiles = array(
			array(DIR_FS_ADMIN, DIR_WS_LANGUAGES . "english/", "product.php"),
			array(DIR_FS_ADMIN, DIR_WS_MODULES, "update_product.php"),
			array(DIR_FS_ADMIN, DIR_WS_MODULES . "product/", "collect_info.php"),
			array(DIR_FS_CATALOG, DIR_WS_TEMPLATES . "template_default/templates/", "tpl_modules_shipping_estimator.php"),
			array(DIR_FS_CATALOG, DIR_WS_TEMPLATES . "template_default/templates/", "tpl_checkout_payment_default.php"),
			array(DIR_FS_ADMIN, DIR_WS_LANGUAGES, "english.php"),
			array(DIR_FS_CATALOG, DIR_WS_LANGUAGES, "english.php"),
			array(DIR_FS_ADMIN, DIR_WS_INCLUDES, "header.php"),
			array(DIR_FS_SQL_CACHE, null, null),
			array(DIR_FS_CATALOG, $ipath, null),
			array(DIR_FS_CATALOG, DIR_WS_LANGUAGES . 'english', null),
			array(DIR_FS_CATALOG, DIR_WS_TEMPLATES . $GLOBALS['template_dir'] . "/templates/", "tpl_modules_shipping_estimator.php"),
			array(DIR_FS_CATALOG, DIR_WS_TEMPLATES . $GLOBALS['template_dir'] . "/templates/", "tpl_checkout_payment_default.php"),
			array(DIR_FS_CATALOG_MODULES, "shipping/", "ozpost.php"),
		);
	}
	
	/**
	 * @param string $method
	 * @return array|void
	 */
	public function quote($method = '')
	{
        
		global $db, $order, $currencies, $parcelWeight, $parcelQty, $orderValue, $NoPack, $ipath;
		
        $Osub = '' ;     // BMH
        $flags = '';    // BMH
        
		if (zen_not_null($method) && (isset($_SESSION['ozpostQuotes']))) {
			$testmethod = $_SESSION['ozpostQuotes']['methods'];
			
			foreach ($testmethod as $temp) {
				$search = array_search("$method", $temp);
				if (strlen($search) > 0 && $search >= 0) {
					break;
				}
			}
			
        $usemod = $this->title ;        // BMH
        $usetitle = $temp['title'] ;    // BMH
        //  Initialise our quote arrays // BMH
        $this->quotes = [
          'id' => $this->code,
          'module' => $usemod,
          'methods' => [
            [
            'id' => $method,
            'title' => $usetitle,
            'cost' =>  $temp['cost']
            ]
          ]
        ];
        // // //
        
			$this->quotes = array(
				'id' => $this->code,        // "ozpost"
				'module' => $temp['carrier'] ?? '',  // TNT, Australia Post, FastWay etc  //BMH  ?? ''
				'methods' => array(
					array(
						'id' => $method, // unique code, eg LL1, REG, REGi.... (user selected value)
						'title' => $temp['title'], // Text string for methods type, eg Large Letter, Regular Parcel, etc
						'cost' => $temp['cost']
					)
				)
			);
			
			if ($this->tax_class > 0) {
				$this->quotes['tax'] = zen_get_tax_rate(
					$this->tax_class,
					$order->delivery['country']['id'],
					$order->delivery['zone_id']
				);
			}
			
			return $this->quotes;   // return a single quote
			
		}  //  Single Quote Exit Point

		//////////  main //////////////////
		$defaultdims = explode(',', MODULE_SHIPPING_OZPOST_DIMS);
		$control_tare = "&tare_weight=" . urlencode(
				MODULE_SHIPPING_OZPOST_TARE
			) . "&tare_dimensions=" . MODULE_SHIPPING_OZPOST_DIMTARE;
		sort($defaultdims);
		
		$parcelWidth = $parcelLength = $parcelHeight = $parcelWeight = $shipping_num_boxes_display = $dg = 0;
		$items =[];
		$enable_debug = "";
		
		$dest_country = $order->delivery['country']['iso_code_2'];
		//$topcode = str_replace(" ", "", ($order->delivery['postcode']));
        $topcode = str_replace(" ", "", ($order->delivery['postcode'] ?? ''));                  // BMH
		$aus_rate = (float)$currencies->get_value('AUD');   // get $AU exchange rate            // BMH include quotes
        
		if ($aus_rate == 0) {
			$aus_rate = (float)$currencies->get_value(AUS); // if AUD zero/undefined then try AUS
			if ($aus_rate == 0) {
				$aus_rate = 1; // if still zero intialise to 1.00 to avoid divide by zero error
			}
		}
		
		if (($topcode == "") && ($dest_country == "AU")) {
			return;
		}    //  This will occur with guest user first quote where no postcode is available
		
		if ($dest_country != "AU" && MODULE_SHIPPING_OZPOST_NOOS == "Yes") {
			return;
		}  // completely disable if overseas and this isn't wanted under any circumstances
		// start debug output
		if (MODULE_SHIPPING_OZPOST_DEBUG == "Yes") {
			$enable_debug = "yes";
			echo "<table border=1 width=95% ><th colspan=8 class=messageStackWarning>Debugging information</hr>";
		}
		// loop through cart validating/setting dimensions, acting on zero weight items, excluding free shipping and virtual items & setting DG flag if needed   //
		$shipme = $_SESSION['cart']->get_products();
		
		for ($index = 0; $index < sizeof($shipme); $index++) {
			$id = $shipme[$index]['id'];
			$type_query = "select products_virtual, product_is_always_free_shipping from " . TABLE_PRODUCTS . " where products_id='$id' limit 1 ";
			$type = $db->Execute($type_query);
			if (($type->fields['product_is_always_free_shipping'] == 0) && ($type->fields['products_virtual'] == 0)) {
				// Test for DG //
				if ($dg == 0) {  // Skip if already set
					//  $dg_query = "select dangerous_goods from " . TABLE_PRODUCTS . " where products_id='$id' limit 1 ";
					$dg_query = "select * from " . TABLE_PRODUCTS . " where products_id='$id' limit 1 ";
					$dgr = $db->Execute($dg_query);
					$dg = $dgr->fields['dangerous_goods'];
				}
				
				$products_weight = $shipme[$index]['weight'];
				///   Zero weight item? ////
				if ($products_weight == 0) {
					if (MODULE_SHIPPING_OZPOST_ZERO_WEIGHT == "Alert") {
						$this->quotes = array(
							'id' => $this->code,        // "ozpost"
							'module' => $this->code,
							'methods' => array(
								array(
									'id' => " ",
									'title' => MODULE_SHIPPING_OZPOST_ERROR_TEXT4,
									'cost' => 0,
									'Display' => ""
								)
							)
						);
						return $this->quotes;
					} elseif ((MODULE_SHIPPING_OZPOST_ZERO_WEIGHT == "Use Default weight") && (MODULE_SHIPPING_OZPOST_DEFW > 0)) {
						$shipme[$index]['weight'] = MODULE_SHIPPING_OZPOST_DEFW;
					}
				}
				
				
				if (MODULE_SHIPPING_OZPOST_WEIGHT_FORMAT == "kilos") {
					$products_weight = $products_weight * 1000;
				} // convert to gms
				$shipme[$index]['weight'] = $products_weight;
				$dim_query = "select products_length, products_height, products_width from " . TABLE_PRODUCTS . " where products_id='$id' limit 1 ";
				$dims = $db->Execute($dim_query);
				
				$shipme[$index]['height'] = ($dims->fields['products_height'] == 0) ? $defaultdims[0] : $dims->fields['products_height'];
				$shipme[$index]['width'] = ($dims->fields['products_width'] == 0) ? $defaultdims[1] : $dims->fields['products_width'];
				$shipme[$index]['length'] = ($dims->fields['products_length'] == 0) ? $defaultdims[2] : $dims->fields['products_length'];
				
				$shipme[$index]['height'] = $shipme[$index]['height'] * 10;  // cm to mm
				$shipme[$index]['width'] = $shipme[$index]['width'] * 10;  // cm to mm
				$shipme[$index]['length'] = $shipme[$index]['length'] * 10;  // cm to mm
				
				
				if ($shipme[$index]['tax_class_id'] > 0) {  // For insurance purposes use retail in tax
					$tr = zen_get_tax_rate(
						$shipme[$index]['tax_class_id'],
						$order->delivery['country']['id'],
						$order->delivery['zone_id']
					);
					$t = $shipme[$index]['price'] + ($shipme[$index]['price'] * ($tr / 100));
				}
				if ($t >= $shipme[$index]['price']) {
					$shipme[$index]['price'] = $t;
				}
				
				$shipme[$index]['price'] = number_format(($shipme[$index]['price'] / $aus_rate), 2);
			} else {
				$shipme[$index] = null; // Don't ship this item //
				if (MODULE_SHIPPING_OZPOST_DEBUG == "Yes") {
					if ($type->fields['product_is_always_free_shipping'] == 1) {
						echo "<tr><td colspan='8' class='messageStackCaution'>Item " . ($index + 1) . ":" . $shipme[$index]['name'] . " : Ignored - Always free shipping</td></tr>";
					} else {
						echo "<tr><td colspan='8' class='messageStackCaution'>Item " . ($index + 1) . ":" . $shipme[$index]['name'] . " : Ignored - Virtual product</td></tr>";
					}
				} // not debug
			} // end of virtual/free shippongh products
			
		} //   end of validation
		
		$tmp = array(); //  remove the nulls //
		foreach ($shipme as $row) {
			if ($row !== null) {
				$tmp[] = $row;
			}
		}
		$shipme = $tmp;

		// Custom packing //
		if (!$this->_checkInc(0)) {
			include(DIR_FS_CATALOG . DIR_WS_MODULES . "shipping/ozpost.inc"); //  All custom packing goes here
		}
		if (is_array($items)) {
			$customPack = "&customPack=1";
		} // just a flag for servrside debugging.

		// Pack whats left Note: override must nullify weight for items already processed - including all! //  no custom packing, use server code
		for ($index = 0; $index < count($shipme); $index++) { // loop through cart populating items array //
			if ($shipme[$index]['weight'] > 0) {
				$items[] = array(
					'Length' => $shipme[$index]['length'],
					'Width' => $shipme[$index]['width'],
					'Height' => $shipme[$index]['height'],
					'Weight' => $shipme[$index]['weight'],
					'Qty' => $shipme[$index]['quantity'],
					'Insurance' => $shipme[$index]['price']
				);
				// Save these in case of error - We use them to calculate static rates //
				//   $parcelWeight += $products_weight * $shipme[$index]['quantity']; $parcelQty += $shipme[$index]['quantity'] ; $orderValue +=  $shipme[$index]['price'] ;
				$parcelWeight += $shipme[$index]['weight'] * $shipme[$index]['quantity'];
				$parcelQty += $shipme[$index]['quantity'];
				$orderValue += $shipme[$index]['price'];
			}
		}  // next item
		$tmp = $this->_tareWeight($parcelWeight);
		$parcelWeight = $tmp[0]; // add tare //
		
		if (MODULE_SHIPPING_OZPOST_DEBUG == "Yes") {
			echo "</table>";
		}
		
		// package created, continue unless zero weight //
		if ($parcelWeight == 0) {
			$this->enabled = false;
			return;
		}
		
		$restrain = (MODULE_SHIPPING_OZPOST_RESTRAIN_DIMS == "Yes") ? "yes" : "";
		//  save dimensions for display purposes on quote form (this way we don't need to hack another system file)
		
		//$parcelWeight = 930 ;;
		$_SESSION['parcelweight'] = $parcelWeight / 1000;
		
		// Do some prep work to create the query string ..
		// convert cm to mm 'cos thats what the server uses //
		$parcelWidth = $parcelWidth * 10;
		$parcelHeight = $parcelHeight * 10;
		$parcelLength = $parcelLength * 10;
		$fromcode = MODULE_SHIPPING_OZPOST_ORIGIN_ZIP;
		// Set destination code ( postcode if AU, else 2 char iso country code )
		if ($dest_country == "AU") {
			$dcode = $topcode;
		} else {
			$dcode = $dest_country;
			if (isset($_POST['destSuburb'])) {
				unset($_POST['destSuburb']);
				$order->delivery['suburb'] = null;
				$order->delivery['city'] = null;
			}
		}
		
		if (MODULE_SHIPPING_OZPOST_DEBUG != "Yes") {    // these cause the server to exit early thus 'hiding' the options serverside
			if (MODULE_SHIPPING_OZPOST_HIDE_PARCEL2 == "Yes") {
				$flags = $flags | 2;
			}  //  hide parcels if satchel sized (original)
			
			switch (MODULE_SHIPPING_OZPOST_HIDE_PARCEL2) {
				case "If Less than 5kg";
					if ($parcelWeight < 5000) {
						$flags = $flags | 2;
					}
					break;
				case "If Less than 3kg</br>";
					if ($parcelWeight < 3000) {
						$flags = $flags | 2;
					}
					break;
				case "If Less than 1kg";
					if ($parcelWeight < 1000) {
						$flags = $flags | 2;
					}
					break;
				case "If Less than 500g";
					if ($parcelWeight < 500) {
						$flags = $flags | 2;
					}
					break;
				default;
					$flags = 0;
			}
			
			if (MODULE_SHIPPING_OZPOST_HIDE_COURIER == "Yes") {
				$flags = $flags | 4;
			}//  hide couriers if AP can handle
			if (MODULE_SHIPPING_OZPOST_HIDE_PARCELD == "Yes") {
				$flags = $flags | 8;
			}  //  hide all parcels if letter rates and domestic (supercedes flags=1)
			if (MODULE_SHIPPING_OZPOST_HIDE_PARCELO == "Yes") {
				$flags = $flags | 16;
			} //  hide all parcels if letter rates and overseas (supercedes flags=1)
		}
		$mail = 0;  //  Days we mail on.
		if (strchr(MODULE_SHIPPING_OZPOST_MAILDAYS, "Monday")) {
			$mail = $mail | 1;
		}
		if (strchr(MODULE_SHIPPING_OZPOST_MAILDAYS, "Tuesday")) {
			$mail = $mail | 2;
		}
		if (strchr(MODULE_SHIPPING_OZPOST_MAILDAYS, "Wednesday")) {
			$mail = $mail | 4;
		}
		if (strchr(MODULE_SHIPPING_OZPOST_MAILDAYS, "Thursday")) {
			$mail = $mail | 8;
		}
		if (strchr(MODULE_SHIPPING_OZPOST_MAILDAYS, "Friday")) {
			$mail = $mail | 16;
		}
		if (strchr(MODULE_SHIPPING_OZPOST_MAILDAYS, "Saturday")) {
			$mail = $mail | 32;
		}
		if (strchr(MODULE_SHIPPING_OZPOST_MAILDAYS, "Sunday")) {
			$mail = $mail | 64;
		}
		
		$maildays = "&maildays=" . $mail;
		
		if (MODULE_SHIPPING_OZPOST_EF != "None") {     // estimated days format (Date/Days/none
			$ef = (MODULE_SHIPPING_OZPOST_EF == "Days") ? "&ef=0" : "&ef=1";
		}
		
		if (MODULE_SHIPPING_OZPOST_DEADLINE > 0) {
			$deadline = "&deadline=" . MODULE_SHIPPING_OZPOST_DEADLINE;
		} // deadline for same day mailings
		if (MODULE_SHIPPING_OZPOST_LEADTIME > 0) {
			$leadtime = "&leadtime=" . MODULE_SHIPPING_OZPOST_LEADTIME;
		}  // leadtime for delayed mailings
		
		$vars = "";
		if ($dest_country == "AU") {
			// create the TNT variables
			$tmp = explode(", ", MODULE_SHIPPING_OZPOST_TYPE_TNT); //  echo sizeof($tmp)  ; exit ;
			if ((sizeof($tmp) > 0) && ($tmp[0] != "--none--") && (MODULE_SHIPPING_OZPOST_TNT_USER != "")) {
				$vars .= "&TNTaccount=" . MODULE_SHIPPING_OZPOST_TNT_ACCT . "&TNTusername=" . MODULE_SHIPPING_OZPOST_TNT_USER . "&TNTpassword=" . MODULE_SHIPPING_OZPOST_TNT_PSWD;
			}
			
			// create the StarTrack variables
			$tmp = explode(", ", MODULE_SHIPPING_OZPOST_TYPE_STA);
			if ((sizeof($tmp) > 0) && ($tmp[0] != "--none--") && (MODULE_SHIPPING_OZPOST_STA_ACCT != "")) {
				$vars .= "&STAaccount=" . MODULE_SHIPPING_OZPOST_STA_ACCT . "&STAusername=" . MODULE_SHIPPING_OZPOST_STA_USER . "&STApassword=" . MODULE_SHIPPING_OZPOST_STA_PSWD . "&STAkey=" . MODULE_SHIPPING_OZPOST_STA_KEY;
			}
			
			// create the SmartSend variables
			$tmp = explode(", ", MODULE_SHIPPING_OZPOST_TYPE_SMS);
			if ((sizeof($tmp) > 0) && ($tmp[0] != "--none--") && (MODULE_SHIPPING_OZPOST_SMS_EMAIL != "")) {
				$vars .= "&SMSemail=" . MODULE_SHIPPING_OZPOST_SMS_EMAIL . "&SMStype=" . MODULE_SHIPPING_OZPOST_SMS_TYPE . "&SMSpassword=" . MODULE_SHIPPING_OZPOST_SMS_PASS;
			}
			
			
			// create the FastWay variables //
			$tmp = explode(", ", MODULE_SHIPPING_OZPOST_TYPE_FW);
			if ((sizeof($tmp) > 0) && ($tmp[0] != "--none--")) {
				$fastway = substr(strtoupper(MODULE_SHIPPING_OZPOST_FWF), 0, 3);
				if ($fastway == "DIS") {
					$fastway = null;
				}
				$vars .= ($fastway) ? "&FastWay=" . $fastway : "";
				if (MODULE_SHIPPING_OZPOST_FW_FREQ == "Yes") {
					$vars .= "f";
				} // 'f' = frequent user flag/trigger/id
				if (MODULE_SHIPPING_OZPOST_FW_FREQ_SPBW != "") {
					$vars .= "s" . MODULE_SHIPPING_OZPOST_FW_FREQ_SPBW;
				} // 's' = Special rates
				
				if (MODULE_SHIPPING_OZPOST_FW_SAT0 != "") {
					$vars .= "&fwA5blue=" . MODULE_SHIPPING_OZPOST_FW_SAT0;
				} //  Custom rates A5 Satchel
				if (MODULE_SHIPPING_OZPOST_FW_SAT1 != "") {
					$vars .= "&fwA4blue=" . MODULE_SHIPPING_OZPOST_FW_SAT1;
				} //  Custom rates A4 Satchel
				if (MODULE_SHIPPING_OZPOST_FW_SAT2 != "") {
					$vars .= "&fwA3blue=" . MODULE_SHIPPING_OZPOST_FW_SAT2;
				} //  Custom rates A3 blue Satchel
				if (MODULE_SHIPPING_OZPOST_FW_SAT3 != "") {
					$vars .= "&fwA3orange=" . MODULE_SHIPPING_OZPOST_FW_SAT3;
				} //Custom rates A3 orange Satchel
				if (MODULE_SHIPPING_OZPOST_FW_SAT4 != "") {
					$vars .= "&fwA2blue=" . MODULE_SHIPPING_OZPOST_FW_SAT4;
				} //  Custom rates A2 Satchel
				
				if (MODULE_SHIPPING_OZPOST_FW_BOXS != "") {
					$vars .= "&fwBox1=" . MODULE_SHIPPING_OZPOST_FW_BOXS;
				} //  Custom rates small box
				if (MODULE_SHIPPING_OZPOST_FW_BOXM != "") {
					$vars .= "&fwBox2=" . MODULE_SHIPPING_OZPOST_FW_BOXM;
				} //  Custom rates medium box
				if (MODULE_SHIPPING_OZPOST_FW_BOXL != "") {
					$vars .= "&fwBox3=" . MODULE_SHIPPING_OZPOST_FW_BOXL;
				} //  Custom rates large box
			}
			
			
			// create the Ego variables //
			$tmp = explode(", ", MODULE_SHIPPING_OZPOST_TYPE_EGO);
			if ((sizeof($tmp) > 0) && ($tmp[0] != "--none--")) {
				$vars .= "&ego=1";
			}
			
			// create the Hynter Express variables //
			$tmp = explode(", ", MODULE_SHIPPING_OZPOST_TYPE_HX);
			if ((sizeof($tmp) > 0) && ($tmp[0] != "--none--")) {
				if ((MODULE_SHIPPING_OZPOST_HX_USER)) {
					$vars .= "&HXusername=" . MODULE_SHIPPING_OZPOST_HX_USER;
				}
				if ((MODULE_SHIPPING_OZPOST_HX_PSWD)) {
					$vars .= "&HXpassword=" . MODULE_SHIPPING_OZPOST_HX_PSWD;
				}
				if ((MODULE_SHIPPING_OZPOST_HX_CUST)) {
					$vars .= "&HXcustomer=" . MODULE_SHIPPING_OZPOST_HX_CUST;
				}
				if ((MODULE_SHIPPING_OZPOST_HX_FUELLEVY)) {
					$vars .= "&HXfuellevy=" . MODULE_SHIPPING_OZPOST_HX_FUELLEVY;
				}
			}
			
			// Create the SDL variable
			$tmp = explode(", ", MODULE_SHIPPING_OZPOST_TYPE_SDL);
			$sdl = substr(strtoupper(MODULE_SHIPPING_OZPOST_SDL), 0, 3);
			if ($sdl == "DIS") {
				$sdl = null;
			} else {
				$vars .= "&sendle=" . MODULE_SHIPPING_OZPOST_TYPE_SDL;
				$this->allowed_methods[] = 'Sendle';
			}
		} // create the Skippy variables //
		else  // Not AU 	 if($dest_country != "AU")
		{
			$tmp = explode(", ", MODULE_SHIPPING_OZPOST_TYPE_SKP);
			if ((sizeof($tmp) > 0) && ($tmp[0] != "--none--")) {
				$vars .= "&skp=1";
				if (MODULE_SHIPPING_OZPOST_SKP_CUST != "") {
					$vars .= "&SKPcust=" . MODULE_SHIPPING_OZPOST_SKP_CUST;
				}
			}
		}
		
		// create the Couriers Please variables -  Could be domestic or international
		$tmp = explode(", ", MODULE_SHIPPING_OZPOST_TYPE_CPL);
		if ((sizeof($tmp) > 0) && ($tmp[0] != "--none--")) {
			if ((MODULE_SHIPPING_OZPOST_CPL_ACCT != "") && (MODULE_SHIPPING_OZPOST_CPL_KEY != "")) {
				$vars .= "&CPLacct=" . MODULE_SHIPPING_OZPOST_CPL_ACCT . "&CPLkey=" . MODULE_SHIPPING_OZPOST_CPL_KEY;
			} else {
				$vars .= "&CPLv3=1";
			}
			
			if (MODULE_SHIPPING_OZPOST_CPL_METRO > 0) {
				$vars .= "&CPLml=" . MODULE_SHIPPING_OZPOST_CPL_METRO;
			}
			if (MODULE_SHIPPING_OZPOST_CPL_EZY > 0) {
				$vars .= "&CPLel=" . MODULE_SHIPPING_OZPOST_CPL_EZY;
			}
			if (MODULE_SHIPPING_OZPOST_CPL_SAT0 > 0) {
				$vars .= "&CPLsat0=" . MODULE_SHIPPING_OZPOST_CPL_SAT0;
			}
			if (MODULE_SHIPPING_OZPOST_CPL_SAT1 > 0) {
				$vars .= "&CPLsat1=" . MODULE_SHIPPING_OZPOST_CPL_SAT1;
			}
			if (MODULE_SHIPPING_OZPOST_CPL_SAT2 > 0) {
				$vars .= "&CPLsat2=" . MODULE_SHIPPING_OZPOST_CPL_SAT2;
			}
			if (MODULE_SHIPPING_OZPOST_CPL_SAT3 > 0) {
				$vars .= "&CPLsat3=" . MODULE_SHIPPING_OZPOST_CPL_SAT3;
			}
		}
		
		
		// create the Transdirect variables -  Could be domestic or international //
		$tmp = explode(", ", MODULE_SHIPPING_OZPOST_TYPE_TRD);
		if ((sizeof($tmp) > 0) && ($tmp[0] != "--none--")) {
			$vars .= "&TransDirect=1";
			
			if ((MODULE_SHIPPING_OZPOST_TRD_USER)) {
				$vars .= "&TRDusername=" . MODULE_SHIPPING_OZPOST_TRD_USER;
			}
			if ((MODULE_SHIPPING_OZPOST_TRD_PSWD)) {
				$vars .= "&TRDpassword=" . MODULE_SHIPPING_OZPOST_TRD_PSWD;
			}
		}
		
		
		// Get and use Suburb names if available - (mainly for couriers due to the way their zones are organised)
		if (MODULE_SHIPPING_OZPOST_ORIGIN_SUBURB != "") {
			$Osub = "&Osub=" . urlencode(MODULE_SHIPPING_OZPOST_ORIGIN_SUBURB);
		}
		
		if (isset($_POST['destSuburb']) != "") { // BMH
			$Dsub = "&Dsub=" . urlencode($_POST['destSuburb']);
		} else {
			if (isset($order->delivery['city']) != "") { // BMH
				$Dsub = "&Dsub=" . urlencode($order->delivery['city']);
			} else {
				if (isset($order->delivery['suburb']) != "") { // BMH
					$Dsub = "&Dsub=" . urlencode($order->delivery['suburb']);
				}
                else {  //BMH
                    $Dsub = "";
                }
			}
		}
		
		if ($dg == 1) {
			$vars .= "&dg=1";
			echo "Your shopping cart contains goods marked as \"dangerous\". Airmail is unavailable.";
		}
		
		$control_data = $control_tare . "&restrain_dimensions=$restrain&enable_debug=$enable_debug.$customPack";
		if ($NoPack === 1) {
			$control_data .= "&NoPack=1";
		}
       
		$control_data .= "&fromcode=$fromcode$Osub&destcode=$dcode$Dsub&flags=$flags&host=$this->HOST&storecode=" . SHIPPING_ORIGIN_ZIP . "&version=$this->VERSION$vars$ef$deadline$maildays$leadtime";
        
		
		$query = "/quotefor.php?host=$this->HOST" . "_" . SHIPPING_ORIGIN_ZIP;
		$result = $this->_get_from_ozpostnet($query, $items, $control_data);

		// test for  error
		if (((substr($result, 0, 7)) != "<error>") && ($result)) {
			libxml_use_internal_errors(true);
			$xmlQuotes = simplexml_load_string(
				"$result"
			); //   $xmlQuotes = new SimpleXMLElement($result)  ; // Parse the .xml results into an array //
			
			if ($xmlQuotes !== false) {
				$order->delivery['city'] = urldecode(
					(string)$xmlQuotes->information[0]->destsuburb
				); // This what the *server* used. Should be same as client specified, but not always (due to typo's, Saint vs St. etc)
				
				$orig = urldecode((string)($xmlQuotes->information[0]->fromsuburb));
				if (MODULE_SHIPPING_OZPOST_ORIGIN_SUBURB != $orig) {
					$db->Execute(
						"update " . TABLE_CONFIGURATION . " set configuration_value = \"$orig\" where configuration_key = \"MODULE_SHIPPING_OZPOST_ORIGIN_SUBURB\""
					);
				}
				
				if (MODULE_SHIPPING_OZPOST_SHOW_PARCEL == "Yes") {
					if (MODULE_SHIPPING_OZPOST_DEBUG == "Yes") {
						echo "<div><strong>>> Parcel_Build <<<br></strong></div>";
					}
					if ($parcelQty > 1) {
						echo "<div><textarea rows=7 cols=100>";
						echo (string)$xmlQuotes->parcel_build;
						echo "</textarea></div>";
					} else {
						echo (string)"<div>Weight " . $xmlQuotes->information[0]->calculated_parcel_weight_kg . "kg, Dims " . $xmlQuotes->information[0]->calculated_parcel_dims_cm . "</div>";
					}
				}
				
				if ((MODULE_SHIPPING_OZPOST_DEBUG == "Yes") && (!stristr($_SERVER['REQUEST_URI'], "checkout"))) {
					echo "<div><strong>>> Server Returned <<<br></strong><textarea rows=50 cols=100>";
					print_r($xmlQuotes);
					echo "</textarea><div>";
				}
				$ozicon = (strstr(
					$_SERVER['REQUEST_URI'],
					"main_page=checkout_shipping"
				)) ? $this->logo : " ";  // no icon  unless checking out (where it only shows once - as a label)
				
				////   Expiration Email management \\
				$days = intval($xmlQuotes->information[0]->expires);
				$db->Execute(
					"update " . TABLE_CONFIGURATION . " set configuration_value = \"" . $days . "\" where configuration_key = \"MODULE_SHIPPING_OZPOST_EXPIRES\""
				);
				
				if (($days <= 14) && ($days > 0) && (MODULE_SHIPPING_OZPOST_EMAIL_FLAG == 0)) {
					$db->Execute(
						"update " . TABLE_CONFIGURATION . " set configuration_value = 1 where configuration_key = \"MODULE_SHIPPING_OZPOST_EMAIL_FLAG\""
					);
					//zen_mail(
					//	"Ozpost Subscriptions",
					//	MODULE_SHIPPING_OZPOST_EMAIL,
					//	"Important Notice",
					//	"Please be advised that your subscription to ozpost.net will expire in " . $days . " Days. <br>Subscriptions can be renewed at https://www.ozpost.net/my-account/",
					//	"The Ozpost Shipping Module",
					//	EMAIL_FROM
					//);
				}
				
				if (($days <= 7) && ($days > 0) && (MODULE_SHIPPING_OZPOST_EMAIL_FLAG < 2)) {
					$db->Execute(
						"update " . TABLE_CONFIGURATION . " set configuration_value = 2 where configuration_key = \"MODULE_SHIPPING_OZPOST_EMAIL_FLAG\""
					);
					//zen_mail(
					//	"Ozpost Subscriptions",
					//	MODULE_SHIPPING_OZPOST_EMAIL,
					//	"Important Notice",
					//	"Please be advised that your subscription to ozpost.net will expire in " . $days . " Days. <br>Subscriptions can be renewed at https://shop.ozpost.net/index.php?main_page=product_info&cPath=43&products_id=207",
					//	"The Ozpost Shipping Module",
					//	EMAIL_FROM
					//);
				}
				
				if (($days <= 3) && ($days > 0) && (MODULE_SHIPPING_OZPOST_EMAIL_FLAG < 3)) {
					$db->Execute(
						"update " . TABLE_CONFIGURATION . " set configuration_value = 3 where configuration_key = \"MODULE_SHIPPING_OZPOST_EMAIL_FLAG\""
					);
					//zen_mail(
					//	"Ozpost Subscriptions",
					//	MODULE_SHIPPING_OZPOST_EMAIL,
					//	"Warning",
					//	"Please be advised that your subscription to ozpost.net will expire in " . $days . " Days. <br>Subscriptions can be renewed at https://shop.ozpost.net/index.php?main_page=product_info&cPath=43&products_id=207",
					//	"The Ozpost Shipping Module",
					//	EMAIL_FROM
					//);
				}
				
				
				if (($days <= 0) && (MODULE_SHIPPING_OZPOST_EMAIL_FLAG < 4) && (MODULE_SHIPPING_OZPOST_EMAIL_FLAG != 0)) {
					$db->Execute(
						"update " . TABLE_CONFIGURATION . " set configuration_value = 4 where configuration_key = \"MODULE_SHIPPING_OZPOST_EMAIL_FLAG\""
					);
					//zen_mail(
					//	"Ozpost Subscriptions",
					//	MODULE_SHIPPING_OZPOST_EMAIL,
					//	"ERROR ALERT",
					//	"Your subscription to ozpost.net HAS EXPIRED.<br> Subscriptions can be renewed at https://www.ozpost.net/my-account/",
					//	"The Ozpost Shipping Module",
					//	EMAIL_FROM
					//);
				}
				
				if (($days > 14) && (MODULE_SHIPPING_OZPOST_EMAIL_FLAG != 0)) {  // Reset flag for next time
					$db->Execute(
						"update " . TABLE_CONFIGURATION . " set configuration_value = 0 where configuration_key = \"MODULE_SHIPPING_OZPOST_EMAIL_FLAG\""
					);
				}
				
				/////////////////////////////////////////////////////////////
				//  Initialise our quote array
				$this->quotes = array('id' => $this->code, 'module' => "$ozicon");
				$quote = array();
				
				$displayed = 0; // flags to prevent superfluous Satchels/Boxes being presented
				//bitmapped: (28 bits)
				// 1	Prepaid Satchels
				// 2 	Express Satchel
				// 4	Express +Signature
				// 8  Standard Air Satchel
				// 16 Standard Air Satchel +sig
				// 32 FW Satchels
				// 64 Prepaid Satchels + sig
				// 128 CnS Satch  -  GONE Oct 2017
				// 256 Express Insured (inc Sign)  2/1
				// 512 Insured Satchel  (inc Sign)
				// 1024 STA White Sats  2/8
				// 2048 CPL Sats 2/32
				// 4096 Standard Air Satchel Insured +sig
				// 8192 Smart Send  AAE Satchels 2/128
				// 16384 Unused
				// 32768 Smart Send  AAE Prepaid Satchel (receipted) 3/2
				// 65536 Smart Send  AAE Prepaid Satchel (insured) 3/2
				// 131072 Smart Send AE Satchel (receipted + insured) 3/4
				// 262144  FW Boxes
				// 524288 FW labels
				// 1048576 CnS Express  -  GONE Oct 2017
				// 2097152  Standard Air Satchel Insured
				// 4194304 Insured Satchel without signature
				// 8388608 Express Insured Satchel without signature
				// 16777216 Express Air Satchel
				// 33554432 Express Air Satchel Insured
				// 67108864 Courier Air Satchel
				// 134217728 Courier Air Satchel Insured
				
				//  loop through the quotes retrieved to get handling & service fees (rego/Insurance)//
				
				foreach ($xmlQuotes->quote as $quote) {       // Quotes returned
					
					//   if ((in_array($quote->id, $allowed_methods)) || 1 ) {  // Continue if an allowed method (all the time until debuggered)
					//   if (in_array("Express", $this->allowed_methods)) { }
					
					$handlingFee = null; // nullify handling fee - We test to ensure its set for a valid quote (unset means the result was filtered)
					
					switch ($quote->id) {
						case "Error";  // Only show errors in shopping cart and popup estimator windows,  else it can mess up screen
							if (((strstr($_SERVER['REQUEST_URI'], "main_page=shopping_cart")) || (strstr(
										$_SERVER['REQUEST_URI'],
										"main_page=popup_shipping_estimator"
									))) && (MODULE_SHIPPING_OZPOST_MSG == "Yes")) {
								echo "<div class=\"messageStackCaution\">" . "$quote->carrier" . " : " . "$quote->description" . "</div>";
							}
							break;
						// Letters //
						case "SLET";
						case "LL1";
						case "LL2";
						case "LL3";
							if (in_array("Aust Standard", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING;
							}
							break;
						
						case "SLETi";
						case "LL1i";
						case "LL2i";
						case "LL3i";
							if (in_array("Aust Standard Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "SLETp";
						case "LL1p";
						case "LL2p";
						case "LL3p";
							if (in_array("Aust Priority", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LETP_HANDLING;
							}
							break;
						
						case "SLETpi";
						case "LL1pi";
						case "LL2pi";
						case "LL3pi";
							if (in_array("Aust Priority Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LETP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						case "REGdl";
						case "REGb4"; // Aust Registered
							if (in_array("Aust Registered", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "REGdli";
						case "REGb4i"; // Aust Registered Insured
							if (in_array("Aust Registered Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						case "EXLS";
						case "EXLM";
						case "EXLL";
							if (in_array("Aust Express", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING;
							}
							break;
						
						
						case "EXLSs";
						case "EXLMs";
						case "EXLLs";
							if (in_array("Aust Express +sig", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						case "EXLSsi";
						case "EXLMsi";
						case "EXLLsi";
							if (in_array("Aust Express Insured +sig", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						case "EXLSio";
						case "EXLMio";
						case "EXLLio";
							if (in_array("Aust Express Insured (no sig)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;


						//  satchels //
						case "PPS5";
						case "PPS5K";
						case "PPS3";
							if (!($displayed & 1)) {   //  only one per group
								if ((in_array("500g Satchel.", $this->allowed_methods)) && ($quote->id == "PPS5")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING;
									if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1R == '1') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
									} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1R == '2') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
									}
								} else {
									if ((in_array("1kg Satchel.", $this->allowed_methods)) && ($quote->id == "PPS1")) {
										$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING;
										//   if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '1') {
										//       $quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.05 );
										//   } elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '2') {
										//       $quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.125);
										//   }
									} else {
										if ((in_array(
												"3kg Satchel.",
												$this->allowed_methods
											)) && ($quote->id == "PPS3")) {
											$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING;
											if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '1') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
											} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '2') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
											}
										} else {
											if ((in_array(
													"5kg Satchel.",
													$this->allowed_methods
												)) && ($quote->id == "PPS5K")) {
												$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING;
												if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3R == '1') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
												} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3R == '2') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
												}
											}
										}
									}
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 1;
								}
							}
							break;
						
						case "PPS5r";
						case "PPS3r";
						case "PPS5Kr";
							if (!($displayed & 64)) {   //  only one per group
								if ((in_array(
										"500g Satchel +Signature",
										$this->allowed_methods
									)) && ($quote->id == "PPS5r")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
									if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1R == '1') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
									} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1R == '2') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
									}
								} else {
									if ((in_array(
											"1kg Satchel +Signature",
											$this->allowed_methods
										)) && ($quote->id == "PPS1r")) {
										$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
										// if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '1') {
										//		$quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.05 );
										// } elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '2') {
										//		$quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.125);
										// }
										
									} else {
										if ((in_array(
												"3kg Satchel +Signature",
												$this->allowed_methods
											)) && ($quote->id == "PPS3r")) {
											$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
											if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '1') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
											} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '2') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
											}
										} else {
											if ((in_array(
													"5kg Satchel +Signature",
													$this->allowed_methods
												)) && ($quote->id == "PPS5Kr")) {
												$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
												if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3R == '1') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
												} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3R == '2') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
												}
											}
										}
									}
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 64;
								}
							}
							break;
						
						
						case "PPS5io";
						case "PPS3io";
						case "PPS5Kio";//   insured without sig (under $300 only)
							if (!($displayed & 4194304)) {   //  only one per group
								if ((in_array(
										"500g Insured Satchel (no sig)",
										$this->allowed_methods
									)) && ($quote->id == "PPS5io")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
									if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1R == '1') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
									} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1R == '2') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
									}
								} else {
									if ((in_array(
											"1kg Insured Satchel (no sig)",
											$this->allowed_methods
										)) && ($quote->id == "PPS1io")) {
										$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
										// if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '1') {
										// 	$quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.05 );
										// } elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '2') {
										//	$quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.125);
										//}
									
									} else {
										if ((in_array(
												"3kg Insured Satchel (no sig)",
												$this->allowed_methods
											)) && ($quote->id == "PPS3io")) {
											$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
											if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '1') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
											} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '2') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
											}
										} else {
											if ((in_array(
													"5kg Insured Satchel (no sig)",
													$this->allowed_methods
												)) && ($quote->id == "PPS5Kio")) {
												$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
												if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3R == '1') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
												} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3R == '2') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
												}
											}
										}
									}
								}
								
								if (isset($handlingFee)) {
									$displayed = $displayed | 4194304;
								}
							}
							
							break;
						
						case "PPS5i";
						case "PPS3i";
						case "PPS5Ki";
							if (!($displayed & 512)) {   //  only one per group
								if ((in_array(
										"500g Insured Satchel (inc Sign)",
										$this->allowed_methods
									)) && ($quote->id == "PPS5i")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
									if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1R == '1') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
									} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1R == '2') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
									}
								} else {
									if ((in_array(
											"1kg Insured Satchel (inc Sign)",
											$this->allowed_methods
										)) && ($quote->id == "PPS1i")) {
										$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
										// if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '1') {
										//	$quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.05 );
										// } elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '2') {
										//	$quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.125);
										//}
									
									} else {
										if ((in_array(
												"3kg Insured Satchel (inc Sign)",
												$this->allowed_methods
											)) && ($quote->id == "PPS3i")) {
											$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
											if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '1') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
											} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R == '2') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
											}
										} else {
											if ((in_array(
													"5kg Insured Satchel (inc Sign)",
													$this->allowed_methods
												)) && ($quote->id == "PPS5Ki")) {
												$handlingFee = MODULE_SHIPPING_OZPOST_PPS_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
												if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3R == '1') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
												} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3R == '2') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
												}
											}
										}
									}
								}
								
								if (isset($handlingFee)) {
									$displayed = $displayed | 512;
								}
							}
							
							break;
						// express //
						case "PPSE5";
						case "PPSE1";
						case "PPSE3";
						case "PPSE5K";
							if (!($displayed & 2)) {   //  only one per group
								if ((in_array(
										"500g Express Satchel.",
										$this->allowed_methods
									)) && ($quote->id == "PPSE5")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING;
									if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1E == '1') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
									} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1E == '2') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
									}
								} else {
									if ((in_array(
											"1kg Express Satchel.",
											$this->allowed_methods
										)) && ($quote->id == "PPSE1")) {
										$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING;
										// if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '1') {
										//	$quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.05 );
										//} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '2') {
										//	$quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.125);
										//}
									} else {
										if ((in_array(
												"3kg Express Satchel.",
												$this->allowed_methods
											)) && ($quote->id == "PPSE3")) {
											$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING;
											if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '1') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
											} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '2') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
											}
										} else {
											if ((in_array(
													"5kg Express Satchel.",
													$this->allowed_methods
												)) && ($quote->id == "PPSE5K")) {
												$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING;
												if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3E == '1') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
												} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3E == '2') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
												}
											}
										}
									}
								}
								
								if (isset($handlingFee)) {
									$displayed = $displayed | 2;
								}
							}
							break;
						// express + sig //
						case "PPSP5";
						case "PPSP1";
						case "PPSP3";
						case "PPSP5K";
							if (!($displayed & 4)) {   //  only one per group
								if ((in_array(
										"500g Express +Signature",
										$this->allowed_methods
									)) && ($quote->id == "PPSP5")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
									if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1E == '1') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
									} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1E == '2') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
									}
								} else {
									if ((in_array(
											"1kg Express +Signature",
											$this->allowed_methods
										)) && ($quote->id == "PPSP1")) {
										$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
//                                if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '1') {
//                                    $quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.05 );
//                                } elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '2') {
//                                    $quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.125);
//                                }
									} else {
										if ((in_array(
												"3kg Express +Signature",
												$this->allowed_methods
											)) && ($quote->id == "PPSP3")) {
											$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
											if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '1') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
											} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '2') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
											}
										} else {
											if ((in_array(
													"5kg Express +Signature",
													$this->allowed_methods
												)) && ($quote->id == "PPSP5K")) {
												$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
												if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3E == '1') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
												} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3E == '2') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
												}
											}
										}
									}
								}
								
								if (isset($handlingFee)) {
									$displayed = $displayed | 4;
								}
							}
							break;
// express insured
						case "PPSP5io";
						case "PPSP1io";
						case "PPSP3io";
						case "PPSP5Kio"; //   express insured without sig (under $300 only)
							if (!($displayed & 8388608)) {   //  only one per group
								if ((in_array(
										"500g Express Insured (no sig)",
										$this->allowed_methods
									)) && ($quote->id == "PPSP5io")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
									if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1E == '1') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
									} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1E == '2') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
									}
								} else {
									if ((in_array(
											"1kg Express Insured (no sig)",
											$this->allowed_methods
										)) && ($quote->id == "PPSP1io")) {
										$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
//                                if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '1') {
//                                    $quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.05 );
//                                } elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '2') {
//                                    $quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.125);
//                                }
									} else {
										if ((in_array(
												"3kg Express Insured (no sig)",
												$this->allowed_methods
											)) && ($quote->id == "PPSP3io")) {
											$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
											if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '1') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
											} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '2') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
											}
										} else {
											if ((in_array(
													"5kg Express Insured (no sig)",
													$this->allowed_methods
												)) && ($quote->id == "PPSP5Kio")) {
												$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
												if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3E == '1') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
												} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3E == '2') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
												}
											}
										}
									}
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 8388608;
								}
							}
							break;
						
						// express insured + sig
						case "PPSP5i";
						case "PPSP1i";
						case "PPSP3i";
						case "PPSP5Ki";
							if (!($displayed & 256)) {   //  only one per group
								if ((in_array(
										"500g Express Insured (inc Sign)",
										$this->allowed_methods
									)) && ($quote->id == "PPSP5i")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
									if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1E == '1') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
									} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT1E == '2') {
										$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
									}
								} else {
									if ((in_array(
											"1kg Express Insured (inc Sign)",
											$this->allowed_methods
										)) && ($quote->id == "PPSP1i")) {
										$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
//                                if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '1') {
//                                    $quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.05 );
//                                } elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '2') {
//                                    $quote->cost = (float) $quote->cost - ((float) $quote->cost * 0.125);
//                                }
									} else {
										if ((in_array(
												"3kg Express Insured (inc Sign)",
												$this->allowed_methods
											)) && ($quote->id == "PPSP3i")) {
											$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
											if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '1') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
											} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E == '2') {
												$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
											}
										} else {
											if ((in_array(
													"5kg Express Insured (inc Sign)",
													$this->allowed_methods
												)) && ($quote->id == "PPSP5Ki")) {
												$handlingFee = MODULE_SHIPPING_OZPOST_PPSE_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
												if (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3E == '1') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.05);
												} elseif (MODULE_SHIPPING_OZPOST_AP_DISCOUNT3E == '2') {
													$quote->cost = (float)$quote->cost - ((float)$quote->cost * 0.125);
												}
											}
										}
									}
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 256;
								}
							}
							break;
						
						
						// Parcels
						case "RPP";
							if (in_array("Regular", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_RPP_HANDLING;
							}
							break;
						
						// Insured w/out sig (only avail if under $300)
						case "RPPio";
							if (in_array("Insured (no sig)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_RPP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "RPPi";
							if (in_array("Insured (inc Sign)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_RPP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						case "REG";
							if (in_array("Regular +Signature", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_RPP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "EXP";
							if (in_array("Express", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_RPP_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING;
							}
							break;
						
						case "PLT";
							if (in_array("Express +Signature", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_RPP_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "PLTi";
							if (in_array("Insured Express (inc Sign)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_RPP_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						// Insured w/out sig (only avail if under $300)
						case "PLTio";
							if (in_array("Insured Express (no sig)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_RPP_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						case "COD";
							if (in_array("Cash on Delivery", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_COD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						//                    case "CNS500": case "CNS3K": case "CNS5K": case "CNSbx1": case "CNSbx2":
						//                        if (!($displayed & 128)) {   //  only one per group
						//                            if ((in_array("500g Satchel", $this->allowed_methods)) && ($quote->id == "CNS500")) {
						//                                $handlingFee = MODULE_SHIPPING_OZPOST_CNS_HANDLING;
						//                            } elseif ((in_array("3kg Satchel", $this->allowed_methods)) && ($quote->id == "CNS3K")) {
						//                                $handlingFee = MODULE_SHIPPING_OZPOST_CNS_HANDLING;
						//                            } elseif ((in_array("5kg Satchel", $this->allowed_methods)) && ($quote->id == "CNS5K")) {
						//                                $handlingFee = MODULE_SHIPPING_OZPOST_CNS_HANDLING;
						//                            } elseif ((in_array("Small box 1kg", $this->allowed_methods)) && ($quote->id == "CNSbx1")) {
						//                                $handlingFee = MODULE_SHIPPING_OZPOST_CNSB_HANDLING;
						//                            } elseif ((in_array("Medium box 3kg", $this->allowed_methods)) && ($quote->id == "CNSbx2")) {
						//                                $handlingFee = MODULE_SHIPPING_OZPOST_CNSB_HANDLING;
						//                            }
						//
						//                            if (isset($handlingFee))
						//                                $displayed = $displayed | 128;
						//                        }
						//                        break;
						//                    //   CNS Express
						//                    case "CNS500e": case "CNS3Ke": case "CNS5Ke":
						//                        if (!($displayed & 1048576)) {   //  only one per group
						//                            if ((in_array("500g Express Satchel", $this->allowed_methods)) && ($quote->id == "CNS500e")) {
						//                                $handlingFee = MODULE_SHIPPING_OZPOST_CNS_HANDLING;
						//                            } elseif ((in_array("3kg Express Satchel", $this->allowed_methods)) && ($quote->id == "CNS3Ke")) {
						//                                $handlingFee = MODULE_SHIPPING_OZPOST_CNS_HANDLING;
						//                            } elseif ((in_array("5kg Express Satchel", $this->allowed_methods)) && ($quote->id == "CNS5Ke")) {
						//                                $handlingFee = MODULE_SHIPPING_OZPOST_CNS_HANDLING;
						//                            }
						//
						//                            if (isset($handlingFee))
						//                                $displayed = $displayed | 1048576;
						//                        }
						//                        break;

						// End of Australia Post  letters & parcels   //////////////////////

						//TNT
						case "TNT712"; // 9pm Express
							if (in_array("Overnight Express by 9:00am", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;
						
						case "TNTEX10"; // 10:00 am
							if (in_array("Overnight Express by 10:00am", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;
						
						case "TNTEX12"; // 12:00 pm
							if (in_array("Overnight Express by 12:00pm", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;
						
						case "TNT75"; // overnight express by 5pm (was Overnight First Class)
							if (in_array("Overnight Express by 5:00pm", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;
						
						case "TNT76"; // Road Express
							if (in_array("Road Express", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;
						
						case "TNT717B";
							if (in_array("Technology Express - Sensitive Express", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;
						
						case "TNT73";
							if (in_array("ONFC Satchel", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;
						
						case "TNT712i"; // 9pm Express
							if (in_array("Overnight Express by 9:00am Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;
						
						case "TNTEX10i"; // 10:00 am
							if (in_array("Overnight Express by 10:00am Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;
						
						case "TNTEX12i"; // 12:00 pm
							if (in_array("Overnight Express by 12:00pm Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;
						
						case "TNT75i"; // overnight express by 5pm (was Overnight First Class)
							if (in_array("Overnight Express by 5:00pm Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;
						
						case "TNT76i"; // Road Express
							if (in_array("Road Express Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;
						
						case "TNT717Bi";
							if (in_array("Technology Express - Sensitive Express Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;
						
						case "TNT73i";
							if (in_array("ONFC Satchel Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TNT_HANDLING;
							}
							break;

						//  Smart Send   //
						case "SMSCPR";
							if (in_array("Couriers Please Road", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						///  Smart Send  AAE Satchels
						case "SMSAAE1K";
						case "SMSAAE3K";
						case "SMSAAE5K";
							if (!($displayed & 8192)) {
								if ((in_array(
										"AAE 1kg Prepaid Satchel",
										$this->allowed_methods
									)) && ($quote->id == "SMSAAE1K")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
								} elseif ((in_array(
										"AAE 3kg Prepaid Satchel",
										$this->allowed_methods
									)) && ($quote->id == "SMSAAE3K")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
								} elseif ((in_array(
										"AAE 5kg Prepaid Satchel",
										$this->allowed_methods
									)) && ($quote->id == "SMSAAE5K")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
								}
								
								if (isset($handlingFee)) {
									$displayed = $displayed | 8192;
								}
							}
							
							break;
						
						///  Smart Send
						case "SMSTNT9";
							if (in_array("TNT : Overnight by 9am", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						///  Smart Send
						case "SMSTNT12";
							if (in_array("TNT : Overnight by 12pm", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSTNT5";
							if (in_array("TNT : Overnight by 5pm", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSTNTR";
							if (in_array("TNT : Road", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSFW";
							if (in_array("Fastway : National Road", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSFWL";
							if (in_array("Fastway : Local", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSFWS";
							if (in_array("Fastway : Satchels", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						
						case "SMSAAEP";
							if (in_array("AAE : Express Premium", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSAAES";
							if (in_array("AAE : Express Saver", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSAAER";
							if (in_array("AAE : Road", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;

						//   SmartSend Receipted Delivery //
						case "SMSCPRr";
							if (in_array("Couriers Please Road (receipted)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						///  Smart Send  AAE regd Satchels
						case "SMSAAE1Kr";
						case "SMSAAE3Kr";
						case "SMSAAE5Kr";
							if (!($displayed & 32768)) {
								if ((in_array(
										"AAE 1kg Prepaid Satchel (receipted)",
										$this->allowed_methods
									)) && ($quote->id == "SMSAAE1Kr")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
								} elseif ((in_array(
										"AAE 3kg Prepaid Satchel (receipted)",
										$this->allowed_methods
									)) && ($quote->id == "SMSAAE3Kr")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
								} elseif ((in_array(
										"AAE 5kg Prepaid Satchel (receipted)",
										$this->allowed_methods
									)) && ($quote->id == "SMSAAE5Kr")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
								}
								
								if (isset($handlingFee)) {
									$displayed = $displayed | 32768;
								}
							}
							
							break;
						
						///  Smart Send
						case "SMSTNT9r";
							if (in_array("TNT : Overnight by 9am (receipted)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						///  Smart Send
						case "SMSTNT12r";
							if (in_array("TNT : Overnight by 12pm (receipted)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSTNT5r";
							if (in_array("TNT : Overnight by 5pm (receipted)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSTNTRr";
							if (in_array("TNT : Road (receipted)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSFWr";
							if (in_array("Fastway : National Road (receipted)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSFWLr";
							if (in_array("Fastway : Local (receipted)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSFWSr";
							if (in_array("Fastway : Satchels (receipted)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						
						case "SMSAAEPr";
							if (in_array("AAE : Express Premium (receipted)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSAAESr";
							if (in_array("AAE : Express Saver (receipted)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSAAERr";
							if (in_array("AAE : Road (receipted)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;

						// SmartSend Insured Delivery //
						case "SMSCPRi";
							if (in_array("Couriers Please Road (insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						///  Smart Send  AAE insured Satchels
						case "SMSAAE1Ki";
						case "SMSAAE3Ki";
						case "SMSAAE5Ki";
							if (!($displayed & 65536)) {
								if ((in_array(
										"AAE 1kg Prepaid Satchel (insured)",
										$this->allowed_methods
									)) && ($quote->id == "SMSAAE1Ki")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
								} elseif ((in_array(
										"AAE 3kg Prepaid Satchel (insured)",
										$this->allowed_methods
									)) && ($quote->id == "SMSAAE3Ki")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
								} elseif ((in_array(
										"AAE 5kg Prepaid Satchel (insured)",
										$this->allowed_methods
									)) && ($quote->id == "SMSAAE5Ki")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
								}
								
								if (isset($handlingFee)) {
									$displayed = $displayed | 65536;
								}
							}
							
							break;
						
						
						case "SMSTNT9i";
							if (in_array("TNT : Overnight by 9am (insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						
						case "SMSTNT12i";
							if (in_array("TNT : Overnight by 12pm (insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSTNT5i";
							if (in_array("TNT : Overnight by 5pm (insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSTNTRi";
							if (in_array("TNT : Road (insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSFWi";
							if (in_array("Fastway : National Road (insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSFWLi";
							if (in_array("Fastway : Local (insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSFWSi";
							if (in_array("Fastway : Satchels (insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						
						case "SMSAAEPi";
							if (in_array("AAE : Express Premium (insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSAAESi";
							if (in_array("AAE : Express Saver (insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSAAERi";
							if (in_array("AAE : Road (insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;


						//   SmartSend Receipted + Insured Delivery ///
						case "SMSCPRri";
							if (in_array("Couriers Please Road (receipted + insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						///  Smart Send  AAE Satchels
						case "SMSAAE1Kri";
						case "SMSAAE3Kri";
						case "SMSAAE5Kri";
							if (!($displayed & 131072)) {
								if ((in_array(
										"AAE 1kg Prepaid Satchel (receipted + insured)",
										$this->allowed_methods
									)) && ($quote->id == "SMSAAE1Kri")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
								} elseif ((in_array(
										"AAE 3kg Prepaid Satchel (receipted + insured)",
										$this->allowed_methods
									)) && ($quote->id == "SMSAAE3Kri")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
								} elseif ((in_array(
										"AAE 5kg Prepaid Satchel (receipted + insured)",
										$this->allowed_methods
									)) && ($quote->id == "SMSAAE5Kri")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
								}
								
								if (isset($handlingFee)) {
									$displayed = $displayed | 131072;
								}
							}
							
							break;
						
						case "SMSTNT9ri";
							if (in_array("TNT : Overnight by 9am (receipted + insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						
						case "SMSTNT12ri";
							if (in_array("TNT : Overnight by 12pm (receipted + insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSTNT5ri";
							if (in_array("TNT : Overnight by 5pm (receipted + insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSTNTRri";
							if (in_array("TNT : Road (receipted + insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSFWri";
							if (in_array("Fastway : National Road (receipted + insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSFWLri";
							if (in_array("Fastway : Local (receipted + insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSFWSri";
							if (in_array("Fastway : Satchels (receipted + insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						
						case "SMSAAEPri";
							if (in_array("AAE : Express Premium (receipted + insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSAAESri";
							if (in_array("AAE : Express Saver (receipted + insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;
						
						case "SMSAAERri";
							if (in_array("AAE : Road (receipted + insured)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SMS_HANDLING;
							}
							break;

						//FastWay //
						case "FWB1";
						case "FWB2";
						case "FWB3";
							if (!($displayed & 262144)) {   //  only one per group
								if ((in_array("FW Small Box", $this->allowed_methods)) && ($quote->id == "FWB1")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_FWB_HANDLING;
								} elseif ((in_array(
										"FW Medium Box",
										$this->allowed_methods
									)) && ($quote->id == "FWB2")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_FWB_HANDLING;
								} elseif ((in_array(
										"FW Large Box",
										$this->allowed_methods
									)) && ($quote->id == "FWB3")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_FWB_HANDLING;
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 262144;
								}
							}
							break;
						
						// case "FWL";
						//  Zones - cheapest first //
						case "FWLbrown";
						case "FWLyellow";
						case "FWLblack";
						case "FWLblue";
						case "FWLlime";
						case "FWLpink";
						case "FWLred";
						case "FWLorange";
						case "FWLgreen";
						case "FWLwhite";
						case "FWLgrey";
							if (!($displayed & 524288)) {   //  only one per group
								if ((in_array("Brown Label", $this->allowed_methods)) && ($quote->id == "FWLbrown")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_FWL_HANDLING;
								} else {
									if ((in_array("Lime Label", $this->allowed_methods)) && ($quote->id == "FWLlime")) {
										$handlingFee = MODULE_SHIPPING_OZPOST_FWL_HANDLING;
									} else {
										if ((in_array(
												"Pink Label",
												$this->allowed_methods
											)) && ($quote->id == "FWLpink")) {
											$handlingFee = MODULE_SHIPPING_OZPOST_FWL_HANDLING;
										} else {
											if ((in_array(
													"Red Label",
													$this->allowed_methods
												)) && ($quote->id == "FWLred")) {
												$handlingFee = MODULE_SHIPPING_OZPOST_FWL_HANDLING;
											} else {
												if ((in_array(
														"Orange Label",
														$this->allowed_methods
													)) && ($quote->id == "FWLorange")) {
													$handlingFee = MODULE_SHIPPING_OZPOST_FWL_HANDLING;
												} else {
													if ((in_array(
															"Green Label",
															$this->allowed_methods
														)) && ($quote->id == "FWLgreen")) {
														$handlingFee = MODULE_SHIPPING_OZPOST_FWL_HANDLING;
													} else {
														if ((in_array(
																"White Label",
																$this->allowed_methods
															)) && ($quote->id == "FWLwhite")) {
															$handlingFee = MODULE_SHIPPING_OZPOST_FWL_HANDLING;
														} else {
															if ((in_array(
																	"Grey Label",
																	$this->allowed_methods
																)) && ($quote->id == "FWLgrey")) {
																$handlingFee = MODULE_SHIPPING_OZPOST_FWL_HANDLING;
															} else {
																if ((in_array(
																		"Black Label",
																		$this->allowed_methods
																	)) && ($quote->id == "FWLblack")) {
																	$handlingFee = MODULE_SHIPPING_OZPOST_FWL_HANDLING;
																} else {
																	if ((in_array(
																			"Blue Label",
																			$this->allowed_methods
																		)) && ($quote->id == "FWLblue")) {
																		$handlingFee = MODULE_SHIPPING_OZPOST_FWL_HANDLING;
																	} else {
																		if ((in_array(
																				"Yellow Label",
																				$this->allowed_methods
																			)) && ($quote->id == "FWLyellow")) {
																			$handlingFee = MODULE_SHIPPING_OZPOST_FWL_HANDLING;
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 524288;
								}
							}
							break;
						//      $d = "" ; $x = preg_split("/\+/", $quote->description );
						//        if($x[1]) $d = " (+".$x[1] ;
						//         $quote->description = ucfirst(preg_replace('/FWL/', "", $quote->id)) . " Label".$d;
						
						
						case "FWS0":
						case "FWS1":
						case "FWS3l":
						case "FWS3":
						case "FWS5":
							if (!($displayed & 32)) {   //  only one per group
								if ((in_array("A5 Satchel (500g)", $this->allowed_methods)) && ($quote->id == "FWS0")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_FWS_HANDLING;
								} elseif ((in_array(
										"A4 Satchel (1kg)",
										$this->allowed_methods
									)) && ($quote->id == "FWS1")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_FWS_HANDLING;
								} elseif ((in_array(
										"A3 Satchel (local)",
										$this->allowed_methods
									)) && ($quote->id == "FWS3l")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_FWS_HANDLING;
								} elseif ((in_array(
										"A3 Satchel (3kg)",
										$this->allowed_methods
									)) && ($quote->id == "FWS3")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_FWS_HANDLING;
								} elseif ((in_array(
										"A2 Satchel (5kg)",
										$this->allowed_methods
									)) && ($quote->id == "FWS5")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_FWS_HANDLING;
								}
								
								if (isset($handlingFee)) {
									$displayed = $displayed | 32;
								}
							}
							break;

						// Transdirect //
						case "TRDti":
							if (in_array("Toll/Ipec", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						case "TRDtp":
							if (in_array("Toll Priority Overnight", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						case "TRDts":
							if (in_array("Toll Priority Same Day", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						case "TRDmf":
							if (in_array("Mainfreight", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						case "TRDnl":
							if (in_array("Northline", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						case "TRDae":
							if (in_array("Allied Express", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						case "TRDcp":
							if (in_array("Couriers Please (Authority to leave)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						case "TRDcpsr":
							if (in_array("Couriers Please (Signature Required)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						case "TRDfw":
							if (in_array("Fastway", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						
						case "TRDtntr":
							if (in_array("TNT Road Express", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						case "TRDtnt9":
							if (in_array("TNT Overnight Express by 9:00am", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						case "TRDtnt10":
							if (in_array("TNT Overnight Express by 10:00am", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						case "TRDtnt12":
							if (in_array("TNT Overnight Express by 12:00pm", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						case "TRDtnt5":
							if (in_array("TNT Overnight Express by 5:00pm", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						
						case "TRDtntIntEE":
							if (in_array("TNT International Express", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_INT_HANDLING;
							}
							break;
						
						case "TRDtntIntDE":
							if (in_array("TNT International Document Express", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_INT_HANDLING;
							}
							break;
						
						case "TRDtntIntEco":
							if (in_array("TNT International Economy Express", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_INT_HANDLING;
							}
							break;
						case "TRDtollIntP":
							if (in_array("Toll International Priority", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_INT_HANDLING;
							}
							break;
						
						case "TRDtollIntD":
							if (in_array("Toll International Priority Document", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_INT_HANDLING;
							}
							break;
						
						// insured //
						case "TRDtii":
							if (in_array("Toll/Ipec Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDtpi":
							if (in_array("Toll Priority Overnight Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDtsi":
							if (in_array("Toll Priority Same Day Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING;
							}
							break;
						
						case "TRDaei":
							if (in_array("Allied Express Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDcpi":
							if (in_array("Couriers Please (Authority to leave) Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDcpsri":
							if (in_array("Couriers Please (Signature Required) Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						case "TRDfwi":
							if (in_array("Fastway Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDmfi":
							if (in_array("Mainfreight Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDnli":
							if (in_array("Northline Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDtntri":
							if (in_array("TNT Road Express Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDtnt9i":
							if (in_array("TNT Overnight Express by 9:00am Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDtnt10i":
							if (in_array("TNT Overnight Express by 10:00am Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDtnt12i":
							if (in_array("TNT Overnight Express by 12:00pm Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDtnt5i":
							if (in_array("TNT Overnight Express by 5:00pm Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						case "TRDtntIntEEi":
							if (in_array("TNT International Express Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDtntIntDEi":
							if (in_array("TNT International Document Express Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDtntIntEcoi":
							if (in_array("TNT International Economy Express Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						case "TRDtollIntPi":
							if (in_array("Toll International Priority Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "TRDtollIntDi":
							if (in_array("Toll International Priority Document Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_TRD_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;

						//  EGO //
						case "EGO":
							if (in_array("e-go.com.au", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_EGO_HANDLING;
							}
							break;
						
						case "EGOi":
							if (in_array("Insured e-go.com.au", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_EGO_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						//////////////  Hunter Express ///////////////////////////////////
						case "HXAF":
							if (in_array("HX Air Express", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_HX_HANDLING;
							}
							break;
						
						case "HXRF":
							if (in_array("HX Road Express", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_HX_HANDLING;
							}
							break;
						
						case "HXHDP":
							if (in_array("HX Home Direct Plus", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_HX_HANDLING;
							}
							break;

						// Startrack //
						case "STAexp":
							if (in_array("StarTrack Road Express", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_STA_HANDLING;
							}
							break;
						
						case "STAprm":
							if (in_array("StarTrack Air Express", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_STA_HANDLING;
							}
							break;
						
						
						case "STAprmi":
							if (in_array("StarTrack Air Express Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_STA_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "STAexpi":
							if (in_array("StarTrack Road Express Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_STA_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;

						// (Satchels) //
						case "STA1k":
						case "STA3k":
						case "STA5k":
							if (!($displayed & 1024)) {   //  only one per group
								if ((in_array(
										"StarTrack 1kg Satchel",
										$this->allowed_methods
									)) && ($quote->id == "STA1k")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_STAS_HANDLING;
								} else {
									if ((in_array(
											"StarTrack 3kg Satchel",
											$this->allowed_methods
										)) && ($quote->id == "STA3k")) {
										$handlingFee = MODULE_SHIPPING_OZPOST_STAS_HANDLING;
									} else {
										if ((in_array(
												"StarTrack 5kg Satchel",
												$this->allowed_methods
											)) && ($quote->id == "STA5k")) {
											$handlingFee = MODULE_SHIPPING_OZPOST_STAS_HANDLING;
										}
									}
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 1024;
								}
							}
							break;
						
						case 'SDL';
							if (in_array("Sendle", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SDL_HANDLING;
							}
							break;
						
						//  Couriers Please  V3
						case "CPLlab":
						case "CPLdps":
						case "CPLdpa":
						case "CPLons":
						case "CPLona":
						case "CPLsds":
						case "CPLsda":
						case "CPLdss":
						case "CPLdsa":
							if (((in_array("EZY Send", $this->allowed_methods)) && ($quote->id == "CPLlab")) ||
								((in_array(
										"Same day - Authority to leave",
										$this->allowed_methods
									)) && ($quote->id == "CPLdpa")) ||
								((in_array(
										"Same day - signature required",
										$this->allowed_methods
									)) && ($quote->id == "CPLdps")) ||
								((in_array(
										"Overnight - Authority to leave",
										$this->allowed_methods
									)) && ($quote->id == "CPLona")) ||
								((in_array(
										"Overnight - signature required",
										$this->allowed_methods
									)) && ($quote->id == "CPLons")) ||
								((in_array(
										"Domestic Priority - Authority to leave",
										$this->allowed_methods
									)) && ($quote->id == "CPLsda")) ||
								((in_array(
										"Domestic Priority - signature required",
										$this->allowed_methods
									)) && ($quote->id == "CPLsds")) ||
								((in_array(
										"Domestic saver- Authority to leave",
										$this->allowed_methods
									)) && ($quote->id == "CPLdsa")) ||
								((in_array(
										"Domestic saver - signature required",
										$this->allowed_methods
									)) && ($quote->id == "CPLdss"))) {
								$handlingFee = MODULE_SHIPPING_OZPOST_CPL_HANDLING;
							}
							break;
						
						// Courier please satchels  //
						case "CPL5g":
						case "CPL1":
						case "CPL3":
						case "CPL5":
							if (!($displayed & 2048)) {   //  only one per group
								if ((in_array("CP 500g Satchel", $this->allowed_methods)) && ($quote->id == "CPL1")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_CPLS_HANDLING;
								} else {
									if ((in_array(
											"CP 1kg Satchel",
											$this->allowed_methods
										)) && ($quote->id == "CPL1")) {
										$handlingFee = MODULE_SHIPPING_OZPOST_CPLS_HANDLING;
									} else {
										if ((in_array(
												"CP 3kg Satchel",
												$this->allowed_methods
											)) && ($quote->id == "CPL3")) {
											$handlingFee = MODULE_SHIPPING_OZPOST_CPLS_HANDLING;
										} else {
											if ((in_array(
													"CP 5kg Satchel",
													$this->allowed_methods
												)) && ($quote->id == "CPL5")) {
												$handlingFee = MODULE_SHIPPING_OZPOST_CPLS_HANDLING;
											}
										}
									}
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 2048;
								}
							}
							break;
						
						//  Couriers Please International parcels //
						case "CPLexp":
						case "CPLsav":
							if (((in_array(
										"International Express",
										$this->allowed_methods
									)) && ($quote->id == "CPLexp")) ||
								((in_array(
										"International Saver",
										$this->allowed_methods
									)) && ($quote->id == "CPLsav"))) {
								$handlingFee = MODULE_SHIPPING_OZPOST_CPLI_HANDLING;
							}
							break;


						// Aust Post Overseas //
						case "IPLEs";
						case "IPLEm";
						case "IPLEl";
							if (in_array("Overseas Economy", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING;
							}
							break;
						
						case "IPLEsi";
						case "IPLEmi";
						case "IPLEli";
							if (in_array("Overseas Economy Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						case "IPLEppes";
						case "IPLEppel";
							if (in_array("Overseas Economy Prepaid", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "IPLEppesi";
						case "IPLEppeli";
							if (in_array("Overseas Economy Prepaid Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						case "IPLRppes";  // case "REGLS"
						case "IPLRppel"; // case "REGLL"
							if (in_array("Overseas Registered Prepaid Envelope", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						//case "EXLSo";
						//    case "EXLLo";
						case "IPLX";
							if (in_array("Overseas Express Letter (inc sig)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING;
							}
							break;
						
						case "IPLXi";
							if (in_array("Overseas Express Letter Insured (inc sig)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						case "IPLXppe";
							if (in_array("Overseas Prepaid Express Letter (inc sig)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING;
							}
							break;
						
						case "IPLXppei";
							if (in_array("Overseas Prepaid Express Letter Insured (inc sig)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						// courier letter
						
						case "IPLC";
							if (in_array("Overseas Courier Letter", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING;
							}
							break;
						
						case "IPLCi";
							if (in_array("Overseas Courier Letter Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						case "IPLCpps";
							if (in_array("Overseas Courier Prepaid Satchel", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING;
							}
							break;
						
						case "IPLCppsi";
							if (in_array("Overseas Courier Prepaid Satchel Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_LET_HANDLING + MODULE_SHIPPING_OZPOST_EXP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						// economy  parcels
						case "IPec";
							;
							if (in_array("Economy Air", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
							}
							break;
						
						case "IPecs";
							if (in_array("Economy Air +sig", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "IPeci";
							if (in_array("Economy Air Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "IPecsi";
							if (in_array("Economy Air Insured +sig", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						case "SEA";
							if (in_array("Sea", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
							}
							break;
						
						case "SEAi";
							if (in_array("Insured Sea", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						
						////////////////////////////////////////////////
						// Standard
						case "IPS";
							;
							if (in_array("Standard Air", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
							}
							break;
						
						case "IPSs";
							if (in_array("Standard Air +sig", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "IPSi";
							if (in_array("Standard Air Insured", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "IPSsi";
							if (in_array("Standard Air Insured +sig", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						// Prepaid - only one per group
						// Standard no extras
						case "IPS500g";
						case "IPS1k";
						case "IPS2k";
						case "IPS5k";
							if (!($displayed & 8)) {   //  only one per group
								if ((in_array(
										"Standard Air 500g Satchel",
										$this->allowed_methods
									)) && ($quote->id == "IPS500g")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
								} elseif ((in_array(
										"Standard Air 1kg Satchel",
										$this->allowed_methods
									)) && ($quote->id == "IPS1k")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
								} elseif ((in_array(
										"Standard Air 2kg Satchel",
										$this->allowed_methods
									)) && ($quote->id == "IPS2k")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
								} elseif ((in_array(
										"Standard Air 5kg Box",
										$this->allowed_methods
									)) && ($quote->id == "IPS5k")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 8;
								}
							}
							break;
						
						
						// Standard +sig
						case "IPS500gs";
						case "IPS1ks";
						case "IPS2ks";
						case "IPS5ks";
							if (!($displayed & 16)) {   //  only one per group
								if ((in_array(
										"Standard Air 500g Satchel +sig",
										$this->allowed_methods
									)) && ($quote->id == "IPS500gs")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								} elseif ((in_array(
										"Standard Air 1kg Satchel +sig",
										$this->allowed_methods
									)) && ($quote->id == "IPS1ks")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								} elseif ((in_array(
										"Standard Air 2kg Satchel +sig",
										$this->allowed_methods
									)) && ($quote->id == "IPS2ks")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								} elseif ((in_array(
										"Standard Air 5kg Box +sig",
										$this->allowed_methods
									)) && ($quote->id == "IPS5ks")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 16;
								}
							}
							break;
						
						//Standard insured
						case "IPS500gi";
						case "IPS1ki";
						case "IPS2ki";
						case "IPS5ki";
							if (!($displayed & 2097152)) {   //  only one per group
								if ((in_array(
										"Standard Air 500g Satchel Insured",
										$this->allowed_methods
									)) && ($quote->id == "IPS500gi")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								} elseif ((in_array(
										"Standard Air 1kg Satchel Insured",
										$this->allowed_methods
									)) && ($quote->id == "IPS1ki")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								} elseif ((in_array(
										"Standard Air 2kg Satchel Insured",
										$this->allowed_methods
									)) && ($quote->id == "IPS2ki")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								} elseif ((in_array(
										"Standard Air 5kg Box Insured",
										$this->allowed_methods
									)) && ($quote->id == "IPS5ki")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 2097152;
								}
							}
							break;
						
						// Standard insured + sig
						case "IPS500gsi";
						case "IPS1ksi";
						case "IPS2ksi";
						case "IPS5ksi";
							if (!($displayed & 4096)) {   //  only one per group
								if ((in_array(
										"Standard Air 500g Satchel Insured +sig",
										$this->allowed_methods
									)) && ($quote->id == "IPS500gsi")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								} elseif ((in_array(
										"Standard Air 1kg Satchel Insured +sig",
										$this->allowed_methods
									)) && ($quote->id == "IPS1ksi")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								} elseif ((in_array(
										"Standard Air 2kg Satchel Insured +sig",
										$this->allowed_methods
									)) && ($quote->id == "IPS2ksi")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								} elseif ((in_array(
										"Standard Air 5kg Box Insured +sig",
										$this->allowed_methods
									)) && ($quote->id == "IPS5ksi")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 4096;
								}
							}
							break;
						
						////////////////////////////////
						// Express
						case "IPEs";
							if (in_array("Express Air (inc sig)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
							}
							break;
						
						case "IPEsi";
							if (in_array("Express Air Insured (inc sig)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						// Prepaid - only one per group
						// Express no extras
						case "IPE500g";
						case "IPE1k";
						case "IPE2k";
						case "IPE5k";
							if (!($displayed & 16777216)) {   //  only one per group
								if ((in_array(
										"Express Air 500g Satchel",
										$this->allowed_methods
									)) && ($quote->id == "IPE500g")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
								} elseif ((in_array(
										"Express Air 1kg Satchel",
										$this->allowed_methods
									)) && ($quote->id == "IPE1k")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
								} elseif ((in_array(
										"Express Air 2kg Satchel",
										$this->allowed_methods
									)) && ($quote->id == "IPE2k")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
								} elseif ((in_array(
										"Express Air 5kg Box",
										$this->allowed_methods
									)) && ($quote->id == "IPE5k")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 16777216;
								}
							}
							break;
						
						//Express  insured
						case "IPE500gi";
						case "IPE1ki";
						case "IPE2ki";
						case "IPE5ki";
							if (!($displayed & 33554432)) {   //  only one per group
								if ((in_array(
										"Express Air 500g Satchel Insured",
										$this->allowed_methods
									)) && ($quote->id == "IPE500gi")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								} elseif ((in_array(
										"Express Air 1kg Satchel Insured",
										$this->allowed_methods
									)) && ($quote->id == "IPE1ki")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								} elseif ((in_array(
										"Express Air 2kg Satchel Insured",
										$this->allowed_methods
									)) && ($quote->id == "IPE2ki")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								} elseif ((in_array(
										"Express Air 5kg Box Insured",
										$this->allowed_methods
									)) && ($quote->id == "IPE5ki")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 33554432;
								}
							}
							break;

						// Courier
						case "IPC";
							if (in_array("Courier Air (inc sig)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
							}
							break;
						
						case "IPCi";
							if (in_array("Courier Air Insured (inc sig)", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						// Prepaid - only one per group
						// Express no extras
						case "IPC500g";
						case "IPC1k";
							if (!($displayed & 67108864)) {   //  only one per group
								if ((in_array(
										"Courier Air 500g Satchel",
										$this->allowed_methods
									)) && ($quote->id == "IPC500g")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
								} elseif ((in_array(
										"Courier Air 1kg Satchel",
										$this->allowed_methods
									)) && ($quote->id == "IPC1k")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING;
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 67108864;
								}
							}
							break;
						
						// courier insured
						case "IPC500gi";
						case "IPC1ki";
							if (!($displayed & 134217728)) {   //  only one per group
								if ((in_array(
										"Courier Air 500g Satchel Insured",
										$this->allowed_methods
									)) && ($quote->id == "IPC500gi")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								} elseif ((in_array(
										"Courier Air 1kg Satchel Insured",
										$this->allowed_methods
									)) && ($quote->id == "IPC1ki")) {
									$handlingFee = MODULE_SHIPPING_OZPOST_INT_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
								}
								if (isset($handlingFee)) {
									$displayed = $displayed | 134217728;
								}
							}
							break;


						//Skippy Post-- Thanks to Adrian Frankel <adrian@precisium.com.au>
						case "SKP";
							if (in_array("Skippy Post Air", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SKP_HANDLING;
							}
							break;
						
						case "SKPt";
							if (in_array("Skippy Post Air with Tracking", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SKP_HANDLING;
							}
							break;
						
						case "SKPti";
							if (in_array("Skippy Post Air with Tracking and Insurance", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SKP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
						
						case "SKPp";
							if (in_array("Skippy Post Air +Proof of postage", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SKP_HANDLING;
							}
							break;
						
						case "SKPtp";
							if (in_array("Skippy Post Air with Tracking +Proof of postage", $this->allowed_methods)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SKP_HANDLING;
							}
							break;
						
						case "SKPtip";
							if (in_array(
								"Skippy Post Air with Tracking and Insurance +Proof of postage",
								$this->allowed_methods
							)) {
								$handlingFee = MODULE_SHIPPING_OZPOST_SKP_HANDLING + MODULE_SHIPPING_OZPOST_RI_HANDLING;
							}
							break;
					}
					
					/////////////////////////////
					if (isset($handlingFee) && ($quote->cost > 0)) {  // valid quote
						
						$hwsurcharge = ($parcelWeight >= MODULE_SHIPPING_OZPOST_HW_LIMIT * 1000) ? MODULE_SHIPPING_OZPOST_HW_SURCHARGE : 0; //  Heavy parcel surcharge //
						
						if ($NoPack === 1) {
							$handlingFee = $handlingFee * $parcelQty;
							$hwsurcharge = $hwsurcharge * $parcelQty;
						} // multiply handling by #of packages (
						
						$cost = (float)($quote->cost) + $handlingFee + $hwsurcharge;
						
						if ($cost < 0) {
							$handlingFee = 0 - (float)($quote->cost);
							$cost = 0;
						}
						
						switch (substr($quote->id, 0, 2)) {
							case "TN":
								$logo = 'ozpost_tnt_logo.';
								break;
							case "EG":
								$logo = 'ozpost_ego_logo.';
								break;
							case "FW":
								$logo = 'ozpost_fastway_logo.';
								break;
							case "TR":
								$logo = 'ozpost_trd_logo.';
								break;
							case "ST":
								$logo = 'ozpost_sta_logo.';
								break;
							case "CP":
								$logo = 'ozpost_cpl_logo.';
								break;
							case "SM":
								$logo = 'ozpost_smart_logo.';
								break;
							case "SK":
								$logo = 'ozpost_skippy_logo.';
								break;
							case "SD":
								$logo = 'ozpost_sendle_logo.';
								break;
							case "HX":
								$logo = 'ozpost_hunterexpress_logo.';
								break;
							default:
								$logo = 'ozpost_austpost_logo.';
						}
						$txtCarrier = (string)$quote->carrier;
						
						if (defined($quote->id . '_description')) {
							$quote->description = constant($quote->id . '_description');
						}
						
						$description = $quote->description;
						$carrier = $txtCarrier . ",";
						
						if (($dest_country == "AU") && (($this->tax_class) > 0)) {
							$t = $cost - ($cost / (zen_get_tax_rate(
											$this->tax_class,
											$order->delivery['country']['id'],
											$order->delivery['zone_id']
										) + 1));
							if ($t > 0) {
								$cost = $t;
							}
						}
						
						if ($method == "") {
							if (MODULE_SHIPPING_OZPOST_ICONSS != "None") {
								$logoname = $logo;
								$logo = $ipath . $logo . MODULE_SHIPPING_OZPOST_ICONS;
								
								if (!is_file($logo)) {
									$result = $this->_get_from_ozpostnet(
										"/updates/icons/" . $logoname . MODULE_SHIPPING_OZPOST_ICONS
									);
									if ($result) {
										if (!strstr($result, "<error>")) {
											file_put_contents(DIR_FS_CATALOG . $logo, $result);
										}
									}
								}
								if (is_file($logo)) {
									$carrier = zen_image($logo, $txtCarrier);
								}
								
								if (MODULE_SHIPPING_OZPOST_ICONSS == "Carriers+Methods") {
									if (!is_file(
										$ipath . "ozpost_" . strtolower($quote->id) . "." . MODULE_SHIPPING_OZPOST_ICONS
									)) {
										$result = $this->_get_from_ozpostnet(
											"/updates/icons/ozpost_" . strtolower(
												$quote->id
											) . "." . MODULE_SHIPPING_OZPOST_ICONS
										);
										
										if ($result) {
											if (!strstr($result, "<error>")) {
												file_put_contents(
													$ipath . "ozpost_" . strtolower(
														$quote->id
													) . "." . MODULE_SHIPPING_OZPOST_ICONS,
													$result
												);
											}
										}
									}
									
									if (is_file(
										$ipath . "ozpost_" . strtolower($quote->id) . "." . MODULE_SHIPPING_OZPOST_ICONS
									)) {  // only if icon exists
										$description = zen_image(
											$ipath . "ozpost_" . strtolower(
												$quote->id
											) . "." . MODULE_SHIPPING_OZPOST_ICONS,
											$quote->description
										);
									}
								}
							} // no icons //
							//////////////////////////////////////////////
							
							$estimateddays = null;
							if (MODULE_SHIPPING_OZPOST_EF != "None") {
								$estimateddays = $quote->days;
								if (MODULE_SHIPPING_OZPOST_EF == "Days") {
									$estimateddays .= MODULE_SHIPPING_OZPOST_EST_DEL_TEXT;
								}
							}
							
							$carrier = "<div style='display:inline;'>" . $carrier . " " . $description . "</div>";
							
							if (($estimateddays) && ($estimateddays != "n/a")) {
								$carrier .= "<div style='display:inline;vertical-align:top;float:right;'><ul><li>" . $estimateddays . "&nbsp;&nbsp;&nbsp;</li>";
							}
							
							$details = null;
							
							if (MODULE_SHIPPING_OZPOST_HIDE_HANDLING != 'Yes') {
								if (!(($estimateddays) && ($estimateddays != "n/a"))) {
									$details = "<div style='display:inline;vertical-align:top;float:right;'><ul>";
								}
								$details = ((float)$handlingFee > 0) ? $details .= "<li>" . MODULE_SHIPPING_OZPOST_HANDLING1_TEXT . $currencies->format(
										(float)$handlingFee / $aus_rate
									) . MODULE_SHIPPING_OZPOST_HANDLING2_TEXT . "</li>" : $details;
								$details = ((float)$quote->insuranceIncl > 0) ? $details .= "<li>" . MODULE_SHIPPING_OZPOST_HANDLING1_TEXT . $currencies->format(
										(float)$quote->insuranceIncl / $aus_rate
									) . " Insurance</li>" : $details;
								$details = ((float)$quote->otherfeeIncl > 0) ? $details .= "<li>" . MODULE_SHIPPING_OZPOST_HANDLING1_TEXT . $currencies->format(
										(float)$quote->otherfeeIncl / $aus_rate
									) . " " . $quote->otherfeeName . " fee</li>" : $details;
								$details = ((float)$hwsurcharge > 0) ? $details .= "<li>" . MODULE_SHIPPING_OZPOST_HANDLING1_TEXT . $currencies->format(
										(float)$hwsurcharge / $aus_rate
									) . "  heavy parcel surcharge</li>" : $details;
							}
							$details .= "</ul></div>";
						}  // end $method wasnt set
						
						$methods[] = array(
							'id' => "$quote->id",
							'title' => "$txtCarrier " . $quote->description,
							'cost' => ($cost / $aus_rate),
							'Display' => $carrier . $details
						);
					}   // end  if  if ((isset($handlingFee)) && ($quote->cost > 0))
				}  // end foreach loop
			} else {//    echo "Bad XML\n";
				$errmsg = "XML Data Error : ";
				foreach (libxml_get_errors() as $error) {
					$errmsg .= $error->message;
				}
				if (MODULE_SHIPPING_OZPOST_MSG == "Yes") {
					echo "<div class=\"messageStackError\">" . $errmsg . "</div>";
				}
			}// XML Data Error
		} else { //  SERVER CONNECTION ERROR
			if (MODULE_SHIPPING_OZPOST_MSG == "Yes") {
				echo "<div class=\"messageStackError\">" . MODULE_SHIPPING_OZPOST_ERROR_TEXT2 . "</br>" . $result . "</div>";
			}
		} //  SERVER CONNECTION ERROR END

		///////////////////////////////////////////////////////////////////////
		//  check to ensure we have at least one valid quote - produce fixed rates if not.
		if  (isset($methods) && sizeof($methods) == 0 ) {
			switch (MODULE_SHIPPING_OZPOST_COST_ON_ERROR_TYPE) {
				//        case "Static Rates":
				//           $cost = $this->_get_error_cost($dest_country) ;
				//            if ($cost[0] == 0)  { $this->enabled = false; return ; }    //  All has failed - Disable module
				//			$methods[] = array( 'id' => " ",  'title' => "(based on ". $cost[1].")", 'cost' => $cost[0], 'Display' => '' ) ;
				//     break;
				
				case "Table Rates":
					$cost = $this->_get_error_cost_tables($dest_country);
					if ($cost[0] == 0) {
						$this->enabled = false;
						return;
					}    //  All has failed - Disable module
					$methods[] = array(
						'id' => " ",
						'title' => "(based on " . $cost[1] . ")",
						'cost' => $cost[0],
						'Display' => ''
					);
					break;
				
				case "TBA":
					$methods[] = array(
						'id' => " ",
						'title' => MODULE_SHIPPING_OZPOST_ERROR_TEXT3,
						'cost' => 0,
						'Display' => ''
					);
					break;
				
				case "Do Nothing":
					$this->enabled = false;
					return;
					break;
				
				default:  //"Static Rates":
					$cost = $this->_get_error_cost($dest_country);
					if ($cost[0] == 0) {
						$this->enabled = false;
						return;
					}    //  All has failed - Disable module X
					$methods[] = array('id' => " ", 'title' => " $" . $cost[1], 'cost' => $cost[0], 'Display' => '');
					break;
			}
		}
		
		//  Sort by cost //
		$sarray = array();
		$resultarr = array();
		
		foreach ($methods as $key => $value) {
			$sarray[$key] = $value['cost'];
		}
		asort($sarray);
		foreach ($sarray as $key => $value) {
			$resultarr[$key] = $methods[$key];
		}
		
		$this->quotes['methods'] = array_values($resultarr);   // set it
		
		if ($this->tax_class > 0) {
			$this->quotes['tax'] = zen_get_tax_rate(
				$this->tax_class,
				$order->delivery['country']['id'],
				$order->delivery['zone_id']
			);
		}
		
		$_SESSION['ozpostQuotes'] = $this->quotes; // save as session to avoid reprocessing when single method required
		// $_SESSION['ozpostOrigState'] = (string)$xmlQuotes->information[0]->origstate ; Was used for CSV
		
		return $this->quotes;   //  all done //
	} //  end function quote($method = '')
	
	/**
	 * @param $dest_country
	 * @return array
	 */
	private function _get_error_cost($dest_country)
	{
		global $parcelWeight, $parcelQty, $order;
		
		$x = explode(',', MODULE_SHIPPING_OZPOST_COST_ON_ERROR);
		unset($_SESSION['ozpostQuotes']);
		
		$rate = ($dest_country == "AU") ? $x[0] : $x[1];
		$cost = $rate;
		if (($dest_country == "AU") && (($this->tax_class) > 0)) {
			$t = $cost - ($cost / (zen_get_tax_rate(
							$this->tax_class,
							$order->delivery['country']['id'],
							$order->delivery['zone_id']
						) + 1));
			if ($t > 0) {
				$cost = $t;
			}
		}
		
		if (MODULE_SHIPPING_OZPOST_STATIC_MODE == "Cost per kg") {
			$cost = $cost * intval(($parcelWeight / 1000) + 1);
		}
		if (MODULE_SHIPPING_OZPOST_STATIC_MODE == "Cost per Item") {
			$cost = $cost * $parcelQty;
		}
		$this->quotes = array('id' => $this->code, 'module' => MODULE_SHIPPING_OZPOST_STATIC_MODE);
		return array($cost, $rate);
	}
	
	/**
	 * @param $dest_country
	 * @return array
	 */
	private function _get_error_cost_tables($dest_country)
	{ // by weight only
		global $parcelWeight, $order, $orderValue, $parcelQty;
		unset($_SESSION['ozpostQuotes']);
		
		$x = explode(' ', MODULE_SHIPPING_OZPOST_COST_ON_ERROR2);
		
		switch (MODULE_SHIPPING_OZPOST_TABLE_MODE) {
			case ('price'):
				$value = $orderValue;
				break;
			case ('weight'):
				$value = $parcelWeight; //gms
				if (MODULE_SHIPPING_OZPOST_WEIGHT_FORMAT == "kilos") {
					$value = $value * 1000;
				} // convert to gms
				break;
			case ('item'):
				$value = $parcelQty;
				break;
		}
		
		$rates = ($dest_country == "AU") ? $x[0] : $x[1];
		$table_cost = preg_split("/[:,]/", $rates);
		$size = sizeof($table_cost);
		for ($i = 0, $n = $size; $i < $n; $i += 2) {
			if (round($value, 9) <= $table_cost[$i]) {
				if (strstr($table_cost[$i + 1], '%')) {
					$cost = ($table_cost[$i + 1] / 100) * $value;
				} else {
					$cost = $table_cost[$i + 1];
				}
				break;
			}
		}
		
		if (($dest_country == "AU") && (($this->tax_class) > 0)) {
			$t = $cost - ($cost / (zen_get_tax_rate(
							$this->tax_class,
							$order->delivery['country']['id'],
							$order->delivery['zone_id']
						) + 1));
			if ($t > 0) {
				$cost = $t;
			}
		}
		$this->quotes = array('id' => $this->code, 'module' => MODULE_SHIPPING_OZPOST_TABLE_MODE);
		
		if (MODULE_SHIPPING_OZPOST_TABLE_MODE === "weight") {
			return array($cost, "Weight of " . $value . "gms");
		} else {
			if (MODULE_SHIPPING_OZPOST_TABLE_MODE === "price") {
				return array($cost, "Total Price");
			} else {
				return array($cost, "Number of Items (" . $value . ")");
			}
		}
	}
	
	/**
	 * @param $folder
	 * @return false|string|null
	 */
	private function _changePerms($folder)
	{
		global $oldperms;
		$oldperms = null;
		if (is_dir($folder)) {
			$oldperms = substr(sprintf('%o', fileperms($folder)), -4);
			if ("$oldperms" < "0775") {
				if (!chmod($folder, 0775)) {
					$oldperms = null;
				}
			}
		}
		return $oldperms;
	}
	
	/**
	 * @param $folder
	 */
	private function _restorePerms($folder)
	{
		global $oldperms;
		if (isset($oldperms) && ("$oldperms" < "0775")) {
			chmod($folder, octdec($oldperms));
		}
	}
	
	/**
	 * @param $folder
	 * @return string
	 */
	private function _get_cache_folder($folder)
	{
		$cachefolder = DIR_FS_SQL_CACHE . "/ozpost/";
		if ($folder == DIR_FS_ADMIN) {
			//-bof-20140801-lat9-Drive letter fix
			if (preg_match('/^[A-Z]:/i', $folder)) {
				$folder = substr($folder, 3);
			}
			//-eof-20140801-lat9
			
			$elements = explode("/", $folder);
			$cachefolder .= $elements[sizeof($elements) - 2] . "/";
		}
		return $cachefolder;
	}
	
	/**
	 * @param $folder
	 * @param $subfolder
	 * @param $filename
	 * @param $data
	 * @return bool
	 */
	private function _writeTempFile($folder, $subfolder, $filename, $data)
	{
		$err = false;
		
		$oldperm = $this->_changePerms(DIR_FS_SQL_CACHE);
		$cachefolder = $this->_get_cache_folder($folder);

		//-bof-20140801-lat9-Fix for drive letter
		if (preg_match('/^[A-Z]:/i', $subfolder)) {
			$subfolder = substr($subfolder, 3);
		}
		$myFolder = $cachefolder . $subfolder;
		//-eof-20140801-lat9
		
		if (!is_dir($myFolder)) {
			if (!mkdir($myFolder, 0775, true)) {
				$err = true;
			}
		}
		
		if (!$err) { // no error, write file
			$file = $myFolder . $filename;
			if (!(file_put_contents($file, $data))) {
				$err = true;
			}
		}
		
		if (isset($oldperm) && ("$oldperm" < "0775")) {
			chmod(DIR_FS_SQL_CACHE, octdec($oldperm));
		}
		
		return $err;
	}
	
	////Tare weight format: {max weight}:{tare %},{max weight}:{tare %} ...
	
	/**
	 * @param $testweight
	 * @return array
	 */
	private function _tareWeight($testweight)
	{
		//$testweight = 2501 ;
		// $MODULE_SHIPPING_OZPOST_TARE = "50:+10, 1000:11, 2000:+500," ; // MODULE_SHIPPING_OZPOST_TARE ;
		// 500:+90,1000:20,2000:15,10000:+2000 // defaults
		$tareArray = explode(',', MODULE_SHIPPING_OZPOST_TARE);
		$tareOut = 0; //fallback no increase
		if (count($tareArray) == 1) {
			//test for only 1 tare weight % given
			$tareOut = $tareArray[0];
			// a single array may still contain two elements.
			$tare = explode(':', $tareOut);
			$tareOut = (sizeof($tare) == 1) ? $tare[0] : $tare[1];
		} else {
			for ($i = 0; count($tareArray) - 1; $i++) {
				$tare = explode(':', $tareArray[$i]);
				
				if (count($tare) == 1) {
					//must be fallback tare %
					$tareOut = $tare[0];
					break;
				}
				if ($testweight <= $tare[0]) {
					$tareOut = $tare[1];
					break;
				}
			}
		}
		
		if (!$tareOut) {  // if we don't have a valid tare then test weight is larger than defined weight, with no fallback, so we use the last array data
			$tare = explode(':', $tareArray[$i - 1]);
			$tareOut = $tare[1];
		}
		
		//  calc new parcelweight //
		if (substr(trim($tareOut), 0, 1) == "+") {
			$parcelWeight = $testweight + $tareOut; // absolute (grams)
		} else {
			$parcelWeight = $testweight + (($testweight * $tareOut) / 100); // percentage
			$tareOut .= "%";
		}

		//  echo "Enter with : "  . $testweight . "gm<br>";
		//  echo "Return with : " . $parcelWeight . "gm<br>" ;
		//  echo "Tare : " .     $tareOut ."<br>" ;
		//  print_r($tare) ;
		//  echo "<br>Array Data :" . $MODULE_SHIPPING_OZPOST_TARE ; die ;
		
		return array($parcelWeight, $tareOut);
	}

	////////////////////////////////////////////////////////////////
	
	/**
	 * @return mixed
	 */
	public function check()
	{
		global $db, $dbDiff, $updavail;
		$dbDiff = 0;
		$updavail = null;
		if (!isset($this->_check)) {
			$check_query = $db->Execute(
				"select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_OZPOST_STATUS'"
			);
			$this->_check = $check_query->RecordCount();
			
			// version check / network check //
			if (MODULE_SHIPPING_OZPOST_STATUS == "True") {
				$error = $this->_checkInc(0);
				if ($error) {
					echo $error;
				}
				//  JavaScript //
				echo "<script type=\"text/javascript\">
							function updateModule(val, text) {
							   if (window.confirm(text)) { window.location = 'modules.php?set=shipping&module=ozpost&action=edit&update=1';
								} else { document.getElementById('updater').value = val+\" (postponed)\" ; }
							}
						</script> ";
				
				$latest_version = (intval(
					isset($_SESSION['versCheck'])
				)) ? $_SESSION['versCheck'] : $this->_get_from_ozpostnet(
					"/quotefor.php?flags=get_latest_client_version"
				);
				$_SESSION['versCheck'] = $latest_version;
				if ((substr($latest_version, 0, 1) == "V") && ($latest_version > "V$this->VERSION")) {
					$updavail = substr($latest_version, 1);
					$messg = "OzPost V$updavail update available";
				} else {
					if ($this->VERSION != MODULE_SHIPPING_OZPOST_DB_VERS) {
						$updavail = $this->VERSION;
						$dbDiff = 1;
						$this->_update($updavail);
						$updavail = null;
						//   $messg = "Database and File versions differ (V" . MODULE_SHIPPING_OZPOST_DB_VERS . " / V" . $this->VERSION . ")";$this->_update($updavail) ;
					}
				}
				if ($updavail) {
					echo "<input type=\"button\" name=\"updater\" value=\"$messg\" id=\"updater\" onClick=\"updateModule('$messg', 'Upgrade now?') \" />";
				} // 'action' undefined array key BMH
                //echo '<br> line3579 ozpost.php'; // BMH DEBUG
                //var_dump($_REQUEST);
				//if (($_REQUEST['action'] == "edit") && ($_REQUEST['update'] == "1") && ($_REQUEST['module'] == "ozpost")) { // BMH 
				if ((($_REQUEST['action']  ?? ' ') == "edit") && (($_REQUEST['update'] ?? ' ') == "1") && ($_REQUEST['module'] == "ozpost")) {
					$this->_update($updavail);
				}
				if ((($_REQUEST['action'] ?? ' ') == "test") && ($_REQUEST['module'] == "ozpost")) { // BMH
					$this->_servertest();
				}
				// if( $_REQUEST['action'] == "edit") {$this->_createCachetable();}
				
				if (SHIPPING_ORIGIN_ZIP != "") {
					if (MODULE_SHIPPING_OZPOST_ORIGIN_ZIP == "") {
						$db->Execute(
							"update " . TABLE_CONFIGURATION . " set configuration_value = \"" . SHIPPING_ORIGIN_ZIP . "\" where configuration_key = \"MODULE_SHIPPING_OZPOST_ORIGIN_ZIP\""
						);
					}
					
					$days = (intval(
						isset($_SESSION['lastCheck'])
					)) ? $_SESSION['lastCheck'] : $this->_get_from_ozpostnet(
						"/quotefor.php?flags=expires&host=$this->HOST&storecode=" . SHIPPING_ORIGIN_ZIP
					);
				}
				$text1 = "Your Ozpost subscription";
				$text2 = "</strong><a href=https://www.ozpost.net/my-account/>(click to renew)</a>";
				if ($days < 1000) {
					if ($days > 0) {
						$_SESSION['lastCheck'] = $days;
						echo "$text1 expires in <strong>$days days $text2.";
					}
					if ($days === 0) {
						echo "$text1 expires <strong>TODAY $text2.";
					}
					if ($days < 0) {
						echo "$text1 expired <strong>" . $days * -1 . " days ago $text2";
					}
				}
				$db->Execute(
					"update " . TABLE_CONFIGURATION . " set configuration_value = \"" . $days . "\" where configuration_key = \"MODULE_SHIPPING_OZPOST_EXPIRES\""
				);
				
				if (MODULE_SHIPPING_OZPOST_EMAIL === "") {
					$db->Execute(
						"update " . TABLE_CONFIGURATION . " set configuration_value = \" " . STORE_OWNER_EMAIL_ADDRESS . "\" where configuration_key = \"MODULE_SHIPPING_OZPOST_EMAIL\""
					);
				}
				
				if (MODULE_SHIPPING_OZPOST_TYPE_LETTERS == "--none--") {   // No letters, then don't hide parcels //
					$res = $db->Execute(
						"update " . TABLE_CONFIGURATION . " set configuration_value = \"No\" where configuration_key = \"MODULE_SHIPPING_OZPOST_HIDE_PARCELD\""
					);
					$res = $db->Execute(
						"update " . TABLE_CONFIGURATION . " set configuration_value = \"No\" where configuration_key = \"MODULE_SHIPPING_OZPOST_HIDE_PARCELO\""
					);
				}
				if (MODULE_SHIPPING_OZPOST_TYPE_APS == "--none--") {
					$res = $db->Execute(
						"update " . TABLE_CONFIGURATION . " set configuration_value = \"No</br>\" where configuration_key = \"MODULE_SHIPPING_OZPOST_HIDE_PARCEL2\""
					);
				}
				// No satchels, then don't hide parcels //
			}  // end MODULE_SHIPPING_OZPOST_STATUS == "True"
		} //  end !isset($this->_check
		return $this->_check;
	}
	
	/**
	 * @param $updavail
	 *
	 */
	private function _update($updavail)
	{
		global $messageStack, $auto, $db, $dbDiff;
		
		if ($updavail != $this->VERSION) {
			// $db->Execute('delete FROM `' . TABLE_OZPOST_CACHE . '` WHERE 1')  ;
			$vers = preg_replace("/[^0-9]/", "", $updavail);    //  numbers only
			if ($vers > 0) {
				$messageStack->add_session('Contacting server......', 'caution');
				$filename = "ozpost_zen." . $vers;  // the update filename
				$result = $this->_get_from_ozpostnet("/updates/" . $filename);// get it
				if (($result != "") && ((substr($result, 0, 7)) != "<error>")) {
					$folder = DIR_FS_CATALOG_MODULES . "shipping/";
					$this->_changePerms($folder);
					
					$myFile = $folder . $filename;
					if (file_put_contents($myFile, $result)) { //   save and check
						if ((strpos($result, "// Zencart"))) {   // check for valid data in file
							//  make a backup of the original file
							$verso = preg_replace("/[^0-9]/", "", $this->VERSION);    //  numbers only
							if (rename(ME, $folder . "ozpost" . $verso)) {
								//  copy new file to old
								if (rename($myFile, ME)) {
									$msg = 'The ozpost shipping module has been updated. Please check your settings.';
									$messageStack->add_session($msg, 'caution');
									echo "<div>" . $msg . "</div>";
									$msg = 'Your previously installed module has been saved as ozpost' . $verso;
									$messageStack->add_session($msg, 'caution');
									echo "<div>" . $msg . "</div>";
									// updated OK - Display changes
									$f = file(ME);
									foreach ($f as $line) {
										if (strstr($line, "\$Id: ozpost.php,V" . $updavail)) {
											$messageStack->add_session($line, 'success');
											$line = next($f);
											while (!strstr($line, "*/")) {
												$messageStack->add_session($line, 'success');
												$line = next($f);
											}
											break;
										}
									}
									$dbDiff = 2;  //  $this->VERSION = $updavail;
								} else {
									$msg = 'There was an error renaming the Update file. Update Aborted.';
									$messageStack->add_session($msg, 'error');
									echo "<div>" . $msg . "</div>";
									rename($folder . "ozpost" . $verso, ME);
								} // restore the original
							} else {
								$msg = 'There was an error renaming the original file. Update Aborted.';
								$messageStack->add_session($msg, 'error');
								echo "<div>" . $msg . "</div>";
							}
						} else {
							$msg = 'There was an error in the file downloaded. Update Aborted.';
							$messageStack->add_session($msg, 'error');
							echo "<div>" . $msg . "</div>";
						}
					} else {
						$msg = 'There was an error saving the Update File. Update Aborted.';
						$messageStack->add_session($msg, 'error');
						echo "<div>" . $msg . "</div>";
					}
				} else {
					if ($result != "") {
						$msg = 'Server Error. ' . $result . ' Update Aborted';
						$messageStack->add_session($msg, 'error');
						echo "<div>" . $msg . "</div>";
					} else {
						$msg = 'No data returned from the server. Update Aborted';
						$messageStack->add_session($msg, 'error');
						echo "<div>" . $msg . "</div>";
					}
				}
				
				$this->_restorePerms($folder);
			}  // Not a valid version number //
		}
		
		if ($dbDiff > 0) {
			$auto = 1;
			$this->remove();
			$this->install();
			
			if ($dbDiff == 1) {
				$messageStack->add_session('Database Updated.', 'success');
			}
			if ($dbDiff == 2) {
				$messageStack->add_session('File Updated.', 'success');
			}
			
			unset($auto);
			unset($dbDiff);
			
			$messageStack->add_session('CHECK YOUR SETTINGS', 'caution');
			
			foreach ($_SESSION['messageToStack'] as $info) {
				$style = "<div style='color:black;'>";
				if ($info['type'] == 'error') {
					$style = "<div style='background-color: #FFB3B5;'>";
				}
				if ($info['type'] == 'caution') {
					$style = "<div style='background-color: #FFFF00;'>";
				}
				if ($info['type'] == 'success') {
					$style = "<div style='background-color: #D4FFBD;'>";
				}
				
				echo $style . $info['text'] . "</div>";
			}
		} // No update available
		
		unset($_REQUEST['update']);
		unset($_SESSION['messageToStack']);
	}

	/////////////////////////////////////////////////////////////////////
	
	/**
	 * @return int
	 */
	private function _preInstallTests()
	{
		global $messageStack, $Ozfiles, $ipath;
		if (isset($_SESSION['lastCheck'])) {
			unset($_SESSION['lastCheck']);
		}
		
		$template = $GLOBALS['template_dir'];
		$error = 0;
		$templateFlag = 0;
		$templateCount = 0;
		
		foreach ($Ozfiles as $fileArray) {
			$folder = $fileArray[0] . $fileArray[1];
			$fileName = ($fileArray[2]) ? $fileArray[2] : null;
			$file = ($fileName) ? $folder . $fileName : null;
			$f2 = ($file) ? $file . "_bak" : null;
			
			//  if(!((function_exists('zen_register_admin_page')) && ($fileName == "clicknsend_customers_dhtml.php")))  {
			
			if ($file) {
				$messageStack->add_session('Checking File : ' . $file, 'success');
			} else {
				$messageStack->add_session('Checking Folder: ' . $folder, 'success');
			}
			
			if ($folder == DIR_FS_CATALOG . $ipath) {    // We may need to create the ozpost images folder.
				if (!file_exists($folder)) {
					mkdir($folder, 0775, true);
				}
			}
			
			if ($folder == DIR_FS_CATALOG . DIR_WS_TEMPLATES . $template . "/templates/") {  // and the templates folder (/includes/templates/classic/templates/ doesn't exist by default
				if (!file_exists($folder)) {
					mkdir($folder, 0775, true);
				}
			}
			
			
			if (($folder === DIR_FS_CATALOG . DIR_WS_TEMPLATES . "template_default/templates/") || ($folder === DIR_FS_CATALOG . DIR_WS_TEMPLATES . $template . "/templates/")) {
				$templateFlag++;
				if (is_readable($file)) {
					$templateCount++;
				}
				if (($templateFlag == 2) && ($templateCount == 0)) {
					$messageStack->add_session(
						'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;File : ' . $file . ' <strong>is Unreadable. Template file unable to be updated - not fatal</strong> ',
						'success'
					);  //   can continue
				}
			} else {
				$this->_changePerms($folder);
				if (!(file_put_contents($folder . "/ozpost_test.txt", "Hello World"))) {
					$messageStack->add_session(
						'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Folder : ' . $folder . ' <strong>is Unwritable.</strong>',
						'error'
					);
					$error++;
				} else {
					if (($file) && (file_exists($file))) {
						if (!copy($file, $f2)) {
							$messageStack->add_session(
								'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;File: ' . $file . '<strong> Unable to make a backup/restoration file.</strong>',
								'error'
							);
							$error++;
						}
						if (file_exists($f2)) {
							unlink($f2);
						}
					}
				}
				$this->_restorePerms($folder);
			}
			if (file_exists($folder . "/ozpost_test.txt")) {
				unlink($folder . "/ozpost_test.txt");
			}
		// }
		}
		return $error;
	}
	
	/**
	 * @param int $install
	 * @return string
	 */
	private function _checkInc($install = 0)
	{
		$fn = DIR_FS_CATALOG . DIR_WS_MODULES . "shipping/ozpost.inc";
		$errors = "";
		if ((!is_file($fn)) && $install === 1) {
			$result = $this->_get_from_ozpostnet("/updates/ozpost.inc");
			if (($result) && (!strstr($result, "<error>"))) {
				if (!file_put_contents($fn, $result)) {
					$errors = "saving error";
				}
			} else {
				$errors = "download error";
			}
		}
		if (is_writable($fn)) {
			if (MODULE_SHIPPING_OZPOST_DEBUG !== "Yes") {
				if (!@chmod($fn, 0444)) {
					$errors = "<div>The ozpost.inc file is writable - it will not be used</div>";
				}
			}
		}
		return $errors;
	}

	////////////////////////////////////////////////////////////////////////////
	
	/**
	 *
	 */
	public function install()
	{
		global $db, $messageStack, $Ozfiles, $updavail, $weight_factor;  //  = $GLOBALS['template_dir'];
		$errors = $this->_preInstallTests();
		if ($errors === 0) {
			/////////////////////////  create tables if they don't already exist //////
			global $sniffer;
			if (!$sniffer->field_exists(TABLE_PRODUCTS, 'products_length')) {
				$db->Execute(
					"ALTER TABLE " . TABLE_PRODUCTS . " ADD `products_length` FLOAT(6,2) NULL AFTER `products_weight`,
					 ADD `products_height` FLOAT(6,2) NULL AFTER `products_length`, ADD `products_width` FLOAT(6,2) NULL AFTER `products_height`"
				);
			}
			if (!$sniffer->field_exists(TABLE_PRODUCTS, 'dangerous_goods')) {
				$db->Execute(
					"ALTER TABLE " . TABLE_PRODUCTS . " ADD `dangerous_goods` INT(1) DEFAULT 0 AFTER `products_width`"
				);
			}
			
			////////  PHP Version Test ///
			if (substr(PHP_VERSION, 0, 1) <= "4") {
				$messageStack->add_session('Installation FAILED. Ozpost requires PHP5.1 or higher. ');
				echo "This module requires PHP5 or higher to work. Most Webhosts will support this.<br>Installation will NOT continue.<br>Press your back-page to continue ";
				exit;
			}
			
			if (!class_exists('SimpleXMLElement')) {
				$messageStack->add_session(
					'Installation FAILED. Ozpost requires SimpleXMLElement to be installed on the system '
				);
				echo "This module requires SimpleXMLElement to work. Most Webhosts will support this.<br>Installation will NOT continue.<br>Press your back-page to continue ";
				exit;
			}
			
			// cURL & Connectivity test //
			$suburb = "";
			$postcode = "";
			$result = $this->_get_from_ozpostnet("/quotefor.php?flags=get_latest_client_version");
			if (substr($result, 0, 1) != "V") {
				$messageStack->add_session('Network Connectivity test FAILED<br>Is cURL installed?');
				echo 'Network Connectivity test FAILED<br>Is cURL installed?';
			} else {
				$this->_checkInc(1);
			}
			
			if (SHIPPING_ORIGIN_ZIP != "") {
				$postcode = SHIPPING_ORIGIN_ZIP;
				$suburb = urldecode($this->_get_from_ozpostnet("/quotefor.php?flags=get_suburb&fromcode=" . $postcode));
			}
			
			//  Kgs or Gms ? (if not set use bestguess)
			$res = $db->Execute(
				"select configuration_value from " . TABLE_CONFIGURATION . " WHERE configuration_key = 'bakMODULE_SHIPPING_OZPOST_WEIGHT_FORMAT' limit 1"
			);
			$wf = strtolower($res->fields['configuration_value']);
			$weight_factor = $wf[0];
			
			if (!$weight_factor) {
				$weight_factor = "g";
				$res = $db->Execute(
					"select products_weight from " . TABLE_PRODUCTS . " WHERE products_weight LIKE '%\.%' limit 1"
				);
				if (($res->fields['products_weight']) && (($res->fields['products_weight']) < 60)) {
					$weight_factor = "k";
				} // over 60kg is probably a mistake
			}
			
			if ($weight_factor == "k") {
				$weight_factor = "kilos";
			} // compatibility fix //
			if ($weight_factor == "g") {
				$weight_factor = "grams";
			}   // compatibility fix //
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
				 VALUES ('</div><div style=\"background:#C1C9CF; color:black\">Enabled : <a target=_self href=modules.php?set=shipping&module=ozpost&action=test>Click here to test the ozpost Servers</a>',
				 'MODULE_SHIPPING_OZPOST_STATUS', 'True', 'Enable this Module', '6', '1', 'ozpost::ozp_cfg_select_option(array(\'True\', \'False\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 VALUES ('Subscription reminders will be sent to', 'MODULE_SHIPPING_OZPOST_EMAIL', '', 'Enter a valid email address for the person responsible for subscription renewals.', '6', '1', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
				 VALUES ('Reminders and update alerts on main screen?', 'MODULE_SHIPPING_OZPOST_ALERTS', 'Yes',
				 'Show subscription and module update alerts on main screen?<br>Note: Subscription reminders/countdown will only show if 10 or less days are left. ',
				  '6', '1', 'ozpost::ozp_cfg_select_option(array(\'Yes\', \'No\'), ', now())"
			);
			$db->Execute(
				"update " . TABLE_CONFIGURATION . " set configuration_value = \"2\" where configuration_key = \"SHOW_SHIPPING_ESTIMATOR_BUTTON\""
			);
			
			// Aust Post letters
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function,  date_added)
                  values ('</div><hr> <div>AustPost Letters (and small parcels@letter rates)', 'MODULE_SHIPPING_OZPOST_TYPE_LETTERS',
                            'Aust Standard, Aust Registered, Overseas Economy',
                            'Select the methods you wish to allow',
                            '6','3',
                            'zen_cfg_select_multioption(array(\'Aust Standard\',\'Aust Standard Insured\',\'Aust Priority\',\'Aust Priority Insured\',\'Aust Registered\',
                            \'Aust Registered Insured\', \'Aust Express\',  \'Aust Express +sig\',\'Aust Express Insured +sig\', \'Aust Express Insured (no sig)\',
                            \'Overseas Economy\',\'Overseas Economy Insured\',\'Overseas Economy Prepaid\',\'Overseas Economy Prepaid Insured\', \'Overseas Registered Prepaid Envelope\',
                            \'Overseas Express Letter (inc sig)\',\'Overseas Express Letter Insured (inc sig)\',\'Overseas Prepaid Express Letter (inc sig)\',\'Overseas Prepaid Express Letter Insured (inc sig)\',
                            \'Overseas Courier Letter\',\'Overseas Courier Letter Insured\',\'Overseas Courier Prepaid Satchel\',\'Overseas Courier Prepaid Satchel Insured\'), ',
                            now())"
			);
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('</div><div style=\"background:#FFB800\">Handling Fee - Standard Letters',
				 'MODULE_SHIPPING_OZPOST_LET_HANDLING', '2.00', 'Handling Fee for Standard letters.', '6', '13', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('</div><div style=\"background:#FFB800\">Handling Fee - Priority Letters',
				 'MODULE_SHIPPING_OZPOST_LETP_HANDLING', '2.50', 'Handling Fee for Priority letters.', '6', '13', now())"
			);
			
			// Aust Post parcels
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function,  date_added)
                  values ('<hr></div><div style=\"background:aliceblue\">Australia Post Parcels - Australia', 'MODULE_SHIPPING_OZPOST_TYPE_APP',
                            'Regular, Regular +Signature, Insured (inc Sign)',
                            'Select the methods you wish to allow',
                            '6','3',
                            'zen_cfg_select_multioption(array(\'Regular\',\'Regular +Signature\',\'Insured (inc Sign)\',\'Insured (no sig)\',
                            \'Express\', \'Express +Signature\',\'Insured Express (inc Sign)\', \'Insured Express (no sig)\',
                            \'Cash on Delivery\'), ',
                            now())"
			);
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</div><div style=\"background:#FFB800\">Handling Fee - Regular parcels', 'MODULE_SHIPPING_OZPOST_RPP_HANDLING', '5.00', 'Handling Fee Regular parcels (keep in mind that handling fees need to include packaging material)', '6', '14', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</div><div style=\"background:#FFB800\">Handling Fee - COD', 'MODULE_SHIPPING_OZPOST_COD_HANDLING', '12.00', 'Handling Fee for COD deliveries', '6', '18', now())"
			);

//   // Click n Send
//        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
//                                                            configuration_group_id, sort_order, set_function, date_added)
//                    values ('</div><div>ClickNsend Prepaid', 'MODULE_SHIPPING_OZPOST_TYPE_CNS',
//                            ' ',
//                            'Select the methods you wish to allow.',
//                            '6','4',
//                            'zen_cfg_select_multioption(array(
//                            \'500g Satchel\',
//                            \'500g Express Satchel\',
//                            \'3kg Satchel\',
//                            \'3kg Express Satchel\',
//                            \'5kg Satchel\',
//                            \'5kg Express Satchel\',
//                            \'Small box 1kg\',
//                            \'Medium box 3kg\'), ',
//                            now())" ) ;
//
//   $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</div><div style=\"background:#FFB800\">Handling Fee - Click n Send Satchels', 'MODULE_SHIPPING_OZPOST_CNSS_HANDLING', '0.00', 'Handling Fee for Click n Send Satchels.', '6', '15', now())");
//    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</div><div style=\"background:#FFB800\">Handling Fee - Click n Send Boxes', 'MODULE_SHIPPING_OZPOST_CNSB_HANDLING', '0.00', 'Handling Fee for Click n Send boxes.<br>Tip: Quotes are based on minimum purchase costs. If you buy in larger quantities and wish to pass the discount on you can enter negative values here. ', '6', '15', now())");
//
			
			
			// Aust Post Satchels
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function,  date_added)
                  values ('</div><div>Australia Post PrePaid Satchels', 'MODULE_SHIPPING_OZPOST_TYPE_APS',
                            '500g Satchel.,3kg Satchel.,5kg Satchel.',
                            'Select the methods you wish to allow',
                            '6','3',
                            'zen_cfg_select_multioption(array(
                            \'500g Satchel.\',
                            \'500g Satchel +Signature\',
                            \'500g Insured Satchel (no sig)\',
                            \'500g Insured Satchel (inc Sign)\',
                            \'500g Express Satchel.\',
                            \'500g Express +Signature\',
                            \'500g Express Insured (no sig)\',
                            \'500g Express Insured (inc Sign)\',
                            
                            \'1kg Satchel.\',
                            \'1kg Satchel +Signature\',
                            \'1kg Insured Satchel (no sig)\',
                            \'1kg Insured Satchel (inc Sign)\',
                            \'1kg Express Satchel.\',
                            \'1kg Express +Signature\',
                            \'1kg Express Insured (no sig)\',
                            \'1kg Express Insured (inc Sign)\',
                            
                            \'3kg Satchel.\',
                            \'3kg Satchel +Signature\',
                            \'3kg Insured Satchel (no sig)\',
                            \'3kg Insured Satchel (inc Sign)\',
                            \'3kg Express Satchel.\',
                            \'3kg Express +Signature\',
                            \'3kg Express Insured (no sig)\',
                            \'3kg Express Insured (inc Sign)\',
                            
                            \'5kg Satchel.\',
                            \'5kg Satchel +Signature\',
                            \'5kg Insured Satchel (no sig)\',
                            \'5kg Insured Satchel (inc Sign)\',
                            \'5kg Express Satchel.\',
                            \'5kg Express +Signature\',
                            \'5kg Express Insured (no sig)\',
                            \'5kg Express Insured (inc Sign)\'), ',
                            now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</div><div style=\"background:#FFB800\">Handling Fee - Prepaid Satchels', 'MODULE_SHIPPING_OZPOST_PPS_HANDLING', '4.00', 'Handling Fee for Prepaid Satchels.', '6', '15', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</div><div style=\"background:#FFB800\">Handling Fee - Prepaid Satchels - Express', 'MODULE_SHIPPING_OZPOST_PPSE_HANDLING', '5.00', 'Handling Fee for Prepaid Express Satchels.', '6', '16', now())"
			);
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('</div><div style=\"background:#e6c776\">Use Discount rates for Regular 500g Prepaid Satchels? ', 'MODULE_SHIPPING_OZPOST_AP_DISCOUNT1R', '0', '0 - No discount, 1 = 5% discount, 2 - 12.5% discount', '6', '7', 'ozpost::ozp_cfg_select_option(array(\'0\',\'1\',\'2\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use Discount rates for Regular 3kg Satchels? ', 'MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R', '0',  '', '6', '7', 'ozpost::ozp_cfg_select_option(array(\'0\',\'1\',\'2\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use Discount rates for Regular 5kg Satchels? ', 'MODULE_SHIPPING_OZPOST_AP_DISCOUNT3R', '0',  '', '6', '7', 'ozpost::ozp_cfg_select_option(array(\'0\',\'1\',\'2\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use Discount rates for Express 500g Prepaid Satchels? ', 'MODULE_SHIPPING_OZPOST_AP_DISCOUNT1E', '0', '', '6', '7', 'ozpost::ozp_cfg_select_option(array(\'0\',\'1\',\'2\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use Discount rates for Express 3kg Satchels? ', 'MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E', '0',  '', '6', '7', 'ozpost::ozp_cfg_select_option(array(\'0\',\'1\',\'2\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Use Discount rates for Express 5kg Satchels? ', 'MODULE_SHIPPING_OZPOST_AP_DISCOUNT3E', '0',  '', '6', '7', 'ozpost::ozp_cfg_select_option(array(\'0\',\'1\',\'2\'), ', now())"
			);
			
			///   FastWay
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function, date_added)
                    values ('</div><div style=\"background:#C1C9CF\"><a href=https://www.fastway.com.au/>FastWay Couriers</a>', 'MODULE_SHIPPING_OZPOST_TYPE_FW',
                            ' ',
                            'Select the methods you wish to allow',
                            '6','4',
                            'zen_cfg_select_multioption(array(\'Red Label\',\'Orange Label\',\'Green Label\',
                            \'White Label\',\'Grey Label\',\'Brown Label\',\'Black Label\',
                            \'Blue Label\',\'Yellow Label\',\'Lime Label\',\'Pink Label\',
                            \'FW Small Box\',\'FW Medium Box\',\'FW Large Box\',
                            \'A5 Satchel (500g)\',\'A4 Satchel (1kg)\',\'A3 Satchel (local)\',\'A3 Satchel (3kg)\',\'A2 Satchel (5kg)\'), ',
                            now())"
			);
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (
						configuration_title, configuration_key,
						configuration_value, configuration_description, configuration_group_id, sort_order,
						set_function, date_added) VALUES ('</div><div style=\"background:#C1C9CF\">FastWay Franchise', 'MODULE_SHIPPING_OZPOST_FWF',
						'Select Franchise to enable', '' , '6', '8',
						'ozpost::ozp_cfg_select_drop_down(array(
							 array(\'id\'=>\'disabled\' , \'text\'=>\'disabled\'),
							 array(\'id\'=>\'ADL\' , \'text\'=>\'Adelaide\'),
							 array(\'id\'=>\'ALB\' , \'text\'=>\'Albury\'),
							 array(\'id\'=>\'BEN\' , \'text\'=>\'Bendigo\'),
							 array(\'id\'=>\'BRI\' , \'text\'=>\'Brisbane\'),
							 array(\'id\'=>\'CNS\' , \'text\'=>\'Cairns\'),
							 array(\'id\'=>\'CBR\' , \'text\'=>\'Canberra\'),
							 array(\'id\'=>\'CAP\' , \'text\'=>\'Capricorn Coast\'),
							 array(\'id\'=>\'CCT\' , \'text\'=>\'Central Coast\'),
							 array(\'id\'=>\'CFS\' , \'text\'=>\'Coffs Harbour\'),
							 array(\'id\'=>\'GEE\' , \'text\'=>\'Geelong\'),
							 array(\'id\'=>\'GLD\' , \'text\'=>\'Gold Coast\'),
							 array(\'id\'=>\'TAS\' , \'text\'=>\'Hobart\'),
							 array(\'id\'=>\'LAU\' , \'text\'=>\'Launceston\'),
							 array(\'id\'=>\'MKY\' , \'text\'=>\'Mackay\'),
							 array(\'id\'=>\'MEL\' , \'text\'=>\'Melbourne\'),
							 array(\'id\'=>\'NEW\' , \'text\'=>\'Newcastle\'),
							 array(\'id\'=>\'NTH\' , \'text\'=>\'Northern Rivers\'),
							 array(\'id\'=>\'PER\' , \'text\'=>\'Perth\'),
							 array(\'id\'=>\'PQQ\' , \'text\'=>\'Port Macquarie\'),
							 array(\'id\'=>\'SUN\' , \'text\'=>\'Sunshine Coast\'),
							 array(\'id\'=>\'SYD\' , \'text\'=>\'Sydney\'),
							 array(\'id\'=>\'TOO\' , \'text\'=>\'Toowoomba\'),
							 array(\'id\'=>\'TVL\' , \'text\'=>\'Townsville\'),
							 array(\'id\'=>\'BDB\' , \'text\'=>\'Wide Bay\'),
							 array(\'id\'=>\'WOL\' , \'text\'=>\'Wollongong\')),',
                            now())"
			);
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
				 VALUES ('FastWay frequent user?', 'MODULE_SHIPPING_OZPOST_FW_FREQ', 'No', 'Frequent users have lower rates, but require a minimum monthly spend.',
				 '6', '9', 'ozpost::ozp_cfg_select_option(array(\'Yes\', \'No\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Special user base weight (kgs).', 'MODULE_SHIPPING_OZPOST_FW_FREQ_SPBW', '', 'Leave blank unless you have a special rate contract.', '6', '9', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('</div><div style=\"background:#FFB800\">Handling Fee - FastWay Labels', 'MODULE_SHIPPING_OZPOST_FWL_HANDLING', '1.00', '', '6', '22', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('</div><div style=\"background:#FFB800\">Handling Fee - FastWay Boxes', 'MODULE_SHIPPING_OZPOST_FWB_HANDLING', '2.50', '', '6', '22', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('</div><div style=\"background:#FFB800\">Handling Fee - FastWay Satchels', 'MODULE_SHIPPING_OZPOST_FWS_HANDLING', '0.50', '', '6', '23', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Cost of your FW Small Box', 'MODULE_SHIPPING_OZPOST_FW_BOXS', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Cost of your FW Medium Box', 'MODULE_SHIPPING_OZPOST_FW_BOXM', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Cost of your FW Large Box', 'MODULE_SHIPPING_OZPOST_FW_BOXL', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Cost of your FW A5 Satchels', 'MODULE_SHIPPING_OZPOST_FW_SAT0', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
 				 values ('Cost of your FW A4 Satchels', 'MODULE_SHIPPING_OZPOST_FW_SAT1', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Cost of your FW A3 Local Satchels', 'MODULE_SHIPPING_OZPOST_FW_SAT2', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Cost of your FW A3 National Satchels', 'MODULE_SHIPPING_OZPOST_FW_SAT3', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Cost of your FW A2 Satchels', 'MODULE_SHIPPING_OZPOST_FW_SAT4', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);

			// TNT
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function, date_added)
                    values ('</div><div><a href=https://www.tntexpress.com.au/interaction/asps/login.asp>TNT Australia</a>', 'MODULE_SHIPPING_OZPOST_TYPE_TNT',
                            ' ',
                            'Select the methods you wish to allow',
                            '6','4',
                            'zen_cfg_select_multioption(array(
                            \'Road Express\',
                            \'ONFC Satchel\',
                            \'Overnight Express by 9:00am\',
                            \'Overnight Express by 10:00am\',
                            \'Overnight Express by 12:00pm\',
                            \'Overnight Express by 5:00pm\',
                            \'Technology Express - Sensitive Express\',
                            \'Road Express Insured\',
                            \'ONFC Satchel Insured\',
                            \'Overnight Express by 9:00am Insured\',
                            \'Overnight Express by 10:00am Insured\',
                            \'Overnight Express by 12:00pm Insured\',
                            \'Overnight Express by 5:00pm Insured\',
                            \'Technology Express - Sensitive Express Insured\',), ',
                            now())"
			);
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('TNT Account Number', 'MODULE_SHIPPING_OZPOST_TNT_ACCT', '', 'A valid TNT account number is required for TNT quotes.<br>Sign up at: https://www.tntexpress.com.au/', '6', '10', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('TNT Login', 'MODULE_SHIPPING_OZPOST_TNT_USER', '', 'A valid TNT CITnumber (preferred) or usernname (obsoleted) is required for TNT quotes. ', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('TNT Password', 'MODULE_SHIPPING_OZPOST_TNT_PSWD', '', 'A valid TNT password is required for TNT quotes', '6', '12', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</div><div style=\"background:#FFB800\">Handling Fee - TNT Merchandise', 'MODULE_SHIPPING_OZPOST_TNT_HANDLING', '5.00', 'Handling Fee - TNT Merchandise.', '6', '24', now())"
			);
			
			
			// Startrack
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function, date_added)
                    values ('</div><div style=\"background:#C1C9CF\"><hr><a href=https://startrack.com.au>StarTrack Express</a><br>NOTE: These quotes can be VERY SLOW to obtain. This is due to the StarTrack servers and NOT ozpost. ', 'MODULE_SHIPPING_OZPOST_TYPE_STA',
                            ' ',
                            'Select the methods you wish to allow',
                            '6','4',
                            'zen_cfg_select_multioption(array(
			\'StarTrack 1kg Satchel\',
                   	\'StarTrack 3kg Satchel\',
			\'StarTrack 5kg Satchel\',
                        \'StarTrack Road Express\',
                        \'StarTrack Road Express Insured\',
			\'StarTrack Air Express\',
			\'StarTrack Air Express Insured\', ), ',
                            now())"
			);
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('StarTrack Username', 'MODULE_SHIPPING_OZPOST_STA_USER', '', 'A valid StarTrack Username is required for StarTrack quotes', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('StarTrack Password', 'MODULE_SHIPPING_OZPOST_STA_PSWD', '', 'A valid StarTrack Password is required for StarTrack quotes', '6', '12', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('StarTrack Account#', 'MODULE_SHIPPING_OZPOST_STA_ACCT', '', 'A valid StarTrack Account Number is required for StarTrack quotes', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('StarTrack Access Key', 'MODULE_SHIPPING_OZPOST_STA_KEY', '', 'A valid StarTrack Security Key is required for StarTrack quotes', '6', '12', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('</div><div style=\"background:#FFB800\">Handling Fee - StarTrack Express Parcels', 'MODULE_SHIPPING_OZPOST_STA_HANDLING', '5.00', '', '6', '24', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('</div><div style=\"background:#FFB800\">Handling Fee - StarTrack Express Satchels', 'MODULE_SHIPPING_OZPOST_STAS_HANDLING', '2.00', '', '6', '24', now())"
			);
			
			// Transdirect
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function, date_added)
                    values ('</div><div><a href=\"https://transdirect.com.au\">transdirect.com.au</a><br>NOTE: These quotes can be VERY SLOW to obtain. This is due to the Transdirect servers and NOT ozpost.',
                     		'MODULE_SHIPPING_OZPOST_TYPE_TRD',
                            ' ',
                            'Select the methods you wish to allow',
                            '6','4',
                            'zen_cfg_select_multioption(array(
                            \'Toll/Ipec\',
                            \'Toll/Ipec Insured\',
                            \'Toll Priority Overnight\',
                            \'Toll Priority Overnight Insured\',
                            \'Toll Priority Same Day\',
                            \'Toll Priority Same Day Insured\',
                            \'Allied Express\',
                            \'Allied Express Insured\',
                            \'Couriers Please (Authority to leave)\',
                            \'Couriers Please (Authority to leave) Insured\',
                            \'Couriers Please (Signature Required)\',
                            \'Couriers Please (Signature Required) Insured\',
                            \'Fastway\',
                            \'Fastway Insured\' ,
                            \'Mainfreight\',
                            \'Mainfreight Insured\',
                            \'Northline\',
                            \'Northline Insured\',
                            \'TNT Road Express\',
                            \'TNT Road Express Insured\',
                            \'TNT Overnight Express by 9:00am\',
                            \'TNT Overnight Express by 9:00am Insured\',
                            \'TNT Overnight Express by 10:00am\',
                            \'TNT Overnight Express by 10:00am Insured\',
                            \'TNT Overnight Express by 12:00pm\',
                            \'TNT Overnight Express by 12:00pm Insured\',
                            \'TNT Overnight Express by 5:00pm\',
                            \'TNT Overnight Express by 5:00pm Insured\',
                            \'TNT International Express\',
                            \'TNT International Express Insured\',
                            \'TNT International Document Express\',
                            \'TNT International Document Express Insured\',
                            \'TNT International Economy Express\',
                            \'TNT International Economy Express Insured\',
                            \'Toll International Priority\',
                            \'Toll International Priority Insured\',
                            \'Toll International Priority Document\',
                            \'Toll International Priority Document Insured\',), ',
                            now())"
			);
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Transdirect Email', 'MODULE_SHIPPING_OZPOST_TRD_USER', '', 'Transdirect Email Address', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Transdirect Password', 'MODULE_SHIPPING_OZPOST_TRD_PSWD', '', 'Transdirect password', '6', '12', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('</div><div style=\"background:#FFB800\">Handling Fee - TransDirect (Aust)', 'MODULE_SHIPPING_OZPOST_TRD_HANDLING', '5.00', '', '6', '24', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('</div><div style=\"background:#FFB800\">Handling Fee - TransDirect (International)', 'MODULE_SHIPPING_OZPOST_TRD_INT_HANDLING', '10.00', '', '6', '24', now())"
			);
			
			
			// Ego
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function, date_added)
                    values ('</div><div style=\"background:#C1C9CF\"><a href=\"https://e-go.com.au>e-go.com.au\"</a>', 'MODULE_SHIPPING_OZPOST_TYPE_EGO',
                            ' ',
                            'Select the methods you wish to allow',
                            '6','4',
                            'zen_cfg_select_multioption(array(
                            \'e-go.com.au\',
                            \'Insured e-go.com.au\' ), ',
                            now())"
			);
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('</div><div style=\"background:#FFB800\">Handling Fee - EGO', 'MODULE_SHIPPING_OZPOST_EGO_HANDLING', '5.00', '', '6', '24', now())"
			);
			
			// Skippy Post
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function, date_added)
                    values ('</div><div style=\"background:#C1C9CF\"><a href=\"https://skippypost.com.au\">skippypost.com.au (O/seas only)</a>', 'MODULE_SHIPPING_OZPOST_TYPE_SKP',
                            ' ',
                            'Select the methods you wish to allow',
                            '6','4',
                            'zen_cfg_select_multioption(array(
                                \'Skippy Post Air\',
                                \'Skippy Post Air with Tracking\',
                                \'Skippy Post Air with Tracking and Insurance\',
                                \'Skippy Post Air +Proof of postage\',
                                \'Skippy Post Air Insured +Proof of postage\',
                                \'Skippy Post Air with Tracking and Insurance +Proof of postage\' ),
                      		',
                            now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Skippy Post Customer Identifier', 'MODULE_SHIPPING_OZPOST_SKP_CUST', '', 'Optional: Cheaper rates if supplied', '6', '24', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('</div><div style=\"background:#FFB800\">Handling Fee - Skippy Post', 'MODULE_SHIPPING_OZPOST_SKP_HANDLING', '5.00', 'Handling Fee - Skippy Post.', '6', '27', now())"
			);
			
			
			// SmartSend
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function, date_added)
                    values ('</div><div style=\"background:#C1C9CF\"><a href=\"https://smartsend.com.au\">smartsend.com.au</a><br>NOTE: These quotes can be VERY SLOW to obtain. and may not even arrive in the allowed time. This is due to the Smartsend servers and NOT ozpost. ', 'MODULE_SHIPPING_OZPOST_TYPE_SMS',
                            ' ',
                            'Select the methods you wish to allow',
                            '6','4',
                            'zen_cfg_select_multioption(array(
                            \'Couriers Please Road\',
                            \'Couriers Please Road (receipted)\',
                            \'Couriers Please Road (insured)\',
                            \'Couriers Please Road (receipted + insured)\',
                            \'AAE 1kg Prepaid Satchel\',
                            \'AAE 1kg Prepaid Satchel (receipted)\',
                            \'AAE 1kg Prepaid Satchel (insured)\',
                            \'AAE 1kg Prepaid Satchel (receipted + insured)\',
                            \'AAE 3kg Prepaid Satchel\',
                            \'AAE 3kg Prepaid Satchel (receipted)\',
                            \'AAE 3kg Prepaid Satchel (insured)\',
                            \'AAE 3kg Prepaid Satchel (receipted + insured)\',
                            \'AAE 5kg Prepaid Satchel\',
                            \'AAE 5kg Prepaid Satchel (receipted)\',
                            \'AAE 5kg Prepaid Satchel (insured)\',
                            \'AAE 5kg Prepaid Satchel (receipted + insured)\',
                            \'AAE : Road\',
                            \'AAE : Road (receipted)\',
                            \'AAE : Road (insured)\',
                            \'AAE : Road (receipted + insured)\',
                            \'AAE : Express Premium\',
                            \'AAE : Express Premium (receipted)\',
                            \'AAE : Express Premium (insured)\',
                            \'AAE : Express Premium (receipted + insured)\',
                            \'AAE : Express Saver\',
                            \'AAE : Express Saver (receipted)\',
                            \'AAE : Express Saver (insured)\',
                            \'AAE : Express Saver (receipted + insured)\',
                            \'Fastway : National Road\',
                            \'Fastway : National Road (receipted)\',
                            \'Fastway : National Road (insured)\',
                            \'Fastway : National Road (receipted + insured)\',
                            \'Fastway : Local\',
                            \'Fastway : Local (receipted)\',
                            \'Fastway : Local (insured)\',
                            \'Fastway : Local (receipted + insured)\',
                            \'Fastway : Satchels\',
                            \'Fastway : Satchels (receipted)\',
                            \'Fastway : Satchels (insured)\',
                            \'Fastway : Satchels (receipted + insured)\',
                            \'TNT : Overnight by 9am\',
                            \'TNT : Overnight by 9am (receipted)\',
                            \'TNT : Overnight by 9am (insured)\',
                            \'TNT : Overnight by 9am (receipted + insured)\',
                            \'TNT : Overnight by 10am\',
                            \'TNT : Overnight by 10am (receipted)\',
                            \'TNT : Overnight by 10am (insured)\',
                            \'TNT : Overnight by 10am (receipted + insured)\',
                            \'TNT : Overnight by 12pm\',
                            \'TNT : Overnight by 12pm (receipted)\',
                            \'TNT : Overnight by 12pm (insured)\',
                            \'TNT : Overnight by 12pm (receipted + insured)\',
                            \'TNT : Overnight by 5pm\',
                            \'TNT : Overnight by 5pm (receipted)\',
                            \'TNT : Overnight by 5pm (insured)\',
                            \'TNT : Overnight by 5pm (receipted + insured)\',
                            \'TNT : Road\',
                            \'TNT : Road (receipted)\',
                            \'TNT : Road (insured)\',
                            \'TNT : Road (receipted + insured)\' ), ',
                            now())"
			);
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				values ('SmartSend Email', 'MODULE_SHIPPING_OZPOST_SMS_EMAIL', '', 'A valid SmartSend Customer email address is required for VALID quotes', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				values ('SmartSend Password', 'MODULE_SHIPPING_OZPOST_SMS_PASS', '', 'A valid SmartSend Password is required for SmartSend quotes', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
				values ('SmartSend Type', 'MODULE_SHIPPING_OZPOST_SMS_TYPE', 'VIP', 'SmartSend Customer Type (default = VIP)', '6', '12', 'ozpost::ozp_cfg_select_drop_down(array( array(\'id\'=>\'VIP\' , \'text\'=>\'VIP\'),array(\'id\'=>\'EBAY\' , \'text\'=>\'EBAY\'), array(\'id\'=>\'CORPORATE\' , \'text\'=>\'CORPORATE\'),  array(\'id\'=>\'PROMOTION\' , \'text\'=>\'PROMOTION\'), array(\'id\'=>\'CASUAL\' , \'text\'=>\'CASUAL\'),  array(\'id\'=>\'REFERRAL\' , \'text\'=>\'REFERRAL\')),', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				values ('</div><div><div style=\"background:#FFB800\">Handling Fee - SmartSend', 'MODULE_SHIPPING_OZPOST_SMS_HANDLING', '2.00', '', '6', '24', now())"
			);

// Couriers Please
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function, date_added)
                    values ('</div><div><a href=\"https://www.couriersplease.com.au\">Couriers Please</a>', 'MODULE_SHIPPING_OZPOST_TYPE_CPL',
                            ' ',
                            'Select the methods you wish to allow',
                            '6','4',
                            'zen_cfg_select_multioption(array(
                            \'CP 500g Satchel\',
                            \'CP 1kg Satchel\',
                            \'CP 3kg Satchel\',
                            \'CP 5kg Satchel\',
                            \'EZY Send\',
                            \'Same day - Authority to leave\',
                            \'Same day - signature required\',
                            \'Overnight - Authority to leave\',
                            \'Overnight - signature required\',
                            \'Domestic Priority - Authority to leave\',
                            \'Domestic Priority - signature required\',
                            \'Domestic saver- Authority to leave\',
                            \'Domestic saver - signature required\',
                            \'International Express\',
                            \'International Saver\'), ',
                                now())"
			);
			
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('</div><div style=\"background:#FFB800\">Handling Fee - Domestic Parcels', 'MODULE_SHIPPING_OZPOST_CPL_HANDLING', '5.00', '', '6', '24', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('</div><div style=\"background:#FFB800\">Handling Fee - International Parcels', 'MODULE_SHIPPING_OZPOST_CPLI_HANDLING', '10.00', '', '6', '24', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('</div><div style=\"background:#FFB800\">Handling Fee - Satchels', 'MODULE_SHIPPING_OZPOST_CPLS_HANDLING', '5.00', '', '6', '24', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Cost of your EZY Send Metro labels', 'MODULE_SHIPPING_OZPOST_CPL_METRO', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Cost of your EZY Send Link labels', 'MODULE_SHIPPING_OZPOST_CPL_EZY', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Cost of your CPL 500g Satchels', 'MODULE_SHIPPING_OZPOST_CPL_SAT0', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Cost of your CPL 1kg Satchels', 'MODULE_SHIPPING_OZPOST_CPL_SAT1', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Cost of your CPL 3kg Satchels', 'MODULE_SHIPPING_OZPOST_CPL_SAT2', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 values ('Cost of your CPL 5kg Satchels', 'MODULE_SHIPPING_OZPOST_CPL_SAT3', '0', 'Costs can vary on a per customer basis.<br>Leave at zero for standard rates.', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				values ('Couriers Please Account#', 'MODULE_SHIPPING_OZPOST_CPL_ACCT', '', 'Leave blank to use the testing server, else enter your Acct#', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				values ('Couriers Please API key', 'MODULE_SHIPPING_OZPOST_CPL_KEY', '', 'Leave blank to use the testing server,else enter your API key', '6', '12', now())"
			);

			// Sendle
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function, date_added)
                    values ('</div><div><a href=\"http://www.sendle.com.au\">Sendle</a>', 'MODULE_SHIPPING_OZPOST_TYPE_SDL',
  'Select the plan you are on ', '' , '6', '8',
        'ozpost::ozp_cfg_select_drop_down(array(
                     array(\'id\'=>\'disabled\' , \'text\'=>\'disabled\'),
                     array(\'id\'=>\'Easy\' , \'text\'=>\'Easy\'),
                     array(\'id\'=>\'Premium\' , \'text\'=>\'Premium\'),
                     array(\'id\'=>\'Pro\' , \'text\'=>\'Pro\')),',
                            now())"
			);
			
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					values ('</div><div style=\"background:#FFB800\">Handling Fee - Sendle', 'MODULE_SHIPPING_OZPOST_SDL_HANDLING', '5.00', 'Handling Fee - Sendle.', '6', '24', now())"
			);
			
			// Hunter Express
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function, date_added)
                    values ('</div><div><a href=\"https://hunterexpress.com.au\">Hunter Express</a>', 'MODULE_SHIPPING_OZPOST_TYPE_HX',
                            ' ',
                            'Select the methods you wish to allow',
                            '6','4',
                            'zen_cfg_select_multioption(array(
                            \'HX Road Express\',
                            \'HX Air Express\',
                            \'HX Home Direct Plus\' ), ',
                            now())"
			);
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					values ('Hunter Express Username', 'MODULE_SHIPPING_OZPOST_HX_USER', '', 'Hunter Express username', '6', '11', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					 values ('Hunter Express Password', 'MODULE_SHIPPING_OZPOST_HX_PSWD', '', 'Hunter Express password', '6', '12', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					 values ('Hunter Express Customer', 'MODULE_SHIPPING_OZPOST_HX_CUST', '', 'Hunter Express Customer - enter \'Demo\' for example rates (not all destinations supported)', '6', '12', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					values ('Hunter Express Fuel levy (percent)', 'MODULE_SHIPPING_OZPOST_HX_FUELLEVY', '', 'Hunter Express Fuel levy (if undefined 14% is assumed', '6', '12', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					values ('</div><div style=\"background:#FFB800\">Handling Fee - Hunter Express', 'MODULE_SHIPPING_OZPOST_HX_HANDLING', '5.00', 'Handling Fee - Hunter Express.', '6', '24', now())"
			);


			// Aust Post oseas
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
               configuration_group_id, sort_order, set_function, date_added)
                        values ('</div><div>Australia Post Overseas Parcels', 'MODULE_SHIPPING_OZPOST_TYPE_APOS',
                        'Economy Air, Economy Air +sig, Economy Air Insured, Economy Air Insured +sig, Standard Air. Standard Air +sig, Standard Air Insured, Standard Air Insured +sig',
                        'You get the idea..',
                        '6','4',
                        'zen_cfg_select_multioption(array(
                        \'Economy Air\',  \'Economy Air +sig\', \'Economy Air Insured\',  \'Economy Air Insured +sig\',
                        \'Standard Air\',  \'Standard Air +sig\', \'Standard Air Insured\',  \'Standard Air Insured +sig\',
                        \'Standard Air 500g Satchel\', \'Standard Air 500g Satchel +sig\',  \'Standard Air 500g Satchel Insured\',\'Standard Air 500g Satchel Insured +sig\',
                        \'Standard Air 1kg Satchel\', \'Standard Air 1kg Satchel +sig\', \'Standard Air 1kg Satchel Insured\', \'Standard Air 1kg Satchel Insured +sig\',
                        \'Standard Air 2kg Satchel\', \'Standard Air 2kg Satchel +sig\',  \'Standard Air 2kg Satchel Insured\', \'Standard Air 2kg Satchel Insured +sig\',
                        \'Standard Air 5kg Box\',   \'Standard Air 5kg Box +sig\',   \'Standard Air 5kg Box Insured\',  \'Standard Air 5kg Box Insured +sig\',
                        \'Express Air (inc sig)\',  \'Express Air Insured (inc sig)\',
                        \'Express Air 500g Satchel\',\'Express Air 500g Satchel Insured\',
                        \'Express Air 1kg Satchel\', \'Express Air 1kg Satchel Insured\',
                        \'Express Air 2kg Satchel\', \'Express Air 2kg Satchel Insured\',
                        \'Express Air 5kg Box\',  \'Express Air 5kg Box Insured\',
                        \'Courier Air (inc sig)\',  \'Courier Air Insured (inc sig)\',
                        \'Courier Air 500g Satchel\', \'Courier Air 500g Satchel Insured\', \'Courier Air 1kg Satchel\',   \'Courier Air 1kg Satchel Insured\',
                        \'Sea\',\'Insured Sea\'),',
                            now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					values ('</div><div style=\"background:#FFB800\">Handling Fee - Overseas parcels', 'MODULE_SHIPPING_OZPOST_INT_HANDLING', '8.00',
					'Handling Fee for overseas parcels (These may require additional packaging).', '6', '19', now())"
			);


			//  Other settings and options //
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
                                                            configuration_group_id, sort_order, set_function, date_added)
                    values ('</div><div style=\"background:#C1C9CF\">Mail Days', 'MODULE_SHIPPING_OZPOST_MAILDAYS',
                            'Monday, Tuesday, Wednesday, Thursday, Friday',
                            'Select the days you mail',
                            '6','4',
                            'zen_cfg_select_multioption(array(\'Monday\',\'Tuesday\',\'Wednesday\',\'Thursday\',\'Friday\',\'Saturday\' ,\'Sunday\'), ',
                            now())"
			);
			
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
				VALUES ('Estimated Delivery Format', 'MODULE_SHIPPING_OZPOST_EF', 'Date', 'Try both, or None, see which you prefer', '6', '35', 'ozpost::ozp_cfg_select_option(array(\'None\', \'Date\', \'Days\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
 values ('Deadline \(hour\)', 'MODULE_SHIPPING_OZPOST_DEADLINE', '10', 'Deadline for same day mailings<br>10= 10am, 11=11am, 12=noon, 13=1pm, etc<br>This uses the store localtime based on the postcode', '6', '24', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				 VALUES ('Shipping from PostCode', 'MODULE_SHIPPING_OZPOST_ORIGIN_ZIP', '$postcode', 'What postcode do you ship from?', '7', '2', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				VALUES ('Shipping from Suburb', 'MODULE_SHIPPING_OZPOST_ORIGIN_SUBURB', '$suburb', 'What suburb do you ship from?', '7', '2', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('</div><div style=\"background:#15F141\">Hide Satchel/Parcel rates if letter sized \(domestic\)? ', 'MODULE_SHIPPING_OZPOST_HIDE_PARCELD', 'No', 'If the parcel/items are letter sized would you like to hide the parcel postage rates? \(domestic shipments\)', '6', '7', 'ozpost::ozp_cfg_select_option(array(\'Yes\', \'No\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('</div><div style=\"background:#15F141\">Hide Satchel/Parcel rates if letter sized \(overseas\)? ', 'MODULE_SHIPPING_OZPOST_HIDE_PARCELO', 'No', 'If the parcel/items are letter sized would you like to hide the parcel postage rates? \(overseas shipments\)', '6', '7', 'ozpost::ozp_cfg_select_option(array(\'Yes\', \'No\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('</div><div style=\"background:#15F141\">Hide parcel rates if Satchel sized ? ', 'MODULE_SHIPPING_OZPOST_HIDE_PARCEL2', 'No</br>', 'If the items will fit into a satchel would you like to hide the parcel postage rates? </br>Note: Has no effect if debug is enabled</br>', '6', '7', 'ozpost::ozp_cfg_select_option(array(\'No</br>\', \'If Less than 5kg\',\'If Less than 3kg</br>\',\'If Less than 1kg\',\'If Less than 500g\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('</div><div style=\"background:#15F141\">Hide Courier rates if Australia Post can handle it? ', 'MODULE_SHIPPING_OZPOST_HIDE_COURIER', 'No', 'If the items can be sent by Australia Post would you like to hide the courier rates? </br>Note: Has no effect if debug is enabled</br>', '6', '7', 'ozpost::ozp_cfg_select_option(array(\'Yes\', \'No\'), ', now())"
			);
			
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					values ('</div><div style=\"background:#ffffff\">Handling Fee - Registered, Signed or Insured parcels \& letters', 'MODULE_SHIPPING_OZPOST_RI_HANDLING', '2.00', 'Handling Fee for Registered and/or Insured Parcels & Letters - This is <strong>in addition</strong> to other handling fees.', '6', '25', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					values ('</div><div style=\"background:#ffffff\">Handling Fee - Express parcels', 'MODULE_SHIPPING_OZPOST_EXP_HANDLING', '5.00', 'Handling Fee Express parcels - This is <strong>in addition</strong> to other handling fees (Excluding Prepaid satchels).', '6', '17', now())"
			);
			
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
						values ('</div><div style=\"background:#EBD08A;\">Heavy Parcel Surcharge - Add this amount if parcel exceeds heavy weight limit ',
						'MODULE_SHIPPING_OZPOST_HW_SURCHARGE', '25.00', 'Heavy Weight Surcharge.', '6', '25', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				values ('Heavy Weight limit (Kg)', 'MODULE_SHIPPING_OZPOST_HW_LIMIT', '25', 'How heaver is your heavy weight?.', '6', '24', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('</div><div style=\"background:#15F141\">Hide Handling Fees?', 'MODULE_SHIPPING_OZPOST_HIDE_HANDLING', 'No',
					'The handling fees are still in the total shipping cost but the Handling Fee is not itemised on the invoice.',
					'6', '26', 'ozpost::ozp_cfg_select_option(array(\'Yes\', \'No\'), ', now())"
			);

			//default values to be used when pricing servers not accessible
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('</div><div style=\"background:#EBD08A\">Default Rates', 'MODULE_SHIPPING_OZPOST_COST_ON_ERROR_TYPE',
					'Static Rates',
					'These rates are only shown when no other methods are available, eg: server offline, network glitches, etc.<br><br> Choose from among the following:<br><em>Static rates</em> are applied as Flat Rate per Order, per item Weight, or per item Price<br><em>Table rates</em> provide more flexibility by describing ranges of item count, weight and price for which different rates may be applied<br><em>TBA</em> will produce a \"Contact store owner\" message with a zero cost. This text can be defined in the ozpost language file. <strong>Warning</strong> TBA may cause a problem with PayPal Express. It cannot prevent a customer from checking out without a shipping cost.<br><em>Do Nothing</em> will disable the module and no shipping rate will be offered.<br><br><em>Select a Default Rate:</em><br>',
					'6', '1000', 'ozpost::ozp_cfg_select_option(array(\'Static Rates\', \'Table Rates\', \'TBA\', \'Do Nothing\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('<HR>Static Mode', 'MODULE_SHIPPING_OZPOST_STATIC_MODE', 'Flat Rate',
					'The shipping cost is based on the Flat Rate per Order, or the per Item Weight, or the per Item Price.<br><br><em>Select the basis for calculating Static rates:</em><br>',
					'6', '1000', 'ozpost::ozp_cfg_select_option(array(\'Flat Rate\', \'Cost per kg\', \'Cost per Item\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					VALUES ('Static Rates', 'MODULE_SHIPPING_OZPOST_COST_ON_ERROR', '25.00,99.99',
					'The first value is for Australian delivery. The second value for Overseas delivery.<br>Values are <strong>COMMA</strong> separated<br>', '6', '1000', now())"
			);
			// $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('    Table Mode', 'MODULE_SHIPPING_OZPOST_TABLE_MODE', 'weight', 'The shipping cost is based on the order total or the total weight of the items ordered or the total number of items orderd.', '6', '29','ozpost::ozp_cfg_select_option(array(\'weight\', \'price\', \'item\'), ', now())");
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('<HR>Table Mode', 'MODULE_SHIPPING_OZPOST_TABLE_MODE', 'weight',
					'The shipping cost will be based on the the total weight of the items ordered, or the total value of the order, or the total number of items ordered.<br><br><em>Select the basis for calculating Table rates:</em><br>',
					'6', '1000', 'ozpost::ozp_cfg_select_option(array(\'weight\', \'price\', \'item\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					VALUES ('Table Rates', 'MODULE_SHIPPING_OZPOST_COST_ON_ERROR2', '0.5:6.35,3:7.26,5:9.09,10:10.90,15:12.27 0.5:20.00,3:25.00,5:30.00,10:40.00,15:50.00',
					'<br>Value sets are separated by a <strong>SPACE</strong> character. The first set is for Australian delivery. The second set for Overseas delivery.<br>Within each set value pairs are separated by a <strong>COMMA</strong> character.<br>Each value is paired with a price, separated by a <strong>COLON</strong> eg 0.5:6.35 is 0.5kg:$6.35<br>',
					 '6', '1000', now())"
			);
			//end default values
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('</div><div style=\"background:#0F71EE; color:black\">Action on zero weights', 'MODULE_SHIPPING_OZPOST_ZERO_WEIGHT', 'Use Default weight',
					'Items without weights cannot be quoted. What shall we do with them?</br><strong>Virtual</strong> and <strong>Always Free Shipping products</strong> are always ignored.<br>If set to use Default and the Default item weight is zero the item will also be Ignored. ',
					'6', '29', 'ozpost::ozp_cfg_select_option(array(\'Alert\', \'Ignore\', \'Use Default weight\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					VALUES ('</div><div style=\"background:#0FB0EE; color:black\">Default ITEM Dimensions', 'MODULE_SHIPPING_OZPOST_DIMS', '29,25,2.5',
					'Default ITEM dimensions (in cm). Three comma separated values (eg 29,24,2.5 = 29cm x 24cm x 2.5cm). These are used if the dimensions of individual products are not set',
					'6', '28', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					VALUES ('</div><div id=\"wgt\" style=\"background:#0FB0EE; color:black\">Default ITEM Weight',
					'MODULE_SHIPPING_OZPOST_DEFW', '1000',
					'Default PRODUCT weight (grams). This is used if the <strong>Action on zero weights \=\"Default\"</strong> and the individual products don\'t have a defined weight.',
					'6', '28', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('</div><div id=\"wfm\" style=\"background:#15F141\">Weight factor', 'MODULE_SHIPPING_OZPOST_WEIGHT_FORMAT',
					'$weight_factor', 'Are your store items weighted by grams or kilos?',
					'6', '30', 'ozpost::ozp_cfg_select_option(array(\'grams\', \'kilos\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					values ('</div><div style=\"background:#0FB0EE; color:black\">Tare Weight (%).', 'MODULE_SHIPPING_OZPOST_TARE',
					'500:+90,1000:20,2000:15,10000:+2000',
					'Tare weight (+grams or %). Default (500:+90,1000:20, 2000:15, 10000:+2000, 20000:10) equates to 0-500g +90gms, 501-1000g +20%, 1001-2000 +15%, over 10kg  +2kg and over 20kg +10% .',
					'6', '31', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					VALUES ('</div><div style=\"background:#0FB0EE; color:black\">Tare Dimensions', 'MODULE_SHIPPING_OZPOST_DIMTARE', '2,2,2',
					'Package Tare dimensions (in mm). Three comma separated values (eg 2,2,2 = 2mm x 2mm x 2mm).', '6', '33', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('</div><div style=\"background:#DF0FF0; color:black\">Icons Style', 'MODULE_SHIPPING_OZPOST_ICONSS', 'Carriers Only', 'What icons do you prefer\? (not available for all templates)', '6', '35', 'ozpost::ozp_cfg_select_option(array(\'None\', \'Carriers Only\', \'Carriers+Methods\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
				VALUES ('Icons Type', 'MODULE_SHIPPING_OZPOST_ICONS', 'jpg', 'Different icon types for different needs. What\'s yours\?', '6', '35', 'ozpost::ozp_cfg_select_option(array(\'jpg\', \'png\', \'gif\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('</div><div style=\"background:#C1C9CF\">Enable Debug?', 'MODULE_SHIPPING_OZPOST_DEBUG', 'No',
					'Shows all the methods returned by the server (including all informational messages, warnings and errors).</br><strong>ENABLE this to help find problems</strong></br>The output isn\'t pretty.',
					'6', '40', 'ozpost::ozp_cfg_select_option(array(\'Yes\', \'No\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('</div><div style=\"background:#C1C9CF\">Show parcel info?',
					'MODULE_SHIPPING_OZPOST_SHOW_PARCEL', 'No', 'See how parcels are created from individual items.',
					'6', '40', 'ozpost::ozp_cfg_select_option(array(\'Yes\', \'No\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('<hr>Show error messages?', 'MODULE_SHIPPING_OZPOST_MSG', 'Yes',
					'Shows errors, such as overweight, invalid dimensions, etc.',
					'6', '40', 'ozpost::ozp_cfg_select_option(array(\'Yes\', \'No\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					values ('Lead time \(days\).', 'MODULE_SHIPPING_OZPOST_LEADTIME', '0',
					'Add this number of days before estimating delivery times. Useful if you don\'t carry stock in hand and need \'x\' days to source before estimating delivery times/dates.<br>Setting this \>0 makes a mockery of overnight delivery estimates, which should probably be disabled.',
					'6', '45', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					values ('Use Core Weight Switch.', 'MODULE_SHIPPING_OZPOST_CORE_WEIGHT', 'No',
					'Set this to YES if you wish your parcels to be split by weight as set in the Shipping/Packaging section. <note>Requires the \"example\" modifiers in the \"ozpost.inc\" file<br><strong><span style=\"color:black\">Warning: Setting this to YES may cause parcels to be split in ways that are physically impossible</span></strong>.',
					'6', '70', 'ozpost::ozp_cfg_select_option(array(\'Yes\', \'No\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					values ('Restrain Dimensions.', 'MODULE_SHIPPING_OZPOST_RESTRAIN_DIMS', 'No',
					'Set this to YES if you wish to restrain the parcel dimensions to within allowable limits.  <strong><span style=\"color:black\">Warning: Setting this to YES may produce quotes for parcels that are too large for the carrier(s) to handle.</span></strong>',
					 '6', '75', 'ozpost::ozp_cfg_select_option(array(\'Yes\', \'No\'), ',now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					values ('<hr>Sort order of display.', 'MODULE_SHIPPING_OZPOST_SORT_ORDER', '0',
					'Sort order of display. Lowest is displayed first.', '6', '85', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added)
					values ('<hr>Tax Class', 'MODULE_SHIPPING_OZPOST_TAX_CLASS', '0',
					'Set Tax class or -none- if not registered for GST.',
					'6', '90', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
					values ('EmailFlag', 'MODULE_SHIPPING_OZPOST_EMAIL_FLAG', '0',
					'Internal use only (prevents multiple emails being sent for subscription reminders)', '6', '90', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
					VALUES ('<hr>Remove button Action', 'MODULE_SHIPPING_OZPOST_REMOVE_ACTION', 'Upgrade',
					'Setting this to Upgrade will retain your current data during the remove/install process.',
					'6', '26', 'ozpost::ozp_cfg_select_option(array(\'Upgrade\', \'Complete removal\'), ', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				values ('DBversion', 'MODULE_SHIPPING_OZPOST_DB_VERS', '$this->VERSION',
				'Internal use only. Used to ensure databases and files remain syncronised', '6', '90', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				VALUES ('Subscription Days remaining','MODULE_SHIPPING_OZPOST_LAST_CHECK', '',  'Internal use only',
				'6', '100', now())"
			);
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
				VALUES ('Subscription Expires','MODULE_SHIPPING_OZPOST_EXPIRES', '',  'Internal use only',
				'6', '100', now())"
			);
			
			$db->Execute(
				"insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
				VALUES ('Disable module if destination overseas?', 'MODULE_SHIPPING_OZPOST_NOOS', 'No', '',
				'6', '40', 'ozpost::ozp_cfg_select_option(array(\'Yes\', \'No\'), ', now())"
			);

			//// restore from backup data    (if exists)
			$query = "select configuration_key as 'key', configuration_value from " . TABLE_CONFIGURATION . " where configuration_key like 'bakMODULE_SHIPPING_OZPOST_%' ";
			$row = $db->Execute($query);
			while (!$row->EOF) {
				$key = substr($row->fields['key'], 3);
				$value = $row->fields['configuration_value'];
				$db->Execute(
					"update  " . TABLE_CONFIGURATION . " set configuration_value = '$value' where configuration_key = '$key' "
				);
				$row->MoveNext();
			}
			
			// keep the version up to date ('cos the new/current would have been overwritten with the restore)
			$db->Execute(
				"update  " . TABLE_CONFIGURATION . " set configuration_value = '$this->VERSION' where configuration_key like 'MODULE_SHIPPING_OZPOST_DB_VERS' "
			);
			
			// delete for next time  (shouldn't need this, but man removal of DB data will leave em lying around causing re-install problems
			$db->Execute(
				"delete from " . TABLE_CONFIGURATION . " where configuration_key like 'bakMODULE_SHIPPING_OZPOST_%' "
			);

			////////////////////
			$errors = 0;
			$weightdefine = "define('TEXT_PRODUCTS_WEIGHT', 'Product Shipping Weight (" . $weight_factor . "):') ;\n";
			///   modify  admin/includes/languages/english/product.php
			$file = $Ozfiles[0][0] . $Ozfiles[0][1] . $Ozfiles[0][2];
			$data = null;
			$doAll = 0;
			$errors = 0;
			$ln = 0;
			$rdata = file($file);
			if ($rdata) {
				foreach ($rdata as $line) {
					if ((strstr($line, "define('TEXT_PRODUCTS_WEIGHT',"))) {  //  Always update this //
						$data = $weightdefine;
						array_splice($rdata, $ln, 1, (array)$data);
					}
					$ln++;
				}
				
				if (!preg_grep("/TEXT_PRODUCTS_DG/", $rdata)) {
					//              echo "<br>DG Needed 1 " . $file;
					if (!preg_grep("/define\('TEXT_PRODUCTS_LENGTH'/", $rdata)) {
						$doAll = 1;
					}// echo "<br>DIMS NEEDED 1 ".$file ;
					
					$ln = 0;
					foreach ($rdata as $line) {
						if (strstr($line, "define('TEXT_PRODUCTS_LENGTH',")) {  //  Already has dimensions
//                   echo "<br>DG 1"   ;
							$data = $line . "define('TEXT_PRODUCTS_DG',  'Dangerous Goods?') ;\ndefine('TEXT_DANGEROUS_GOODS_EDIT',  'Dangerous Goods can\'t be AirMailed, and not all Couriers will take them.</br>Setting this to \'Yes\' prevents these methods from being supplied if this product as added to the customers cart..') ;\n";
							array_splice($rdata, $ln, 1, (array)$data); // splice
							
						} else {
							if ((strstr($line, "define('TEXT_PRODUCTS_WEIGHT',")) && ($doAll == 1)) {
								//     echo "<br>ALL 1"   ;
								$data = $weightdefine;
								$data .= "define('TEXT_PRODUCTS_HEIGHT',  'Product Height (cm):') ;\n";
								$data .= "define('TEXT_PRODUCTS_WIDTH',   'Product Width  (cm):') ;\n";
								$data .= "define('TEXT_PRODUCTS_LENGTH',  'Product Length (cm):') ;\n";
								$data .= "define('TEXT_PRODUCTS_DG',  'Dangerous Goods?') ;\n";
								$data .= "define('TEXT_DANGEROUS_GOODS_EDIT',  '</br>Dangerous Goods can\'t be AirMailed, and not all Couriers will take them.</br>Setting this to \'Yes\' prevents these methods from being supplied if this product as added to the customers cart..') ;\n";
								array_splice($rdata, $ln, 1, (array)$data); // splice
							}
						}
						$ln++;
					}
				}  // premodded
				
				if ($this->_writeTempFile($Ozfiles[0][0], $Ozfiles[0][1], $Ozfiles[0][2], $rdata)) {
					$errors++;
				}
			} else {
				$messageStack->add_session('FATAL ERROR: Unable to READ ' . $file, 'error');
				$errors++;
			} //end modify admin/includes/languages/english/product.php
			
			/////////////////////////
			// modify admin/includes/modules/update_product.php
			$file = $Ozfiles[1][0] . $Ozfiles[1][1] . $Ozfiles[1][2];
			$data = null;
			$doAll = 0;
			
			$rdata = file($file);
			if ($rdata) {
				if (count(preg_grep("/dangerous_goods/", $rdata)) <= 0) {
					//      echo "<br>DG Needed 2 ". $file ;
					if (!preg_grep("/products_length/", $rdata)) {
						$doAll = 1;
					} // echo "<br>DIMS NEEDED 2 ". $file ;
					
					
					$ln = 0;
					foreach ($rdata as $line) {
						$ln++;
						if (strstr(
							$line,
							"\$products_length = (!zen_not_null(\$tmp_value) || \$tmp_value=='' || \$tmp_value == 0) ? 0 : \$tmp_value;"
						)) {  //  Already has dimensions

							//     echo "<br>DG ONLY 2a"
							//   $data = "    \$dangerous_goods = ((\$_POST['dangerous_goods']) == 1) ? 1:0;\n";
							
							$data = "  \$tmp_value = zen_db_prepare_input(\$_POST['dangerous_goods']);\n\$dangerous_goods = (!zen_not_null(\$tmp_value) || \$tmp_value=='' || \$tmp_value == 0) ? 0 : 1;\n";
							
							
							array_splice($rdata, $ln, 0, (array)$data);
							$ln++;
						} else {
							if ((strstr(
									$line,
									"\$products_weight = (!zen_not_null(\$tmp_value) || \$tmp_value=='' || \$tmp_value == 0) ? 0 : \$tmp_value;"
								)) && ($doAll == 1)) {
								//     echo "<br>ALL 2a"   ;
								//  Only takes place up until 1.5.5 After that we use function covertToFloat() to convert.
								$data = "    \$tmp_value = zen_db_prepare_input(\$_POST['products_height']);\n";
								$data .= "    \$products_height = (!zen_not_null(\$tmp_value) || \$tmp_value=='' || \$tmp_value == 0) ? 0 : \$tmp_value;\n";
								
								$data .= "    \$tmp_value = zen_db_prepare_input(\$_POST['products_width']);\n";
								$data .= "    \$products_width = (!zen_not_null(\$tmp_value) || \$tmp_value=='' || \$tmp_value == 0) ? 0 : \$tmp_value;\n";
								
								$data .= "    \$tmp_value = zen_db_prepare_input(\$_POST['products_length']);\n";
								$data .= "    \$products_length = (!zen_not_null(\$tmp_value) || \$tmp_value=='' || \$tmp_value == 0) ? 0 : \$tmp_value;\n";
								//    $data .= "    \$dangerous_goods = ((\$_POST['dangerous_goods']) == 1) ? 1:0;\n";
								
								$data .= "    \$tmp_value = zen_db_prepare_input(\$_POST['dangerous_goods']);\n    \$dangerous_goods = (!zen_not_null(\$tmp_value) || \$tmp_value=='' || \$tmp_value == 0) ? 0 : 1;\n";
								
								array_splice($rdata, $ln, 0, (array)$data);
								$ln++;
								//  echo $data ; die ;
							}
						}
						
						if (strstr($line, "'products_length' => \$products_length,")) {
							//  Already has dimensions V1.5.5
							//              echo "DG ONLY 2b" ;
							$data = "                             'dangerous_goods' => \$dangerous_goods,\n";
							
							array_splice($rdata, $ln, 0, (array)$data);
							$ln++;
						} elseif (strstr($line, "'products_length' => convertToFloat(\$_POST['products_length']),")) {
							//  Already has dimensions V1.5.6
							$data = "    'dangerous_goods' => convertToFloat(\$_POST['dangerous_goods']),\n";
							
							array_splice($rdata, $ln, 0, (array)$data);
							$ln++;
							
						} elseif ((strstr($line, "'products_weight' => \$products_weight,")) && ($doAll == 1)) {
							// echo "<br>ALL 2b"   ;
							$data = "                            'products_height' => \$products_height,\n";
							$data .= "                            'products_width'  => \$products_width,\n";
							$data .= "                            'products_length' => \$products_length,\n";
							$data .= "                            'dangerous_goods' => \$dangerous_goods,\n";
							
							array_splice($rdata, $ln, 0, (array)$data);
							$ln++;
						} elseif ((strstr($line, "'products_weight' => convertToFloat(\$_POST['products_weight']),")) && ($doAll == 1)) {
							$data = "    'products_height' => convertToFloat(\$_POST['products_height']),\n";
							$data .= "    'products_width'  => convertToFloat(\$_POST['products_width']),\n";
							$data .= "    'products_length' => convertToFloat(\$_POST['products_length']),\n";
							$data .= "    'dangerous_goods' => convertToFloat(\$_POST['dangerous_goods']),\n";
							
							array_splice($rdata, $ln, 0, (array)$data);
							$ln++;
						}
					}  // next line
				}   // premodded
				if ($this->_writeTempFile($Ozfiles[1][0], $Ozfiles[1][1], $Ozfiles[1][2], $rdata)) {
					$errors++;
				}
			} else {
				$messageStack->add_session('FATAL ERROR: Unable to READ ' . $file, 'error');
				$errors++;
			} // end modify admin/includes/modules/update_product.php


			// die ;
			// modify admin/includes/modules/product/collect_info.php
			$file = $Ozfiles[2][0] . $Ozfiles[2][1] . $Ozfiles[2][2];
			$data = null;
			$doAll = 0;
			$rdata = file($file);
			if ($rdata) {
				if (count(preg_grep("/TEXT_PRODUCTS_DG/", $rdata)) <= 0) {
					//         echo "<br>DG Needed 3".$file ;
					if (!preg_grep("/products_length/", $rdata)) {
						$doAll = 1; // echo "<br>DIMS NEEDED 3".$file ;
					}
					$ln = 0;
					
					foreach ($rdata as $line) {
						$ln++;
						if (strstr($line, "'products_length' => '',")) {
							//                      echo "<br>DG ONLY 3a" ;
							$data = "                       'dangerous_goods' => '',\n";
							array_splice($rdata, $ln, 0, (array)$data);
							$ln++;
						} else {
							if ((strstr($line, "'products_weight' => '',")) ||
								(strstr($line, "'products_weight' => '0',")) && ($doAll == 1)) {
								//            echo "<br>ALL 3a"   ;
								$data = "  'products_height' => '0',\n";
								$data .= "  'products_width'  => '0',\n";
								$data .= "  'products_length' => '0',\n";
								$data .= "  'dangerous_goods' => '0',\n";
								
								array_splice($rdata, $ln, 0, (array)$data);
								$ln++;
							}
						}
						
						if (strstr($line, "p.products_length,")) {
							//               echo "<br>DG ONLY 3b" ;
							$data = "                               p.dangerous_goods,\n";
							array_splice($rdata, $ln, 0, (array)$data);
							$ln++;
						} else {
							if ((strstr($line, "p.products_weight,")) && ($doAll == 1)) {
								// echo "<br>ALL 3b"   ;
								$data = "                                      p.products_height,\n";
								$data .= "                                      p.products_width,\n";
								$data .= "                                      p.products_length,\n";
								$data .= "                                      p.dangerous_goods,\n";
								
								array_splice($rdata, $ln, 0, (array)$data);
								$ln++;
							}
						}
						
						if (strstr($line, "default: \$in_product_is_call = false; \$out_product_is_call = true;")) {
							//   echo "<br>DG ONLY 3c" ;    die ;
							$data = "    }\n// Product is Dangerous\n";
							$data .= "    if (!isset(\$pInfo->dangerous_goods)) \$pInfo->dangerous_goods = '0';\n";
							$data .= "    switch (\$pInfo->dangerous_goods) {\n";
							$data .= "      case '0': \$in_dangerous_goods = false; \$out_dangerous_goods = true; break;\n";
							$data .= "      case '1': \$in_dangerous_goods = true; \$out_dangerous_goods = false; break;\n";
							$data .= "      default: \$in_dangerous_goods = false; \$out_dangerous_goods = true;\n";
							
							array_splice($rdata, $ln, 0, (array)$data);
							$ln++;
						}
						
						if (strstr(
							$line,"<td class=\"main\"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_input_field('products_height', \$pInfo->products_height); ?></td>"
						)) {
							//            echo "<br>DG ONLY 3d" ;
							$data = "</tr> <tr> <td class=\"main\"><?php echo TEXT_PRODUCTS_DG; ?></td>\n";
							$data .= " <td class=\"main\"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_radio_field('dangerous_goods', '1', (\$in_dangerous_goods==1)) . '&nbsp;' . TEXT_YES . '&nbsp;&nbsp;' . zen_draw_radio_field('dangerous_goods', '0', (\$in_dangerous_goods==0)) . '&nbsp;' . TEXT_NO . ' ' . (\$pInfo->dangerous_goods == 1 ? '<span class=\"errorText\">' . TEXT_DANGEROUS_GOODS_EDIT . '</span>' : ''); ?></td>\n";
							
							array_splice($rdata, $ln, 0, (array)$data);
							$ln++;
						} elseif ((strstr(
							$line,"<td class=\"main\"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_input_field('products_weight', \$pInfo->products_weight); ?></td>"
							)) && ($doAll == 1)) {
							//    echo "<br>ALL 3c"   ;
							// bugfix 4.2.3
							// Was showing three commas ... near top of edit product screen immediately above "Product Master Category:" test
							// Fix, remove commas in 3 lines below: ie "</td>,\n"  to  "</td>\n"
							
							$data = " </tr> <tr> <td class=\"main\"><?php echo TEXT_PRODUCTS_LENGTH; ?></td>\n";
							$data .= "  <td class=\"main\"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_input_field('products_length', \$pInfo->products_length); ?></td>\n";
							$data .= " </tr> <tr> <td class=\"main\"><?php echo TEXT_PRODUCTS_WIDTH; ?></td>\n";
							$data .= "  <td class=\"main\"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_input_field('products_width', \$pInfo->products_width); ?></td>\n";
							$data .= " </tr> <tr> <td class=\"main\"><?php echo TEXT_PRODUCTS_HEIGHT; ?></td>\n";
							$data .= "  <td class=\"main\"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_input_field('products_height', \$pInfo->products_height); ?></td>\n";
							$data .= " </tr> <tr> <td class=\"main\"><?php echo TEXT_PRODUCTS_DG; ?></td>\n";
							$data .= " <td class=\"main\"><?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_radio_field('dangerous_goods', '1', (\$in_dangerous_goods==1)) . '&nbsp;' . TEXT_YES . '&nbsp;&nbsp;' . zen_draw_radio_field('dangerous_goods', '0', (\$in_dangerous_goods==0)) . '&nbsp;' . TEXT_NO . ' ' . (\$pInfo->dangerous_goods == 1 ? '<span class=\"errorText\">' . TEXT_DANGEROUS_GOODS_EDIT . '</span>' : ''); ?></td>\n";
							
							array_splice($rdata, $ln, 0, (array)$data);
							$ln++;
							
						} elseif (strstr(
							$line, "<?php echo zen_draw_input_field('products_weight', \$pInfo->products_weight, 'class=\"form-control\"'); ?>")) {
							$data = "    </div>\n";
							$data .= "  </div>\n";
							$data .= "  <div class=\"form-group\">\n";
							$data .= "    <?php echo zen_draw_label(TEXT_PRODUCTS_LENGTH, 'products_length', 'class=\"col-sm-3 control-label\"'); ?>\n";
							$data .= "    <div class=\"col-sm-9 col-md-6\">\n";
							$data .= "      <?php echo zen_draw_input_field('products_length', \$pInfo->products_length, 'class=\"form-control\"'); ?>\n";
							$data .= "    </div>\n";
							$data .= "  </div>\n";
							$data .= "  <div class=\"form-group\">\n";
							$data .= "    <?php echo zen_draw_label(TEXT_PRODUCTS_WIDTH, 'products_width', 'class=\"col-sm-3 control-label\"'); ?>\n";
							$data .= "    <div class=\"col-sm-9 col-md-6\">\n";
							$data .= "      <?php echo zen_draw_input_field('products_width', \$pInfo->products_width, 'class=\"form-control\"'); ?>\n";
							$data .= "    </div>\n";
							$data .= "  </div>\n";
							$data .= "  <div class=\"form-group\">\n";
							$data .= "    <?php echo zen_draw_label(TEXT_PRODUCTS_HEIGHT, 'products_height', 'class=\"col-sm-3 control-label\"'); ?>\n";
							$data .= "    <div class=\"col-sm-9 col-md-6\">\n";
							$data .= "      <?php echo zen_draw_input_field('products_height', \$pInfo->products_height, 'class=\"form-control\"'); ?>\n";
							$data .= "    </div>\n";
							$data .= "  </div>\n";
							$data .= "  <div class=\"form-group\">\n";
							$data .= "    <?php echo zen_draw_label(TEXT_PRODUCTS_DG, 'dangerous_goods', 'class=\"col-sm-3 control-label\"'); ?>\n";
							$data .= "    <div class=\"col-sm-9 col-md-6\">\n";
							$data .= "      <label class=\"radio-inline\"><?php echo zen_draw_radio_field('dangerous_goods', '1', (\$pInfo->dangerous_goods == 1)) . TEXT_YES; ?></label>\n";
							$data .= "      <label class=\"radio-inline\"><?php echo zen_draw_radio_field('product_is_free', '0', (\$pInfo->dangerous_goods == 0)) . TEXT_NO; ?></label>\n";

							
							array_splice($rdata, $ln, 0, (array)$data);
							$ln++;
						}
						
					}  // next line
				} //  premodded
				
				if ($this->_writeTempFile($Ozfiles[2][0], $Ozfiles[2][1], $Ozfiles[2][2], $rdata)) {
					$errors++;
				}
			} else {
				$messageStack->add_session('FATAL ERROR: Unable to READ ' . $file, 'error');
				$errors++;
			}
			
			
			// modify includes/templates/CUSTOM/templates/tpl_modules_shipping_estimator.php if it exists, else
			// copy amd modify includes/templates/template_default/templates/tpl_modules_shipping_estimator.php
			
			$tpl_folder = DIR_FS_CATALOG . DIR_WS_TEMPLATES . $GLOBALS['template_dir'] . "/templates/";
			$file = $tpl_folder . $Ozfiles[3][2];
			
			$data = null;
			$rdata = file($file);
			if (!$rdata) {   // no CUSTOM file
				$file = $Ozfiles[3][0] . $Ozfiles[3][1] . $Ozfiles[3][2];  //  use default
			} else {
				$bak = $file . "_ozpRestore";
				$oldbakFile = $file . "_restore";
				if (file_exists($oldbakFile)) {
					rename($oldbakFile, $bak);
				} //  Update for new filename convention
				copy($file, $bak);
			}
			
			$data = null;
			$rdata = file($file);
			if ($rdata) {
				if (count(preg_grep("['Display']", $rdata)) <= 0) {
					$ln = -1;
					$lines_changed = 0;
					foreach ($rdata as $line) {
						$ln++;
						switch (true) {
							case (strstr($line, "<?php echo zen_draw_hidden_field('action', 'submit'); ?>")) :
								{
									$line .= "<?php echo \"<style type=\\\"text/css\\\">
#quotes {width: 100%;}
#quotes_ td {
border-top: 1px solid #000000;
border-bottom: 1px solid #000000;
}
.quotes_responsive_classic td {};
.seDisplayedAddressLabel {
	text-align: right;
	background-color: #A8A5A5;
	color: black;
}
.seDisplayedAddressInfo {
	text-align: center;
	font-weight: bold;
}
.rowEven {
	background-color: #FDFDFD;
}
.rowOdd {
	background-color: #EBEBEB;
}
</style>\"; ?>
";
									array_splice($rdata, $ln, 1, (array)$line);
									$lines_changed++;
								}
								break;
							
							case (strstr($line, "zen_get_country_list(")) :
								{
									$line = str_replace("update_zone(this.form)", "hideZip(this.form)", $line);
									array_splice($rdata, $ln, 1, (array)$line);
									$lines_changed++;
								}
								break;
							
							case (strstr($line, "<?php echo ENTRY_STATE; ?>")):
								{
									array_splice($rdata, $ln, 6);
									$lines_changed++;
									$ln -= 5;  // 6 - 1 because we are not replacing anything
								}
								break;
							
							case (strstr($line, "if(CART_SHIPPING_METHOD_ZIP_REQUIRED == ")) :
								{
									$line = "echo \"<script type=\\\"text/javascript\\\" src=\\\"https://svr0.ozpost.net/ozpost.js\\\"></script>\" ;
if (\$order->delivery['country']['iso_code_2'] != 'AU') {
        \$zip_code = '' ; \$order->delivery['suburb']  = '' ; \$d1 = \"none\" ;
   } else { \$d1 = \"inline\" ;}

echo \"<div id=\\\"isHidZip\\\" style=\\\"display: \$d1\\\">\";

echo \"<label class=\\\"inputLabel\\\">\".ENTRY_POST_CODE.\"</label>\" ;
echo '<input type=\"text\" name=\"zip_code\" value=\"'.\$zip_code.'\" size=\"4\" id=\"zip_code\" onmouseout=\"upDateSuburb(this.form)\" />';

 if(isset(\$_POST['destSuburb'])) \$order->delivery['suburb'] = \$_POST['destSuburb'] ;

 if ( \$order->delivery['suburb'] != \"\" ) {
                \$Dsub = \$order->delivery['suburb'] ;
                    } elseif ( \$order->delivery['city'] != \"\" )  { \$Dsub = \$order->delivery['city'] ;
        }

\$d = (\$Dsub && (\$Dsub != \" \")) ? \"inline\":\"none\" ;
echo \"<div id=\\\"isHid\\\" style=\\\"display: \$d\\\">  <input type=\\\"text\\\"  name=\\\"destSuburb\\\" value=\\\"\$Dsub\\\" size=\\\"25\\\" id=\\\"destSuburb\\\" />
</div><div id=\\\"suburb_list\\\"></div></div>\" ;
";
									
									array_splice($rdata, $ln, 5, (array)$line);
									$lines_changed++;
									$ln -= 5;
								}
								break;
							
							case (strstr($line, "span class=\"seDisplayedAddressInfo")):
								{
									$line = "
<?php
echo '<span class=\"seDisplayedAddressInfo\" id=\"seDisplayedAddressInfo\" >' ;
if(isset(\$_SESSION['parcelweight'])) echo \"(\".\$_SESSION['parcelweight']. \" kg) \" ;
echo  MODULE_SHIPPING_OZPOST_ORIGIN_SUBURB . \" \" . MODULE_SHIPPING_OZPOST_ORIGIN_ZIP . \" to \" ;
if (\$order->delivery['country']['iso_code_2'] == 'AU') {
  echo \$order->delivery['suburb'] . zen_get_zone_name(\$selected_country, \$state_zone_id, '') . (\$selectedState != '' ? ' ' . \$selectedState : '') . ' ' . \$order->delivery['postcode'] . ' ' . zen_get_country_name(\$order->delivery['country_id']) . '</span>' ;
} else {
  echo  zen_get_country_name(\$order->delivery['country_id']) . '</span>'; }
?>";
									
									array_splice($rdata, $ln, 1, (array)$line);
									$lines_changed++;
								}
								
								break;
							
							case (strstr($line, "BUTTON_IMAGE_UPDATE")) :
								{ // delete update button
									$line = str_replace(
										"buttonRow forward\"",
										"buttonRow forward\" id=\"updateBtn\" style=\"visibility: visible\"",
										$line
									);
									array_splice($rdata, $ln, 1, (array)$line);
									$lines_changed++;
								}
								break;
							
							case (strstr($line,"<table width=\"100%\" border=\"1\" cellpadding=\"2\" cellspacing =\"2\">"	)) :
							case (strstr($line,"<table id=\"seQuoteResults\">")) :
								{
									$line = "<table id=\"quotes\" class=\"quotes_" . $GLOBALS['template_dir'] . "\">";
									array_splice($rdata, $ln, 1, (array)$line);
									$lines_changed++;
								}
								break;
							
							case (strstr(
								$line,
								"<td class=\"bold\"><?php echo \$quotes[\$i]['module']; ?>&nbsp;(<?php echo \$quotes[\$i]['methods'][0]['title']; ?>)</td>"
							)) :
							case (strstr(
								$line,
								"<td class=\"bold\"><?php echo \$quotes[\$i]['module']; ?>&nbsp;<?php echo \$quotes[\$i]['methods'][0]['title']; ?></td>"
							)) :
								{
									$line = "<td class=\"bold\"><?php echo \$quotes[\$i]['module']; ?>&nbsp;<?php echo  (\$quotes[\$i]['methods'][0]['Display'] != '') ? \$quotes[\$i]['methods'][0]['Display'] : \$quotes[\$i]['methods'][0]['title'] ; ?></td>\n";
									array_splice($rdata, $ln, 1, (array)$line);
									$lines_changed++;
								}
								break;
							
							case (strstr(
								$line,
								"<td><?php echo \$quotes[\$i]['module']; ?>&nbsp;(<?php echo \$quotes[\$i]['methods'][0]['title']; ?>)</td>"
							)) :
							case (strstr(
								$line,
								"<td><?php echo \$quotes[\$i]['module']; ?>&nbsp;<?php echo \$quotes[\$i]['methods'][0]['title']; ?></td>"
							)) :
								{
									$line = "<td><?php echo \$quotes[\$i]['module']; ?>&nbsp;<?php echo  (\$quotes[\$i]['methods'][0]['Display'] != '') ? \$quotes[\$i]['methods'][0]['Display'] : \$quotes[\$i]['methods'][0]['title'] ; ?></td>\n";
									array_splice($rdata, $ln, 1, (array)$line);
									$lines_changed++;
								}
								break;
							
							
							case (strstr($line, "<tr class=\"<?php echo \$extra; ?>\">")) :
								{
									$line = "<tr class=\"<?php echo (\$j & 1) ? 'rowOdd' : 'rowEven' ; ?>\">";
									array_splice($rdata, $ln, 1, (array)$line);
									$lines_changed++;
								}
								break;
							
							
							case (strstr(
								$line,
								"<td class=\"bold\"><?php echo \$quotes[\$i]['module']; ?>&nbsp;(<?php echo \$quotes[\$i]['methods'][\$j]['title']; ?>)</td>"
							)) :
								{
									$line = "<td class=\"bold\"><?php echo \$quotes[\$i]['module']; ?>&nbsp;<?php echo  (\$quotes[\$i]['methods'][\$j]['Display'] != '') ? \$quotes[\$i]['methods'][\$j]['Display'] : \$quotes[\$i]['methods'][\$j]['title'] ; ?></td>\n";
									array_splice($rdata, $ln, 1, (array)$line);
									$lines_changed++;
								}
								
								break;
							
							case (strstr(
								$line,
								"<td><?php echo \$quotes[\$i]['module']; ?>&nbsp;(<?php echo \$quotes[\$i]['methods'][\$j]['title']; ?>)</td>"
							)) :
								{
									$line = "<td><?php echo \$quotes[\$i]['module']; ?>&nbsp;<?php echo (\$quotes[\$i]['methods'][\$j]['Display'] != '') ? \$quotes[\$i]['methods'][\$j]['Display'] : \$quotes[\$i]['methods'][\$j]['title'] ; ?></td>\n";
									array_splice($rdata, $ln, 1, (array)$line);
									$lines_changed++;
								}
								break;
						} // end switch
					} // end foreach
					
					if ($lines_changed != 12) {
						$messageStack->add_session(
							'Possible error updating the template file. If you experience problems please replace <br>' .DIR_FS_CATALOG . DIR_WS_TEMPLATES . $GLOBALS['template_dir'] . '/templates/' . $Ozfiles[3][2] . '<br>with a zencart original and try again ',
							'error'
						);
					}
				} // premodded
				
				if ($this->_writeTempFile(
					DIR_FS_CATALOG,
					DIR_WS_TEMPLATES . $GLOBALS['template_dir'] . "/templates/",
					$Ozfiles[3][2],
					$rdata
				)) {
					$errors++;
				}
			} else {
				$messageStack->add_session('FATAL ERROR: Unable to READ ' . $file, 'error');
				$errors++;
			}
			// $this->_restorePerms($tpl_folder);
			
			
			//Modify the checkout page
			// modify includes/templates/CUSTOM/templates/tpl_checkout_payment_default.php if it exists, else
			// copy amd modify includes/templates/template_default/templates/tpl_checkout_payment_default.php
			$tpl_folder = DIR_FS_CATALOG . DIR_WS_TEMPLATES . $GLOBALS['template_dir'] . "/templates/";
			$file = $tpl_folder . $Ozfiles[4][2];
			
			$data = null;
			$rdata = file($file);
			if (!$rdata) {   // no CUSTOM file
				$file = $Ozfiles[4][0] . $Ozfiles[4][1] . $Ozfiles[4][2];  //  use default
			} else {
				$bak = $file . "_ozpRestore";
				$oldbakFile = $file . "_restore";
				if (file_exists($oldbakFile)) {
					rename($oldbakFile, $bak);
				} //  Update for new filename convention
				copy($file, $bak);
			}
			
			$data = null;
			$rdata = file($file);
			if ($rdata) {
				if (count(preg_grep("['Display']", $rdata)) <= 0) {
					$ln = -1;
					$lines_changed = 0;
					foreach ($rdata as $line) {
						$ln++;
						switch (true) {
							case (strstr($line, "<?php echo zen_draw_hidden_field('action', 'submit'); ?>")) :
								{
									$line .= "<?php echo \"<style type=\\\"text/css\\\">
#checkoutOrderTotals{
  clear: both;
  text-align: left;
}
</style>\"; ?>
";
									array_splice($rdata, $ln, 1, (array)$line);
									$lines_changed++;
								}
								break;
						}
					}
					if ($lines_changed != 1) {
						$messageStack->add_session(
							'Possible error updating the template file. If you experience problems please replace <br>' .DIR_FS_CATALOG . DIR_WS_TEMPLATES . $GLOBALS['template_dir'] . '/templates/' . $Ozfiles[4][2] . '<br>with a zencart original and try again ',
							'error'
						);
					}
				} // premodded
				
				if ($this->_writeTempFile(
					DIR_FS_CATALOG,
					DIR_WS_TEMPLATES . $GLOBALS['template_dir'] . "/templates/",
					$Ozfiles[4][2],
					$rdata
				)) {
					$errors++;
				}
			} else {
				$messageStack->add_session('FATAL ERROR: Unable to READ ' . $file, 'error');
				$errors++;
			}
			// $this->_restorePerms($tpl_folder);
			
			//  Change $weight_format in the language files
			$file = $Ozfiles[5][0] . $Ozfiles[5][1] . $Ozfiles[5][2]; // admin
			$rdata = file($file);
			if ($rdata) {
				$ln = 0;
				foreach ($rdata as $line) {
					if (strstr($line, "define('TEXT_PRODUCT_WEIGHT_UNIT',")) {   // admin
						$data = "define('TEXT_PRODUCT_WEIGHT_UNIT','$weight_factor');\n";
						array_splice($rdata, $ln, 1, (array)$data); // splice
					}
					$ln++;
				}
				if ($this->_writeTempFile($Ozfiles[5][0], $Ozfiles[5][1], $Ozfiles[5][2], $rdata)) {
					$errors++;
				}
			} else {
				$messageStack->add_session('FATAL ERROR: Unable to READ ' . $file, 'error');
			}
			//////////////////////////////////////////////
			$file = $Ozfiles[6][0] . $Ozfiles[6][1] . $Ozfiles[6][2]; // public
			
			$rdata = file($file);
			if ($rdata) {
				$ln = 0;
				foreach ($rdata as $line) {
					if (strstr($line, "define('TEXT_PRODUCT_WEIGHT_UNIT',")) { // public
						$data = "  define('TEXT_PRODUCT_WEIGHT_UNIT','$weight_factor');\n";
						array_splice($rdata, $ln, 1, (array)$data); // splice
					}
					if (strstr($line, "define('TEXT_SHIPPING_WEIGHT',")) {
						$data = "  define('TEXT_SHIPPING_WEIGHT','$weight_factor');\n";
						array_splice($rdata, $ln, 1, (array)$data); // splice
					}
					$ln++;
				}
				if ($this->_writeTempFile($Ozfiles[6][0], $Ozfiles[6][1], $Ozfiles[6][2], $rdata)) {
					$errors++;
				}
			} else {
				$messageStack->add_session('FATAL ERROR: Unable to READ ' . $file, 'error');
			}
			
	
			//Modify /admin/includes/header.php
			$file = $Ozfiles[7][0] . $Ozfiles[7][1] . $Ozfiles[7][2];
			$data = null;
			$rdata = file($file);
			if ($rdata) {
				if (count(preg_grep("/Added by Ozpost/", $rdata)) <= 0) {
					$ln = 0;
					
					foreach ($rdata as $line) {
						$ln++;
						if (strstr($line, "hide when other language dropdown is used")) {
							$data = "\n\n/////////////////\n// Added by Ozpost.  A Backup file was made called header.php_ozpRestore
if ( MODULE_SHIPPING_OZPOST_ALERTS == \"Yes\") {
\$ozpv = @file('https://svr1.ozpost.net/quotefor.php?flags=get_latest_client_version');
 if ((substr(\$ozpv[0], 0, 1) == \"V\") && (\$ozpv[0] > \"V\". MODULE_SHIPPING_OZPOST_DB_VERS )) {
    \$messageStack->add('An Ozpost shipping module update is available :'.substr(\$ozpv[0],0). \". Select Modules->Shipping to update.\" , 'alert');
   }
}

\$days =   MODULE_SHIPPING_OZPOST_EXPIRES ; \$msg = \"\" ;
\$text1 = \"Your Ozpost subscription\" ; \$text2 = \"</strong><a href='https://www.ozpost.net/my-account/'>(click to renew)</a>\" ;
             if (\$days > 0)   \$msg = \"\$text1 expires in <strong>\$days days \$text2.\" ;
             if(\$days < 0 )  \$msg = \"\$text1 expired <strong>\".\$days * -1 .\" days ago \$text2\" ;
if (\$msg && \$days <= 10) \$messageStack->add(\$msg , 'alert');\n/////// End of Ozpost additions ///////\n";
							array_splice($rdata, $ln, 0, (array)$data);
						}
					}  // next line
				} //  premodded
				
				if ($this->_writeTempFile($Ozfiles[7][0], $Ozfiles[7][1], $Ozfiles[7][2], $rdata)) {
					$errors++;
				}
			} else {
				$messageStack->add_session('FATAL ERROR: Unable to READ ' . $file, 'error');
				$errors++;
			}
			
			//////////////////////////////////////////////////////////////////////////////////////////////
			if ($errors > 0) {
				$messageStack->add_session('Unable to create temporary files.', 'error');
			} else {
				//  Move temp files to proper locations (if permitted)
				foreach ($Ozfiles as $file) {
					if (($file[2] != "") && ($file[2] != "ozpost.php") && ($file[1] != DIR_WS_TEMPLATES . $GLOBALS['template_dir'] . "/templates/")) {  // Skip folders and myself
						//               echo "Doing ";
						//            echo $file[0];
						//            echo $file[1];
						//               echo $file[2];
						//             echo "<br>";
						//
						
						if ($file[1] === DIR_WS_TEMPLATES . "template_default/templates/") {
							$cachefolder = $this->_get_cache_folder($file[0]);
							$cachefolder .= DIR_WS_TEMPLATES . "/" . $GLOBALS['template_dir'] . "/templates/";
							
							if (!copy($cachefolder . $file[2], $tpl_folder . $file[2])) {
								$messageStack->add_session(
									'Unable to copy <strong>' . $file[2] . '</strong> to ' . $tpl_folder . ' Relax folder permissions or copy manually.',
									'error'
								);
								$errors++;
							}
						} else {
							$File = $file[0] . $file[1] . $file[2];
							$this->_changePerms($file[0] . $file[1]);
							
							$cachefolder = $this->_get_cache_folder($file[0]);
							$cachefolder .= $file[1];
							$bakFile = $File . "_ozpRestore";
							$oldbakFile = $File . "_restore";
							if (file_exists($oldbakFile)) {
								rename($oldbakFile, $bakFile);
							} //  Update for new filename convention
							
							if (rename($File, $bakFile)) {
								if (!copy($cachefolder . $file[2], $File)) {
									$messageStack->add_session(
										'Unable to copy <strong>' . $file[2] . '</strong> Relax folder permissions or copy manually.',
										'error'
									);
									$errors++;
								}
							} else {
								$messageStack->add_session(
									'Unable to create <strong>' . $file[2] . '</strong> restoration file',
									'error'
								);
								$errors++;
							} // couldn't make backup //
							
							$this->_restorePerms($file[0] . $file[1]);
						} //  not tpl_
			//    }
			//    else {
			//                       echo "Skipping ";
			//            echo $file[0];
			//            echo $file[1];
			//               echo $file[2];
			//             echo "<br>";
					}// is folder, not a file
				} // next file
			}  // had error writing one or more files
			//echo "END" ; die ;
			
			///////////////////////////////
			
			if ($errors === 0) {
				if ($updavail) {
					$messageStack->add_session('Ozpost v' . $updavail . ' Upgrade successful.', 'success');
				} else {
					$messageStack->add_session(
						'Ozpost v' . $this->VERSION . ' Installation successful.<br>You will need to modify the settings and press UPDATE to complete.',
						'success'
					);
				}
				$db->Execute(
					"update " . TABLE_CONFIGURATION . " set configuration_value =  concat(configuration_value,\"ozpost.php\") where configuration_key = \"MODULE_SHIPPING_INSTALLED\""
				); // Register module to prevent unwanted 'no methods defined' error if this is the only shipping module installed
				
				$this->_changePerms(DIR_FS_SQL_CACHE);
				// RodG Delete
				$this->_rrmdir(DIR_FS_SQL_CACHE . "/ozpost/");
				$this->_restorePerms(DIR_FS_SQL_CACHE);
			} else {
				$messageStack->add_session('Installation FAILED', 'error');
				if (is_dir(DIR_FS_SQL_CACHE . "/ozpost/")) {
					$messageStack->add_session(' Work files saved in ' . DIR_FS_SQL_CACHE . "/ozpost/", 'success');
				} else {
					$messageStack->add_session(
						' Please ensure that <strong> ' . DIR_FS_SQL_CACHE . " </strong>is writable by the server process then uninstall reinstall to complete",
						'caution'
					);
				}
			}
		} else {  // didn't get past the starting gate - unable to write to /cache/ etc....//
			$messageStack->add_session(
				" <strong></br> " . $errors . '</strong> Folder or File Permissions issues have been detected. Installation cannot proceed until corrected</br>'
				. ' Folders need permissions of 0775 (ug=rwx) <strong>Fix these first</strong> , Files need permissions of 664 (ug=rw). ',
				'caution'
			);
			
			header('location: modules.php?set=shipping');
			exit (0);
		}
	}  //  End Install()


	//////////////////////////////////////////////////////////////////////
	/**
	 *
	 */
	public function remove()
	{
		global $db, $auto, $Ozfiles;  //  save for updates //
		$db->Execute(
			"delete from " . TABLE_CONFIGURATION . " where configuration_key like 'bakMODULE_SHIPPING_OZPOST_%' "
		); // get rid of old stuff laying around //
		
		if ((MODULE_SHIPPING_OZPOST_REMOVE_ACTION == "Upgrade") || ($auto == 1)) {   // make backup if needed  //
			$db->Execute(
				"update  " . TABLE_CONFIGURATION . " set configuration_key = concat('bak', configuration_key) where configuration_key like 'MODULE_SHIPPING_OZPOST_%' "
			);
			if (defined('TABLE_ADMIN_MENUS')) {
				$db->Execute("delete from " . TABLE_ADMIN_MENUS . " where menu_key like 'ClickNsend%' ");
			}
		}
		
		$db->Execute(
			"delete from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE_SHIPPING_OZPOST_%' "
		);  // Delete current settings
		
		
		foreach ($Ozfiles as $file) {
			$File = $file[0] . $file[1] . $file[2];
			
			$bakFile = $File . "_ozpRestore";
			$oldbakFile = $File . "_restore";
			$this->_changePerms($file[0] . $file[1]);
			if (file_exists($oldbakFile)) {
				rename($oldbakFile, $bakFile);
			} //  Update for new filename convention
			if (file_exists($bakFile)) {
				rename($bakFile, $File);
			}  //  restore original file
			$this->_restorePerms($file[0] . $file[1]);
			//   }
			
			if (strstr(
				$file[2],
				"tpl_modules_shipping_estimator.php"
			)) { // restore or delete custom tpl_modules_shipping_estimator.php
				// $te mplate = $GLOBALS['template_dir'];
				$folder = DIR_WS_TEMPLATES . $GLOBALS['template_dir'] . "/templates/";
				
				$file[1] = $folder;
				$File = $file[0] . $file[1] . $file[2];
				$this->_changePerms($file[0] . $file[1]);
				
				if (file_exists($oldbakFile)) {
					rename($oldbakFile, $bakFile);
				} //  Update for new filename convention
				if (file_exists($File . "_ozpRestore")) {
					rename($File . "_ozpRestore", $File);  //  restore original file
				} else {
					if (file_exists($File)) {
						unlink($File);
					}  //  We added it. We delete it.
				}
				$this->_restorePerms($file[0] . $file[1]);
			}
		} // next file
		
		$this->_changePerms(DIR_FS_SQL_CACHE);
		$this->_rrmdir(DIR_FS_SQL_CACHE . "/ozpost/");
		$this->_restorePerms(DIR_FS_SQL_CACHE);
		
		$db->Execute("DROP TABLE IF EXISTS " . TABLE_OZPOST_CACHE);
	}
	
	/**
	 * @param $dir
	 */
	private function _rrmdir($dir)
	{
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir . "/" . $object) == "dir") {
						$this->_rrmdir($dir . "/" . $object);
					} else {
						unlink($dir . "/" . $object);
					}
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}
	
	// FUNCTION _get_from_ozpostnet
	
	/**
	 * @param $request
	 * @param string $items
	 * @param string $controls
	 * @return bool|mixed|string
	 */
	private function _get_from_ozpostnet($request, $items = "", $controls = "")
	{
		global $messageStack;
		$domain = "ozpost.net";
		$error1 = "";
		$error2 = "";
		$error0 = "";
		$data = "";
		$file = "";
		$time_start = microtime(true);
		
		
		//  check Cache //
		$hash = md5($request . $controls . addslashes(serialize($items)));
		//unset($_SESSION['ozpostQuotes_'.$hash]) ;
		
		if (((isset($_SESSION['hash_' . $hash])) && (isset($_SESSION['ozpostQuotes_' . $hash])) && (isset($_SESSION['hashTime_' . $hash]))) && (($_SESSION['hash_' . $hash] === $hash) && (time(
					) - $_SESSION['hashTime_' . $hash] < 900))) {
		//  if ( $this->config->get('ozpost_DEBUG') == "Yes" )  $log->write(' **********  Using Session data  ******* ') ;
			return $_SESSION['ozpostQuotes_' . $hash];  // use stored data
		}


		//    $hash = md5($request . $controls . addslashes(serialize($items))) ;
		//
		//    $fn =  preg_split("/\//" , $request);
		//
		//    if (isset($fn[2]))  $file = $fn[2] ;
		//        if(!(preg_match('/[jpg][png][gif][inc]/',$file)))  {
		//        $data = $this->_fetchFromCache($hash) ;   //  skip cache check for images & V4x include file//
		//    }
		
		//  if(!$data) {
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt(
			$ch,
			CURLOPT_USERAGENT,
			"[" . PROJECT_VERSION_NAME . " " . PROJECT_VERSION_MAJOR . "." . PROJECT_VERSION_MINOR . " : " . $this->code . " v" . $this->VERSION . "] " . $_SERVER['SERVER_NAME']
		);        //     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		
		if (is_array($items)) {
			curl_setopt($ch, CURLOPT_POST, true);
			$vars = http_build_query(array('Items' => $items));
			$vars .= $controls;
			curl_setopt($ch, CURLOPT_POSTFIELDS, "$vars");
		}
		
		if (($_SERVER['REMOTE_HOST'] ?? ' ')=== "sue") { // Local debugging (won't work for anyone else)
			$svr = "https://sue";
			curl_setopt($ch, CURLOPT_URL, $svr . "/ozpost" . $request);
			$data = curl_exec($ch);
			$error1 = curl_error($ch);
			$commInfo = @curl_getinfo($ch);
			if ($commInfo['http_code'] == "404") {
				$error1 = "File  $file Not Found";
			}
			if (($error1 != "") || ($data == "")) {
				$data = '<error>' . $error1 . '</error>';
			}
		} else {
			curl_setopt($ch, CURLOPT_URL, "https://svr1." . $domain . $request);
			$data = curl_exec($ch);
			$error1 = curl_error($ch);
			$commInfo = curl_getinfo($ch);
			
			if ($data == "Access denied") {
				$error1 = $data;
				$messageStack->add_session(
					'svr1.ozpost,net : ' . $error1 . ". Please report this error to <strong>support@ozpost.net</strong>",
					'error'
				);
			}
			
			if ($commInfo['http_code'] == "404") {
				$error1 = "File  $file Not Found";
			}
			$svr = 1;
			if (($error1) || (!$data) || $commInfo['http_code'] != "200") {  // exit ;
				if (preg_match('/[jpg][png][gif]/', $file)) {    //  only use the primary server for icons/images
					if ((MODULE_SHIPPING_OZPOST_DEBUG == "Yes") && ($_GET['set'] != "shipping")) {
						//    echo $error1."<br>" ;
						return "<error>";
					} else {
						return "<error>";
					}
				}
				
				curl_setopt($ch, CURLOPT_URL, "https://svr2." . $domain . $request);
				$data = curl_exec($ch);
				$error2 = curl_error($ch);
				$commInfo = curl_getinfo($ch);
				if ($data == "Access denied") {
					$error2 = $data;
					$messageStack->add_session(
						'svr2.ozpost,net : ' . $error2 . ". Please report this error to <strong>support@ozpost.net</strong>",
						'error'
					);
				}
				if ($commInfo['http_code'] == "404") {
					$error2 = "\"$file\" Not Found";
				}
				$svr = 2;
				
				if (($error2) || (!$data) || $commInfo['http_code'] != "200") {
					curl_setopt($ch, CURLOPT_URL, "https://svr0." . $domain . $request);
					$data = curl_exec($ch);
					$error0 = curl_error($ch);
					$commInfo = curl_getinfo($ch);
					
					if ($data == "Access denied") {
						$error0 = $data;
						$messageStack->add_session(
							'svr0.ozpost,net : ' . $error0 . ". Please report this error to <strong>support@ozpost.net</strong>",
							'error'
						);
					}
					if ($commInfo['http_code'] == "404") {
						$error0 = "File  $file Not Found";
					}
					$svr = 0;
					if (($error0) || (!$data) || $commInfo['http_code'] != "200") {
						$data = '<error>Server#1 Error: ' . $error1 . '</br>Server#2 Error: ' . $error2 . '</br>Server#0 Error: ' . $error0 . '</error>';
					} // server 0 ok
				}  // server 2 ok
			}    // server 1 - ok
		} // Not local
		if ((MODULE_SHIPPING_OZPOST_DEBUG == "Yes") && (isset($_GET['set']) != "shipping")) {   // BMH 
			echo "<div>Server " . $svr . " response time " . round(microtime(true) - $time_start, 3) . "ms</div>";
		}
		
		curl_close($ch);
		//if((substr($data, 0, 7)) != "<error>") {
		// if ((substr($data, 0, 19)) == "<?xml version='1.0'") $this->_saveToCache($hash, $data) ; // only cache valid XML
		// }
		//} else { // Got from cache //
		//        if (( MODULE_SHIPPING_OZPOST_DEBUG == "Yes") && ($_GET['set'] != "shipping"))  {   echo "<div>Retrieved from local DB Cache</div>" ; }
		//      } //  end Got from cache //
		///  cache the results //
		$_SESSION['ozpostQuotes_' . $hash] = $data;
		$_SESSION['hash_' . $hash] = $hash;
		$_SESSION['hashTime_' . $hash] = time();
		//////////////////////
		return $data;
	}   // ! FUNCTION _get_from_ozpostnet
	
	
	// class methods
	/**
	 * @param $select_array
	 * @param $key_value
	 * @param string $key
	 * @return mixed
	 */
	public static function ozp_cfg_select_drop_down($select_array, $key_value, $key = '')
	{ // can't use zen original because it expects $key_value to be an interger, we need a string
		$name = ((zen_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');
		return zen_draw_pull_down_menu($name, $select_array, $key_value);
	}
	
	/**
	 * @param $select_array
	 * @param $key_value
	 * @param string $key
	 * @return string
	 */
	public static function ozp_cfg_select_option($select_array, $key_value, $key = '') 
	// BMH
	{ //make inline instead of down //
		
		$string = '';
		for ($i = 0, $n = sizeof($select_array); $i < $n; $i++) {
			$name = ((zen_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');
			
			$string .= '<input type="radio" name="' . $name . '" value="' . $select_array[$i] . '"';
			
			if ($key_value == $select_array[$i]) {
				$string .= ' CHECKED';
			}
			
			$string .= ' id="' . strtolower($select_array[$i] . '-' . $name) . '"> ' . '<label for="' . strtolower(
					$select_array[$i] . '-' . $name
				) . '" class="inputSelect">' . $select_array[$i] . '</label>&nbsp;&nbsp;';
		}
		return $string;
	}

	//  private function _getskipvalue($item) {
	//       $value =  $item['quantity'] * $item['price'] ;
	//  if ($item['tax_class_id'] > 0)   $value +=  ( ($item['quantity'] * $item['price']) / 10 ) ;
	//  return $value ;
	//  }
	/////////////////////////////////////////////////
	
	/**
	 *
	 */
	private function _servertest()
	{
		error_reporting(E_ALL);
		
		$url[] = "svr0.ozpost.net";
		$url[] = "svr1.ozpost.net";
		$url[] = "svr2.ozpost.net";

		//  $url[] = "https://svrX.ozpost.net"; //  used to test that error reports are correctly shown.
		$data = "/quotefor.php?flags=get_latest_client_version"; //  test string. We check for a valid return, not just any server response.
		
		$i = 0;
		while ($i < sizeof($url)) {
			$ip = gethostbyname($url[$i]);
			$ip = ($ip === $url[$i]) ? "" : "@" . $ip;
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://" . $url[$i] . $data);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 2);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Ozpost - Server Test');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			//  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5');
			
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			
			$edata = curl_exec($ch);
			$errtext = curl_error($ch);
			$errnum = curl_errno($ch);
			$commInfo = curl_getinfo($ch);
			
			if ($edata === "Access denied") {
				$errtext = "<strong>" . $edata . ".</strong> Please report this error to <strong>support@ozpost.net  ";
			}
			
			curl_close($ch);
			echo "<div style='color:black;display: inline-block;font-weight:bold;'>" . $url[$i] . $ip . "></div>";
			
			if (($commInfo['http_code'] == 200) && ($errnum == 0)) {
				$commInfo['connect_time'] = (($commInfo['connect_time'] * 1000) >= 1) ? intval(
					$commInfo['connect_time'] * 1000
				) : number_format(($commInfo['connect_time'] * 1000), 3);
				$commInfo['namelookup_time'] = (($commInfo['namelookup_time'] * 1000) >= 1) ? intval(
					$commInfo['namelookup_time'] * 1000
				) : number_format(($commInfo['namelookup_time'] * 1000), 3);
				$commInfo['total_time'] = (($commInfo['total_time'] * 1000) >= 1) ? intval(
					$commInfo['total_time'] * 1000
				) : number_format(($commInfo['total_time'] * 1000), 3);
				
				
				echo "<div style='color:green;display: inline-block;white-space: nowrap;'>";
				echo " Connect Time     : " . $commInfo['connect_time'] . "ms >";
				if ($commInfo['connect_time'] < 100) {
					echo "&nbsp;";
				}
				
				echo " Name lookup Time : " . $commInfo['namelookup_time'] . "ms >";
				if ($commInfo['namelookup_time'] > 1) {
					echo "&nbsp;&nbsp;&nbsp;";
				}
				echo " Total Time       : " . $commInfo['total_time'] . "ms >";
				if ($commInfo['total_time'] < 100) {
					echo "&nbsp;";
				}
				echo "<div style='color:black;display: inline-block;font-weight:bold;'> ";
				
				if (($commInfo['total_time']) > 1000) {
					echo " Poor " . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif');
				} else {
					if (($commInfo['total_time']) > 700) {
						echo " Sluggish " . zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif');
					} else {
						if (($commInfo['total_time']) < 300) {
							echo " Excellent " . zen_image(DIR_WS_IMAGES . 'icon_status_green.gif');
						} else {
							if (($commInfo['total_time']) <= 700) {
								echo " Good " . zen_image(DIR_WS_IMAGES . 'icon_status_green.gif');
							}
						}
					}
				}
				
				echo "</div></div>";
			} else {
				echo "<div style='color:red;display:inline-block;white-space: nowrap;'>" . $errtext . " > FAIL </strong>" . zen_image(
						DIR_WS_IMAGES . 'icon_red_off.gif'
					) . "</div>";
			}
			echo "</br>";
			$i++;
		}
		error_reporting(0);
	}
	
	/**
	 * @return string[]
	 */
	public function keys()
	{
		return array(
			'MODULE_SHIPPING_OZPOST_STATUS',
			'MODULE_SHIPPING_OZPOST_NOOS',
			'MODULE_SHIPPING_OZPOST_ORIGIN_ZIP',
			'MODULE_SHIPPING_OZPOST_ORIGIN_SUBURB',
			'MODULE_SHIPPING_OZPOST_EMAIL',
			'MODULE_SHIPPING_OZPOST_ALERTS',
			'MODULE_SHIPPING_OZPOST_TYPE_LETTERS',
			'MODULE_SHIPPING_OZPOST_LET_HANDLING',
			'MODULE_SHIPPING_OZPOST_LETP_HANDLING',
			
			'MODULE_SHIPPING_OZPOST_HIDE_PARCELD',
			'MODULE_SHIPPING_OZPOST_HIDE_PARCELO',
			
			'MODULE_SHIPPING_OZPOST_TYPE_APS',
			'MODULE_SHIPPING_OZPOST_AP_DISCOUNT1R',
			'MODULE_SHIPPING_OZPOST_AP_DISCOUNT2R',
			'MODULE_SHIPPING_OZPOST_AP_DISCOUNT3R',
			'MODULE_SHIPPING_OZPOST_AP_DISCOUNT1E',
			'MODULE_SHIPPING_OZPOST_AP_DISCOUNT2E',
			'MODULE_SHIPPING_OZPOST_AP_DISCOUNT3E',
			'MODULE_SHIPPING_OZPOST_PPS_HANDLING',
			'MODULE_SHIPPING_OZPOST_PPSE_HANDLING',
			
			
			'MODULE_SHIPPING_OZPOST_HIDE_PARCEL2',
			//'MODULE_SHIPPING_OZPOST_TYPE_CNS',
			//'MODULE_SHIPPING_OZPOST_CNSS_HANDLING',
			//'MODULE_SHIPPING_OZPOST_CNSB_HANDLING',
			
			'MODULE_SHIPPING_OZPOST_TYPE_APP',
			'MODULE_SHIPPING_OZPOST_RPP_HANDLING',
			'MODULE_SHIPPING_OZPOST_COD_HANDLING',
			
			
			'MODULE_SHIPPING_OZPOST_HIDE_COURIER',
			
			'MODULE_SHIPPING_OZPOST_TYPE_APOS',
			'MODULE_SHIPPING_OZPOST_INT_HANDLING',
			'MODULE_SHIPPING_OZPOST_RI_HANDLING',
			'MODULE_SHIPPING_OZPOST_EXP_HANDLING',
			
			
			'MODULE_SHIPPING_OZPOST_TYPE_FW',
			'MODULE_SHIPPING_OZPOST_FWF',
			'MODULE_SHIPPING_OZPOST_FW_FREQ',
			'MODULE_SHIPPING_OZPOST_FW_FREQ_SPBW',
			'MODULE_SHIPPING_OZPOST_FW_BOXS',
			'MODULE_SHIPPING_OZPOST_FW_BOXM',
			'MODULE_SHIPPING_OZPOST_FW_BOXL',
			'MODULE_SHIPPING_OZPOST_FW_SAT0',
			'MODULE_SHIPPING_OZPOST_FW_SAT1',
			'MODULE_SHIPPING_OZPOST_FW_SAT2',
			'MODULE_SHIPPING_OZPOST_FW_SAT3',
			'MODULE_SHIPPING_OZPOST_FW_SAT4',
			'MODULE_SHIPPING_OZPOST_FWL_HANDLING',
			'MODULE_SHIPPING_OZPOST_FWB_HANDLING',
			'MODULE_SHIPPING_OZPOST_FWS_HANDLING',
			
			
			'MODULE_SHIPPING_OZPOST_TYPE_TNT',
			'MODULE_SHIPPING_OZPOST_TNT_ACCT',
			'MODULE_SHIPPING_OZPOST_TNT_USER',
			'MODULE_SHIPPING_OZPOST_TNT_PSWD',
			'MODULE_SHIPPING_OZPOST_TNT_HANDLING',
			'MODULE_SHIPPING_OZPOST_TNT_INT_HANDLING',
			
			'MODULE_SHIPPING_OZPOST_TYPE_STA',
			'MODULE_SHIPPING_OZPOST_STA_ACCT',
			'MODULE_SHIPPING_OZPOST_STA_USER',
			'MODULE_SHIPPING_OZPOST_STA_PSWD',
			'MODULE_SHIPPING_OZPOST_STA_KEY',
			'MODULE_SHIPPING_OZPOST_STA_HANDLING',
			'MODULE_SHIPPING_OZPOST_STAS_HANDLING',
			
			'MODULE_SHIPPING_OZPOST_TYPE_CPL',
			
			'MODULE_SHIPPING_OZPOST_CPL_METRO',
			'MODULE_SHIPPING_OZPOST_CPL_EZY',
			'MODULE_SHIPPING_OZPOST_CPL_SAT0',
			'MODULE_SHIPPING_OZPOST_CPL_SAT1',
			'MODULE_SHIPPING_OZPOST_CPL_SAT2',
			'MODULE_SHIPPING_OZPOST_CPL_SAT3',
			
			'MODULE_SHIPPING_OZPOST_CPL_ACCT',
			'MODULE_SHIPPING_OZPOST_CPL_KEY',
			'MODULE_SHIPPING_OZPOST_CPL_HANDLING',
			'MODULE_SHIPPING_OZPOST_CPLI_HANDLING',
			'MODULE_SHIPPING_OZPOST_CPLS_HANDLING',
			
			'MODULE_SHIPPING_OZPOST_TYPE_SMS',
			'MODULE_SHIPPING_OZPOST_SMS_EMAIL',
			'MODULE_SHIPPING_OZPOST_SMS_PASS',
			'MODULE_SHIPPING_OZPOST_SMS_TYPE',
			'MODULE_SHIPPING_OZPOST_SMS_HANDLING',
			
			'MODULE_SHIPPING_OZPOST_TYPE_TRD',
			'MODULE_SHIPPING_OZPOST_TRD_USER',
			'MODULE_SHIPPING_OZPOST_TRD_PSWD',
			'MODULE_SHIPPING_OZPOST_TRD_HANDLING',
			'MODULE_SHIPPING_OZPOST_TRD_INT_HANDLING',
			
			'MODULE_SHIPPING_OZPOST_TYPE_EGO',
			'MODULE_SHIPPING_OZPOST_EGO_HANDLING',
			
			'MODULE_SHIPPING_OZPOST_TYPE_SDL',
			'MODULE_SHIPPING_OZPOST_SDL_HANDLING',
			
			'MODULE_SHIPPING_OZPOST_TYPE_HX',
			'MODULE_SHIPPING_OZPOST_HX_USER',
			'MODULE_SHIPPING_OZPOST_HX_PSWD',
			'MODULE_SHIPPING_OZPOST_HX_CUST',
			'MODULE_SHIPPING_OZPOST_HX_FUELLEVY',
			'MODULE_SHIPPING_OZPOST_HX_HANDLING',
			
			'MODULE_SHIPPING_OZPOST_TYPE_SKP',
			'MODULE_SHIPPING_OZPOST_SKP_CUST',
			'MODULE_SHIPPING_OZPOST_SKP_HANDLING',
			
			
			'MODULE_SHIPPING_OZPOST_HW_SURCHARGE',
			'MODULE_SHIPPING_OZPOST_HW_LIMIT',
			
			'MODULE_SHIPPING_OZPOST_HIDE_HANDLING',
			
			'MODULE_SHIPPING_OZPOST_DIMS',
			'MODULE_SHIPPING_OZPOST_TARE',
			'MODULE_SHIPPING_OZPOST_DIMTARE',
			'MODULE_SHIPPING_OZPOST_RESTRAIN_DIMS',
			'MODULE_SHIPPING_OZPOST_WEIGHT_FORMAT',
			'MODULE_SHIPPING_OZPOST_ZERO_WEIGHT',
			'MODULE_SHIPPING_OZPOST_DEFW',
			'MODULE_SHIPPING_OZPOST_CORE_WEIGHT',
			
			'MODULE_SHIPPING_OZPOST_COST_ON_ERROR_TYPE',
			'MODULE_SHIPPING_OZPOST_STATIC_MODE',
			'MODULE_SHIPPING_OZPOST_COST_ON_ERROR',
			'MODULE_SHIPPING_OZPOST_TABLE_MODE',
			'MODULE_SHIPPING_OZPOST_COST_ON_ERROR2',
			
			'MODULE_SHIPPING_OZPOST_ICONSS',
			'MODULE_SHIPPING_OZPOST_ICONS',
			
			'MODULE_SHIPPING_OZPOST_MAILDAYS',
			'MODULE_SHIPPING_OZPOST_LEADTIME',
			'MODULE_SHIPPING_OZPOST_EF',
			'MODULE_SHIPPING_OZPOST_DEADLINE',
			
			'MODULE_SHIPPING_OZPOST_SORT_ORDER',
			'MODULE_SHIPPING_OZPOST_TAX_CLASS',
			'MODULE_SHIPPING_OZPOST_DEBUG',
			'MODULE_SHIPPING_OZPOST_SHOW_PARCEL',
			'MODULE_SHIPPING_OZPOST_MSG',
			'MODULE_SHIPPING_OZPOST_REMOVE_ACTION'
		);
	}  // end keys function
}  // end class     
