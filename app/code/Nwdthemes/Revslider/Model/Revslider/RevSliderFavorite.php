<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2019 ThemePunch
 */

namespace Nwdthemes\Revslider\Model\Revslider;

class RevSliderFavorite extends RevSliderFunctions {

	public $allowed	= array(
		'moduletemplates',
		'moduletemplateslides',
		'modules',
		'moduleslides',
		'svgs',
		'images',
		'videos',
		'objects',
		'fonticons'
	);

	public function __construct(
		\Nwdthemes\Revslider\Helper\Framework $frameworkHelper
    ) {
		parent::__construct($frameworkHelper);
	}

	/**
	 * change the setting of a favorization
	 **/
	public function set_favorite($do, $type, $id){
		$fav = $this->_frameworkHelper->get_option('rs_favorite', array());
		$id	 = $this->_frameworkHelper->esc_attr($id);

		if(in_array($type, $this->allowed)){
			if(!isset($fav[$type])) $fav[$type] = array();

			$key = array_search($id, $fav[$type]);

			if($key === false){
				if($do == 'add') $fav[$type][] = $id;
			}else{
				if($do == 'remove'){
					unset($fav[$type][$key]);
				}
			}
		}
		$this->_frameworkHelper->update_option('rs_favorite', $fav);

		return $fav;
	}


	/**
	 * get a certain favorite type
	 **/
	public function get_favorite($type){
		$fav = $this->_frameworkHelper->get_option('rs_favorite', array());
		$list = array();
		if(in_array($type, $this->allowed)){
			$list = $this->get_val($fav, $type, array());
		}

		return $list;
	}


	/**
	 * return if certain element is in favorites
	 **/
	public function is_favorite($type, $id){
		$favs = $this->get_favorite($type);

		return (array_search($id, $favs) !== false) ? true : false;
	}
}
