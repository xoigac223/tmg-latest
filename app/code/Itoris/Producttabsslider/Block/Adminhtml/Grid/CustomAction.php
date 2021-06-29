<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_TABS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */


namespace Itoris\Producttabsslider\Block\Adminhtml\Grid;


class CustomAction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    protected $_tabArrayId;
    protected function _toLinkHtml($action, \Magento\Framework\DataObject $row,$opt=[])
    {
        $tab_id =$row->getData('tab_id');
        $action_menthod='';
        if(!isset($opt['color'])) {
            $actionAttributes = new \Magento\Framework\DataObject();

            $actionCaption = '';
            $this->_transformActionData($action, $actionCaption, $row);

            if (isset($action['confirm'])) {
                $action['onclick'] = 'return window.confirm(\'' . addslashes(
                        $this->escapeHtml($action['confirm'])
                    ) . '\')';
                unset($action['confirm']);
            }
            if(isset($opt['method'])) {
                $action_menthod = $opt['method'];
            }
            $actionAttributes->setData($action);
            return '<a class="'.$action_menthod.'" data_tab_custom="'.$tab_id.'"' . $actionAttributes->serialize() . '>' . $actionCaption . '</a>';
        }else{
            $actionAttributes = new \Magento\Framework\DataObject();
            $actionCaption = '';
            $this->_transformActionData($action, $actionCaption, $row);

            if (isset($action['confirm'])) {
                $action['onclick'] = 'return window.confirm(\'' . addslashes(
                        $this->escapeHtml($action['confirm'])
                    ) . '\')';
                unset($action['confirm']);
            }
            $action_menthod = $opt['method'];
            $actionAttributes->setData($action);
            return '<a class="'.$action_menthod.'"  style = "color:red;" data_tab_custom="'.$tab_id.'" ' . $actionAttributes->serialize() . '>' . $actionCaption . '</a>';
        }
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = $this->getColumn()->getActions();

        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }
        if (sizeof($actions) == 1 && !$this->getColumn()->getNoLink()) {
            foreach ($actions as $action) {
                if (is_array($action)) {
                    return $this->_toLinkHtml($action, $row);
                }
            }
        }
        $out = '';
        $i = 0;
        foreach ($actions as $action) {
            $i++;
            $data=[];
            if (is_array($action)) {
                if($i==1){
                    $data['method']='edit_tabs_grid';
                    $out .= $this->_toLinkHtml($action, $row,$data);
                }
                elseif(!in_array($row->getData('tab_id'),$this->getColumn()->getData('id_global'))){
                    $data['method']='delete_tabs_grid';
                    $data['color']=true;
                    $out .= '|<span>'.$this->_toLinkHtml($action, $row,$data).'</span>';
                }

            }
        }
        $out .= '</select>';
        return $out;
    }
}