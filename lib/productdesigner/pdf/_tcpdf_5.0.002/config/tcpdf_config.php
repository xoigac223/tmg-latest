<?php

// If you define the constant K_TCPDF_EXTERNAL_CONFIG, the following settings will be ignored.

if (!defined('K_TCPDF_EXTERNAL_CONFIG')) {

	// DOCUMENT_ROOT fix for IIS Webserver
	if ((!isset($_SERVER['DOCUMENT_ROOT'])) OR (empty($_SERVER['DOCUMENT_ROOT']))) {
		if(isset($_SERVER['SCRIPT_FILENAME'])) {
			$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
		} elseif(isset($_SERVER['PATH_TRANSLATED'])) {
			$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
		}	else {
			// define here your DOCUMENT_ROOT path if the previous fails
			$_SERVER['DOCUMENT_ROOT'] = '/var/www';
		}
	}

	// Automatic calculation for the following K_PATH_MAIN constant
	$k_path_main = str_replace( '\\', '/', realpath(substr(dirname(__FILE__), 0, 0-strlen('config'))));
	if (substr($k_path_main, -1) != '/') {
		$k_path_main .= '/';
	}

	/**
	 * Installation path (/var/www/tcpdf/).
	 * By default it is automatically calculated but you can also set it as a fixed string to improve performances.
	 */
	if (!defined('K_PATH_MAIN')) {
		define ('K_PATH_MAIN', $k_path_main);
	}

	// Automatic calculation for the following K_PATH_URL constant
	$k_path_url = $k_path_main; // default value for console mode
	if (isset($_SERVER['HTTP_HOST']) AND (!empty($_SERVER['HTTP_HOST']))) {
		if(isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS'])!='off') {
			$k_path_url = 'https://';
		} else {
			$k_path_url = 'http://';
		}
		$k_path_url .= $_SERVER['HTTP_HOST'];
		$k_path_url .= str_replace( '\\', '/', substr($_SERVER['PHP_SELF'], 0, -24));
	}

	/**
	 * URL path to tcpdf installation folder (http://localhost/tcpdf/).
	 * By default it is automatically calculated but you can also set it as a fixed string to improve performances.
	 */
	if (!defined('K_PATH_URL')) {
		define ('K_PATH_URL', $k_path_url);
	}

	/**
	 * path for PDF fonts
	 * use K_PATH_MAIN.'fonts/old/' for old non-UTF8 fonts
	 */
	if (!defined('K_PATH_FONTS')) {
		define ('K_PATH_FONTS', K_PATH_MAIN.'fonts/');
	}

	/**
	 * cache directory for temporary files (full path)
	 */
	if (!defined('K_PATH_CACHE')) {
		define ('K_PATH_CACHE', K_PATH_MAIN.'cache/');
	}

	/**
	 * cache directory for temporary files (url path)
	 */
	if (!defined('K_PATH_URL_CACHE')) {
		define ('K_PATH_URL_CACHE', K_PATH_URL.'cache/');
	}

	/**
	 *images directory
	 */
	if (!defined('K_PATH_IMAGES')) {
		define ('K_PATH_IMAGES', K_PATH_MAIN.'images/');
	}

	/**
	 * blank image
	 */
	if (!defined('K_BLANK_IMAGE')) {
		define ('K_BLANK_IMAGE', K_PATH_IMAGES.'_blank.png');
	}

	/**
	 * page format
	 */
	if (!defined('PDF_PAGE_FORMAT')) {
		define ('PDF_PAGE_FORMAT', 'A4');
	}

	/**
	 * page orientation (P=portrait, L=landscape)
	 */
	if (!defined('PDF_PAGE_ORIENTATION')) {
		define ('PDF_PAGE_ORIENTATION', 'P');
	}

	/**
	 * document creator
	 */
	if (!defined('PDF_CREATOR')) {
		define ('PDF_CREATOR', 'TCPDF');
	}

	/**
	 * document author
	 */
	if (!defined('PDF_AUTHOR')) {
		define ('PDF_AUTHOR', 'TCPDF');
	}

	/**
	 * header title
	 */
	if (!defined('PDF_HEADER_TITLE')) {
		define ('PDF_HEADER_TITLE', 'TCPDF Example');
	}

	/**
	 * header description string
	 */
	if (!defined('PDF_HEADER_STRING')) {
		define ('PDF_HEADER_STRING', "by Nicola Asuni - Tecnick.com\nwww.tcpdf.org");
	}

	/**
	 * image logo
	 */
	if (!defined('PDF_HEADER_LOGO')) {
		define ('PDF_HEADER_LOGO', 'tcpdf_logo.jpg');
	}

	/**
	 * header logo image width [mm]
	 */
	if (!defined('PDF_HEADER_LOGO_WIDTH')) {
		define ('PDF_HEADER_LOGO_WIDTH', 30);
	}

	/**
	 *  document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch]
	 */
	if (!defined('PDF_UNIT')) {
		define ('PDF_UNIT', 'mm');
	}

	/**
	 * header margin
	 */
	if (!defined('PDF_MARGIN_HEADER')) {
		define ('PDF_MARGIN_HEADER', 5);
	}

	/**
	 * footer margin
	 */
	if (!defined('PDF_MARGIN_FOOTER')) {
		define ('PDF_MARGIN_FOOTER', 10);
	}

	/**
	 * top margin
	 */
	if (!defined('PDF_MARGIN_TOP')) {
		define ('PDF_MARGIN_TOP', 27);
	}

	/**
	 * bottom margin
	 */
	if (!defined('PDF_MARGIN_BOTTOM')) {
		define ('PDF_MARGIN_BOTTOM', 25);
	}

	/**
	 * left margin
	 */
	if (!defined('PDF_MARGIN_LEFT')) {
		define ('PDF_MARGIN_LEFT', 15);
	}

	/**
	 * right margin
	 */
	if (!defined('PDF_MARGIN_RIGHT')) {
		define ('PDF_MARGIN_RIGHT', 15);
	}

	/**
	 * default main font name
	 */
	if (!defined('PDF_FONT_NAME_MAIN')) {
		define ('PDF_FONT_NAME_MAIN', 'helvetica');
	}

	/**
	 * default main font size
	 */
	if (!defined('PDF_FONT_SIZE_MAIN')) {
		define ('PDF_FONT_SIZE_MAIN', 10);
	}

	/**
	 * default data font name
	 */
	if (!defined('PDF_FONT_NAME_DATA')) {
		define ('PDF_FONT_NAME_DATA', 'helvetica');
	}

	/**
	 * default data font size
	 */
	if (!defined('PDF_FONT_SIZE_DATA')) {
		define ('PDF_FONT_SIZE_DATA', 8);
	}

	/**
	 * default monospaced font name
	 */
	if (!defined('PDF_FONT_MONOSPACED')) {
		define ('PDF_FONT_MONOSPACED', 'courier');
	}

	/**
	 * ratio used to adjust the conversion of pixels to user units
	 */
	if (!defined('PDF_IMAGE_SCALE_RATIO')) {
		define ('PDF_IMAGE_SCALE_RATIO', 1.25);
	}

	/**
	 * magnification factor for titles
	 */
	if (!defined('HEAD_MAGNIFICATION')) {
		define('HEAD_MAGNIFICATION', 1.1);
	}

	/**
	 * height of cell repect font height
	 */
	if (!defined('K_CELL_HEIGHT_RATIO')) {
		define('K_CELL_HEIGHT_RATIO', 1.25);	
	}

	/**
	 * title magnification respect main font size
	 */
	if (!defined('K_TITLE_MAGNIFICATION')) {
		define('K_TITLE_MAGNIFICATION', 1.3);
	}

	/**
	 * reduction factor for small font
	 */
	if (!defined('K_SMALL_RATIO')) {
		define('K_SMALL_RATIO', 2/3);
	}

	/**
	 * set to true to enable the special procedure used to avoid the overlappind of symbols on Thai language
	 */
	if (!defined('K_THAI_TOPCHARS')) {
		define('K_THAI_TOPCHARS', true);
	}

	/**
	 * if true allows to call TCPDF methods using HTML syntax
	 * IMPORTANT: For security reason, disable this feature if you are printing user HTML content.
	 */
	if (!defined('K_TCPDF_CALLS_IN_HTML')) {
		define('K_TCPDF_CALLS_IN_HTML', true);
	}
}

//============================================================+
// END OF FILE
//============================================================+
?>
