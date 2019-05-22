<?php

/**
 * This file is part of Oveleon ImmoManager.
 *
 * @link      https://github.com/oveleon/contao-immo-manager-bundle
 * @copyright Copyright (c) 2018-2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://github.com/oveleon/contao-immo-manager-bundle/blob/master/LICENSE
 */

$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'setMarketingType';
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'setRealEstateType';

// Extend the regular palette
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('immo_manager_legend', 'publish_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField(array('setMarketingType', 'setRealEstateType'), 'immo_manager_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('regular', 'tl_page')
;

// Extend subpalettes
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['setMarketingType'] = 'marketingType';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['setRealEstateType'] = 'realEstateType';

// Add fields
array_insert($GLOBALS['TL_DCA']['tl_page']['fields'], 0, array
(
    'setMarketingType' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_page']['setMarketingType'],
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'eval'                    => array('submitOnChange'=>true),
        'sql'                     => "char(1) NOT NULL default ''"
    ),
    'marketingType' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_page']['marketingType'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options'                 => array('kauf_erbpacht_miete_leasing' ,'kauf_erbpacht', 'miete_leasing'),
        'reference'               => &$GLOBALS['TL_LANG']['tl_real_estate_type'],
        'eval'                    => array('tl_class'=>'w50'),
        'sql'                     => "varchar(32) NOT NULL default ''",
    ),
    'setRealEstateType' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_page']['setRealEstateType'],
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'eval'                    => array('submitOnChange'=>true),
        'sql'                     => "char(1) NOT NULL default ''"
    ),
    'realEstateType' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_page']['realEstateType'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => array('tl_page_immo_manager', 'getRealEstateTypes'),
        'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
        'sql'                     => "int(10) unsigned NOT NULL default '0'",
    ),
));

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Fabian Ekert <fabian@oveleon.de>
 */
class tl_page_immo_manager extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Return all real estate types as array
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getRealEstateTypes(DataContainer $dc)
    {
        $objTypes = $this->Database->execute("SELECT id, title, longTitle FROM tl_real_estate_type");

        if ($objTypes->numRows < 1)
        {
            return array();
        }

        $arrTypes = array();

        while ($objTypes->next())
        {
            $arrTypes[$objTypes->id] = $objTypes->title . ' (' . $objTypes->longTitle . ')';
        }

        return $arrTypes;
    }
}