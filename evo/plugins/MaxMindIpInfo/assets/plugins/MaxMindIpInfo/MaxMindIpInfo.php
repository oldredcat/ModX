<?php
/**
 * Collect IP Info with MaxMind free database
 * 
 * MaxMind IP Info
 *
 * @category    plugin
 * @version     1.0.1
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @properties &display=Collect IP Info;list;All,City,Country,ASN;All
 * @internal    @events OnBeforeLoadDocumentObject
 * @internal    @modx_category Manager and Admin
 * @internal    @legacy_names MaxMindIpInfo
 * @internal    @installset base
 *
 * @reportissues https://github.com/extras-evolution/tinymce4-for-modx-evo
 * @documentation Plugin docs https://github.com/extras-evolution/tinymce4-for-modx-evo
 * @author      RedCat
 * @lastupdate  2019-09-13
 */
 
defined('MODX_BASE_PATH') or die('Access denied');

if(empty($_SESSION['MaxMindIpInfo'])){
	
	if(empty($_COOKIE['MaxMindIpInfo'])){
		
		$dataFileCity 		= __DIR__ . '/data/GeoLite2-City.mmdb';
		$dataFileCountry 	= __DIR__ . '/data/GeoLite2-Country.mmdb';
		$dataFileASN 		= __DIR__ . '/data/GeoLite2-ASN.mmdb';

		require_once __DIR__ . '/MaxMind/Db/Reader/InvalidDatabaseException.php';
		require_once __DIR__ . '/MaxMind/Db/Reader/Decoder.php';
		require_once __DIR__ . '/MaxMind/Db/Reader/Metadata.php';
		require_once __DIR__ . '/MaxMind/Db/Reader/Util.php';
		require_once __DIR__ . '/MaxMind/Db/Reader.php';
		
		$display = empty($display) ? 'all' : strtolower($display);
		
		$cityIpInfo 	= array();
		$countryIpInfo 	= array();
		$asnIpInfo 		= array();
		
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip_address = $_SERVER['REMOTE_ADDR'];
		}
		
		switch($display){
			case 'city':	if(is_file($dataFileCity)){
								$cityReader = new \MaxMind\Db\Reader($dataFileCity);
								$cityIpInfo = $cityReader->get($ip_address);
								$cityReader->close();
							}
				break;
			case 'country':	if(is_file($dataFileCountry)){
								$countryReader = new \MaxMind\Db\Reader($dataFileCountry);
								$countryIpInfo = $countryReader->get($ip_address);
								$countryReader->close();
							}
				break;
			case 'asn':		if(is_file($dataFileASN)){
								$asnReader = new \MaxMind\Db\Reader($dataFileASN);
								$asnIpInfo = $asnReader->get($ip_address);
								$asnReader->close();
							}
				break;
			default:		if(is_file($dataFileCity)){
								$cityReader = new \MaxMind\Db\Reader($dataFileCity);
								$cityIpInfo = $cityReader->get($ip_address);
								$cityReader->close();
							}
							
							if(is_file($dataFileCountry)){
								$countryReader = new \MaxMind\Db\Reader($dataFileCountry);
								$countryIpInfo = $countryReader->get($ip_address);
								$countryReader->close();
							}
							
							if(is_file($dataFileASN)){
								$asnReader = new \MaxMind\Db\Reader($dataFileASN);
								$asnIpInfo = $asnReader->get($ip_address);
								$asnReader->close();
							}
				break;
		}
		
		$result = array_merge($cityIpInfo, $countryIpInfo, $asnIpInfo);
		
		$_SESSION['MaxMindIpInfo'] = $result;
		
		setcookie('MaxMindIpInfo', base64_encode(json_encode($result)), time() + 864000, '/');	//	10 дней
		
	}else{
		$_SESSION['MaxMindIpInfo'] = json_decode(base64_decode($_COOKIE['MaxMindIpInfo']));
	}
}

file_put_contents(__DIR__ . '/test.txt', json_encode($_SESSION['MaxMindIpInfo']));