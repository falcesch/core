<?php
/**
 * This file is part of Oveleon ImmoManager.
 *
 * @link      https://github.com/oveleon/contao-immo-manager-bundle
 * @copyright Copyright (c) 2018-2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://github.com/oveleon/contao-immo-manager-bundle/blob/master/LICENSE
 */

namespace Oveleon\ContaoImmoManagerBundle;

use Contao\PageModel;

class RealEstate
{
    private $objRealEstate = null;

    private $objType = null;

    private $formatter = null;

    private $arrFieldOrder = null;

    private $links = null;

    public function __construct($objRealEstate, $typeId)
    {
        $this->objRealEstate = $objRealEstate;

        /** @var RealEstateType $realEstateType */
        $realEstateType = RealEstateType::getInstance();

        if ($typeId === null)
        {
            $this->objType = $realEstateType->getTypeByRealEstate($objRealEstate);
        }
        else
        {
            $this->objType = $realEstateType->getTypeById($typeId);
        }

        $this->formatter = RealEstateFormatter::getInstance();
        $this->formatter->setRealEstateModel($objRealEstate);

        // load dca
        \Controller::loadDataContainer('tl_real_estate');

        // collect default order fields
        $this->arrFieldOrder = $this->getOrderFields();

        // collect links
        $arrLinks =  \StringUtil::deserialize($this->objRealEstate->links, true);

        foreach ($arrLinks as $link)
        {
            if(!$link)
            {
                continue;
            }

            $this->links[] = $link;
        }
    }

    public function __get($name)
    {
        if(property_exists($this, $name))
        {
            return $this->{$name};
        }

        if ($this->objRealEstate->{$name})
        {
            return $this->objRealEstate->{$name};
        }

        return null;
    }

    public function generateExposeUrl($pageId)
    {
        $objPage = \PageModel::findByPk($pageId);

        if (!$objPage instanceof \PageModel)
        {
            global $objPage;
        }

        $params = (\Config::get('useAutoItem') ? '/' : '/items/') . ($this->objRealEstate->alias ?: $this->objRealEstate->id);

        return ampersand($objPage->getFrontendUrl($params));
    }

    public function getTitle()
    {
        return $this->objRealEstate->objekttitel;
    }

    public function getDescription($maxTextLength=0)
    {
        if ($maxTextLength > 0)
        {
            $description = trim(preg_replace('#[\s\n\r\t]{2,}#', ' ', $this->objRealEstate->objektbeschreibung));

            while (substr($description, $maxTextLength, 1) != " ")
            {
                $maxTextLength++;

                if ($maxTextLength > strlen($description))
                {
                    break;
                }
            }

            return substr($description, 0, $maxTextLength) . '...';
        }

        return $this->objRealEstate->objektbeschreibung;
    }

    public function getMainImage()
    {
        if ($this->objRealEstate->titleImageSRC)
        {
            return $this->objRealEstate->titleImageSRC;
        }

        $arrFields = array('imageSRC', 'interiorViewImageSRC', 'exteriorViewImageSRC');

        foreach ($arrFields as $field)
        {
            if ($this->objRealEstate->{$field})
            {
                return \StringUtil::deserialize($this->objRealEstate->{$field})[0];
            }
        }

        return null;
    }

    public function getImageBundle()
    {
        $return = array();

        if ($this->objRealEstate->titleImageSRC)
        {
            $return[] = $this->objRealEstate->titleImageSRC;
        }

        $arrFields = array('imageSRC', 'interiorViewImageSRC', 'exteriorViewImageSRC', 'panoramaImageSRC');

        foreach ($arrFields as $field)
        {
            if ($this->objRealEstate->{$field})
            {
                $arrImages = \StringUtil::deserialize($this->objRealEstate->{$field});

                foreach ($arrImages as $image)
                {
                    $return[] = $image;
                }
            }
        }

        return $return;
    }

    public function getFloorPlanImages()
    {
        $return = array();

        if ($this->objRealEstate->planImageSRC)
        {
            $arrImages = \StringUtil::deserialize($this->objRealEstate->planImageSRC);

            foreach ($arrImages as $image)
            {
                $return[] = $image;
            }
        }

        return $return;
    }

    public function getStatus()
    {
        $return = array();

        if (strtotime('+7 day', $this->objRealEstate->dateAdded) > time())
        {
            $return[] = array
            (
                'value' => Translator::translateValue('neu'),
                'class' => 'new'
            );
        }
        if ($this->objRealEstate->verkaufstatus === 'reserviert')
        {
            $return[] = array
            (
                'value' => Translator::translateValue('reserviert'),
                'class' => 'reserved'
            );
        }
        if ($this->objRealEstate->verkaufstatus === 'verkauft')
        {
            $return[] = array
            (
                'value' => Translator::translateValue('verkauft'),
                'class' => 'saled'
            );
        }

        return $return;
    }

    public function getMarketingToken()
    {
        if($this->objRealEstate->vermarktungsartKauf || $this->objRealEstate->vermarktungsartErbpacht)
        {
            return Translator::translateValue('for_sale');
        }

        if($this->objRealEstate->vermarktungsartMietePacht || $this->objRealEstate->vermarktungsartLeasing)
        {
            return Translator::translateValue('for_rent');
        }
    }

    public function getLocation($forceCompleteAddress=false)
    {
        $arrAddress = array();

        if ($this->objRealEstate->objektadresseFreigeben || $forceCompleteAddress === true)
        {
            if ($this->objRealEstate->strasse)
            {
                $arrAddress['strasse'] = $this->objRealEstate->strasse;

                if ($this->objRealEstate->hausnummer)
                {
                    $arrAddress['hausnummer'] = $this->objRealEstate->hausnummer;
                }
            }
        }

        if ($this->objRealEstate->plz)
        {
            $arrAddress['plz'] = $this->objRealEstate->plz;
        }

        if ($this->objRealEstate->ort)
        {
            $arrAddress['ort'] = $this->objRealEstate->ort;

            if ($this->objRealEstate->regionalerZusatz)
            {
                $arrAddress['reagionalerZusatz'] = $this->objRealEstate->regionalerZusatz;
            }
        }

        return $arrAddress;
    }

    public function getLocationString($forceCompleteAddress=false)
    {
        $strAddress = '';
        $arrAddress = $this->getLocation($forceCompleteAddress);

        if($arrAddress['hausnummer'])
        {
            $strAddress .= $arrAddress['strasse'] . ' ' . $arrAddress['hausnummer'] . ', ';
        }
        elseif($arrAddress['strasse'])
        {
            $strAddress .= $arrAddress['strasse'] . ', ';
        }

        if($arrAddress['plz'])
        {
            $strAddress .= $arrAddress['plz'] . ' ';
        }

        if($arrAddress['reagionalerZusatz'])
        {
            $strAddress .= $arrAddress['ort'] . ' - ' . $arrAddress['reagionalerZusatz'];
        }
        else
        {
            $strAddress .= $arrAddress['ort'];
        }

        return $strAddress;
    }

    public function getMainPrice()
    {
        if($this->objRealEstate->{$this->objType->price})
        {
            return $this->formatter->getFormattedCollection($this->objType->price);
        }
        elseif($this->objRealEstate->{'freitextPreis'})
        {
            return $this->formatter->getFormattedCollection('freitextPreis');
        }
        else
        {
            return $this->formatter->getFormattedCollection('auf_anfrage');
        }
    }

    public function getMainArea()
    {
        return $this->formatter->getFormattedCollection($this->objType->area);
    }

    /**
     * return all details from real estate
     *
     * @param null|array $separateGroups: area, price, attribute, detail
     * @param bool       $includeAddress
     * @param string     $defaultGroup: Allows you to add non-assignable fields to a custom group name or add them to an existing group
     *
     * @return array $details: array('group1' [,group2,group3,...])
     */
    public function getDetails($separateGroups=null, $includeAddress = false, $defaultGroup='detail')
    {
        // set available groups and sort order
        $availableGroups = array('area', 'price', 'attribute', 'detail', 'energie');

        if($includeAddress)
        {
            $availableGroups[] = 'address';
        }

        $collection = array();

        // loop through the real estate fields
        foreach ($GLOBALS['TL_DCA']['tl_real_estate']['fields'] as $field => $data)
        {
            // check whether the field may be included
            if(!is_array($data['realEstate']) || !$this->formatter->isValid($field))
            {
                continue;
            }

            // get available config keys
            $configKeys = array_keys($data['realEstate']);

            // check if there a match with available groups
            if(!count(array_intersect($configKeys, $availableGroups)))
            {
                continue;
            }

            // check if the fields have to be assigned to groups
            if($separateGroups === null)
            {
                $collection[ $defaultGroup ][ $field ] = $this->formatter->getFormattedCollection($field);
            }
            else
            {
                $allocationGroup  = $defaultGroup;
                $sortedGroupOrder = array();

                // sort order
                foreach ($availableGroups as $aGroup)
                {
                    if(in_array($aGroup, $separateGroups))
                    {
                        $sortedGroupOrder[] = $aGroup;
                    }
                }

                // loop through given groups
                foreach ($sortedGroupOrder as $group)
                {
                    // check if the group is assignable
                    if(in_array($group, $configKeys))
                    {
                        $allocationGroup = $group;
                        break;
                    }
                }

                $collection[ $allocationGroup ][ $field ] = $this->formatter->getFormattedCollection($field);
            }
        }

        // sort fields within the group
        foreach ($collection as $group => $fieldList) {
            $collection[ $group ] = $this->sort($fieldList);
        }

        return $collection;
    }

    public function getMainDetails($max=null)
    {
        $return = array();

        $arrMainDetails = \StringUtil::deserialize($this->objType->mainDetails);

        if($arrMainDetails === null)
        {
            return $return;
        }

        $index = 1;

        foreach ($arrMainDetails as $detail)
        {
            if ($this->formatter->isValid($detail['field']))
            {
                $return[] = $this->formatter->getFormattedCollection($detail['field']);

                if ($max !== null && $max === $index++){
                    break;
                }
            }
        }

        return $return;
    }

    public function getMainAttributes($max=null)
    {
        $return = array();

        $arrMainAttributes = \StringUtil::deserialize($this->objType->mainAttributes);

        if($arrMainAttributes === null)
        {
            return $return;
        }

        $index = 1;

        foreach ($arrMainAttributes as $attribute)
        {
            if ($this->formatter->isFilled($attribute['field']))
            {
                $collection = $this->formatter->getFormattedCollection($attribute['field']);

                $return[] = array_merge(
                    $collection,
                    array(
                        'showValue' => $this->objRealEstate->{$attribute['field']} > 1
                    )
                );

                if ($max !== null && $max === $index++){
                    break;
                }
            }
        }

        return $return;
    }

    // ToDo: maxLength
    public function getTexts()
    {
        $validTexts = array('objektbeschreibung', 'ausstattBeschr', 'lage', 'sonstigeAngaben');

        $return = array();

        foreach ($validTexts as $text)
        {
            $return[] = $this->formatter->getFormattedCollection($text);
        }

        return $return;
    }

    public function getType()
    {
        return $this->objType;
    }

    public function getId()
    {
        return $this->objRealEstate->id;
    }

    public function getContactPerson($forceCompleteAddress=false)
    {
        $objContactPerson = $this->objRealEstate->getRelated('contactPerson');
        $objProvider = $objContactPerson->getRelated('pid');

        if($objContactPerson === null)
        {
            return null;
        }

        $contactPerson = $objContactPerson->row();

        $contactPerson['name'] = $objContactPerson->vorname . ' ' . $objContactPerson->name;

        if(!$objContactPerson->adressfreigabe && $forceCompleteAddress === false)
        {
            $arrAddressFields = array('strasse', 'hausnummer', 'plz', 'ort', 'land', 'zusatzfeld');

            foreach ($arrAddressFields as $arrAddressField)
            {
                unset($contactPerson[ $arrAddressField ]);
            }
        }

        if($objProvider->forwardingMode === 'provider') {
            $contactPerson['mainPhone'] = $objContactPerson->tel_zentrale;
            $contactPerson['mainEmail'] = $objContactPerson->email_zentrale;
        }
        else
        {
            if($objContactPerson->email_direkt)
            {
                $contactPerson['mainEmail'] = $objContactPerson->email_direkt;
            }
            else
            {
                $contactPerson['mainEmail'] = $objContactPerson->email_zentrale;
            }

            if($objContactPerson->tel_durchw)
            {
                $contactPerson['mainPhone'] = $objContactPerson->tel_durchw;
            }
            else
            {
                $contactPerson['mainPhone'] = $objContactPerson->tel_zentrale;
            }
        }

        return $contactPerson;
    }

    /**
     * Collect and return all default order fields as sorted array
     *
     * @return array
     */
    private function getOrderFields()
    {
        $orderedFields = array();

        // get default fields with order index
        if (\is_array($GLOBALS['TL_DCA']['tl_real_estate']['fields']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_real_estate']['fields'] as $field => $data)
            {
                if(\is_array($data['realEstate']) && $data['realEstate']['order'])
                {
                    $orderedFields[$field] = $data['realEstate']['order'];
                }
            }
        }

        // sort fields by value
        asort($orderedFields);

        // if there is a separate order in the types, manipulate the default field order
        if($this->objType->orderFields)
        {
            $orderValues = \StringUtil::deserialize($this->objType->orderedFields, true);

            foreach (array_reverse($orderValues) as $order)
            {
                // remove field from current array position
                unset($orderedFields[ $order['field'] ]);

                // add the field to the first position
                $orderedFields = array($order['field'] => 0) + $orderedFields;
            }
        }

        return array_keys($orderedFields);
    }

    /**
     * Sorts an array by order fields
     *
     * @param $arrList
     *
     * @return array
     */
    public function sort($arrList){
        $ordered = array();

        foreach ($this->arrFieldOrder as $index => $key) {
            if (array_key_exists($key, $arrList)) {
                $ordered[$key] = $arrList[$key];
                unset($arrList[$key]);
            }
        }

        return $ordered + $arrList;
    }
}