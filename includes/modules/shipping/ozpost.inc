<?php
/* 
 * Custom packing modifications go here - This file will NOT be modified during ozpost updates
 * 
 * Modify this file in any way you like for custom 'packing' (or other special treatment) 
 * 
 * This file is called *after* the "$shipme" array has been created - it will only contain valid data. The zero weight item processing has already been handled
 * default weight/dimensions have been applied and converted to gm and mm (if needed), and the virtual and/or free shipping items have been excluded from the array. 
 * 
 * The final quote will be based on the 'items' modified or entered here PLUS the contents of the original $shipme array, so if this override is used to modify 
 * item data then that item must be 'nullified' (as per the examples) to prevent a duplicate entry (the original plus the modified). This method means you don't need 
 * to create a full $shipme array in this override - Just skip anything that doesn't require any special treament.    
 *  
 * This file can be empty -  but if it exists it must NOT be world writable (for security reasons). Unless the ozpost 'debug' is enabled the module will 
 * attempt to change the permissions to read only (something to be waware of when editing this file. 
 * 
 * The *examples* provided here are all disabled by default via the relevent 'IF' statements.   
 * 
 * Valid output requires 1 or more item[]'s plus $parcelWeight, $parcelQty (qty total) & $orderValue eg:  
 * $parcelWeight = 1234 (gm) ; $parcelQty = 1 ; $orderValue = 99.99 ;
 * $items[] = array('Length' => $lengthmm, 'Width' => $widthmm, 'Height' => $heightmm,  'Weight' =>  $parcelWeight,  'Qty' => $parcelQty,  'Insurance' =>  $value ); 
 * 
 * By default tare weight/dimensions are calculated serverside (using the ozpost settings defaults. To override these set 
 *        $control_tare = "", $control_tare = "&tare_weight=WGT",  $control_tare = "&tare_dimensions=1,2,3" or  $control_tare = "&tare_weight=WGT&tare_dimensions=1,2,3" ; 
*/

//
//                             ///////////////     Working Examples  /////////////////
//
// This is the 'Use Core weight' override function -  It uses the default ZenCart weightings, number of boxes & weight per box (if applicable)
// You can delete this example if you don't want to use the original zencart shipping data (parcel splitting based on weight) 
// This is about as simple as it gets -  All 'ozpost' parcel handling is nullified. A single 'item' is sent to the ozpost servers.
// If multiple 'packages' are called for they will be combined by the ozpost servers unless the $NoPack variable is set to '1' 

if (MODULE_SHIPPING_OZPOST_CORE_WEIGHT === "Yes") {   // Only if enabled via admin setting
	global $shipping_weight, $shipping_num_boxes;
	$parcelWidth = $parcelLength = $parcelHeight = 0;
	
	$parcelWeight = (MODULE_SHIPPING_OZPOST_WEIGHT_FORMAT == "kilos") ? ($shipping_weight * 1000) : $shipping_weight;
	$parcelQty = ($shipping_num_boxes <= 0) ? 1 : $shipping_num_boxes;
	$orderValue = $order->info['total'] / $parcelQty;
	$control_tare = "";// disable ozpost defaults
	
	//   Get some dimensions -  Zen doesn't normally know about these, but no reason why we shouldn't use them for sizing purposes
	for ($index = 0; $index < count($shipme); $index++) {
		$itemWidth = $shipme[$index]['width'];
		$itemLength = $shipme[$index]['length'];
		$itemHeight = $shipme[$index]['height'] * $shipme[$index]['quantity']; // just do a simple stack here
		
		// set the maxwidth and length of parcel as needed
		if ($itemWidth > $parcelWidth) {
			$parcelWidth = $itemWidth;
		}
		if ($itemLength > $parcelLength) {
			$parcelLength = $itemLength;
		}
		$parcelHeight += $itemHeight;
	}
	
	$parcelHeight = $parcelHeight / $shipping_num_boxes;  // Leave the lengh/width alone, split the parcel by height (same was it was created)
	// Just a single 'item' is sent to the ozpost servers.
	$items[] = array(
		'Length' => $parcelLength,
		'Width' => $parcelWidth,
		'Height' => $parcelHeight,
		'Weight' => $parcelWeight,
		'Qty' => $parcelQty,
		'Insurance' => $orderValue
	);
	
	$shipme = null; // Stop further processing
	$NoPack = 1; // Treat the Qty as separate parcels (don't try to pack into a single parcel)
} ////////////

else {
	///////////////    Custom calcs by Lee (Living meridian store)
	//// This code will select a particular mailing tube size and then stuff it with paper charts up to preset limits
	//// The paper charts are all the same in dimensions and weight, the tube varies in diameter, length and weight
	//// All dimensions are in cm or kg
	//Code is smart enough to optimize tube size and qty for each line item in the order but not for the order as a whole
	
	// loop through $shipme items
	for ($index = 0; $index < count($shipme); $index++) {
		if (($shipme[$index]['category'] == "104") && (stristr($shipme[$index]['name'],"Chart"))) { // Only if category '104' and name contains 'chart'
			
			$control_tare = ""; // It is generally good to add tare to a parcel. I've disabled it here on the *assumption* that there will be no other items in the cart
			// If there are other items the ozpost servers will assume you will be packing these tubed items into the same box as other items so
			// disabling tare here isn't what it really wanted
			
			$tws = 204; // weight (gm) of a single small tube 73 x 8 x 8 cms
			$twm = 256; // weight (gm) of a single medium tube 75 x 10 x 10 cms < this is the original tube
			$twl = 672; // weight (gm) of a single large tube 75 x 20 x 20 cms - used instead of 5 large tubes
			$twx = 1070; // weight (gm) of a single maxi tube 97 x 20 x 20 cms - only used for shipping a combination of banners and charts
			$tq = 0; // the qty of tubes that will be needed
			$pq = $shipme[$index]['quantity']; // the quantity of chartsets in this line item
			$pw = $shipme[$index]['weight']; //the weight of the product (usually chartsets but may be banners or books)
			
			if ($pq < 3): {
				$tw = $tws;
				$tq = ceil($pq / 2);
			} // special calc to handle stuffing up to 2 charts per small tube
			elseif ($pq < 16): {
				$tw = $twm;
				$tq = ceil($pq / 5);
			} // special calc to handle stuffing up to 5 charts per medium tube
			elseif ($pq < 26): {
				$tw = $twl;
				$tq = ceil($pq / 25);
			} // special calc to handle stuffing up to 25 charts per large tube
			else: {
				$tw = $twm;
				$tq = ceil($pq / 5);
			} // let's give up here and go for the basic solution since actual packing will involve a variety of tubes.
			endif;
			
			$products_weight = (($pw * $pq) + ($tw * $tq)) / $tq; //(weight of one chart * qty ordered) + (weight of the tube * qty of tubes needed)
			$i = 0;
			while ($i < $tq) {
				if ($pq < 3): {
					$tw = $tws;
					$length = 73;
					$height = 8;
					$width = 8;
				} // special calc to handle stuffing up to 2 charts per small tube
				elseif ($pq < 16): {
					$tw = $twm;
					$length = 75;
					$height = 10;
					$width = 10;
				} // special calc to handle stuffing up to 5 charts per medium tube
				elseif ($pq < 25): {
					$tw = $twl;
					$length = 75;
					$height = 20;
					$width = 20;
				} // special calc to handle stuffing up to 25 charts per large tube
				else: {
					$tw = $twm;
					$length = 75;
					$height = 10;
					$width = 10;
				}
				endif;
				$i++;
			} // next in Qty
			
			$parcelWeight += $products_weight;
			$parcelQty += $tq;
			$productvalue = $shipme[$index]['price'] * $pq / $tq;
			$orderValue += $productvalue;
			// $width = 0    used for debugging missing data //
			$items[] = array(
				'Length' => $length * 10,
				'Width' => $width * 10,
				'Height' => $height * 10,
				'Weight' => $products_weight,
				'Qty' => $tq,
				'Insurance' => $productvalue
			);
			$shipme[$index] = null; //  Mark item as done
			$NoPack = 1; // Treat the tubes as separate parcels - Note: Only works if only one 'item' is in the cart.  Comment out for normal packing
		} // not this category
	} // next item
}


////   Custom code for tintex - Not sure what this is used for. Uncomment to use    
// for($index = 0 ; $index < count($shipme) ; $index++ ) {
//            
//        $id = explode(":", $shipme[$index]['id'] );  
//        $id = intval($id[0]);
//        if (($id == 5 || $id == 2) && ($products_weight < 100)) { 
//           $shipme[$index]['height'] = 90 ;
//           $shipme[$index]['width'] = 90 ;
//           $shipme[$index]['length'] = 90 ;
//        }
//        
//  if ($shipme[$index]['weight'] > 0) { 
//    $items[] = array('Length' => $shipme[$index]['length'], 'Width' => $shipme[$index]['width'], 'Height' => $shipme[$index]['height'] ,  'Weight' =>  $shipme[$index]['weight'],  'Qty' => $shipme[$index]['quantity'],  'Insurance' =>  $shipme[$index]['price'] );  
//        // Save these in case of error - We use them to calculate static rates // 
//   $parcelWeight += $products_weight * $shipme[$index]['quantity']; $parcelQty += $shipme[$index]['quantity'] ; $orderValue +=  $shipme[$index]['price'] ;
//    }  
//  }  // next item
////   End Custom code for tintex ///     
