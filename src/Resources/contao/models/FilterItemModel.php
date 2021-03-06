<?php
/**
 * This file is part of Contao EstateManager.
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/core
 * @copyright Copyright (c) 2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

namespace ContaoEstateManager;


/**
 * Reads and writes filter items
 *
 * @property integer $id
 * @property integer $pid
 * @property integer $sorting
 * @property integer $tstamp
 * @property string  $type
 * @property string  $label
 * @property string  $name
 * @property boolean $published
 *
 * @method static FilterItemModel|null findById($id, array $opt=array())
 * @method static FilterItemModel|null findByPk($id, array $opt=array())
 * @method static FilterItemModel|null findOneBy($col, $val, $opt=array())
 * @method static FilterItemModel|null findOneByTstamp($col, $val, $opt=array())
 * @method static FilterItemModel|null findOneByType($col, $val, $opt=array())
 * @method static FilterItemModel|null findOneByTitle($col, $val, $opt=array())
 * @method static FilterItemModel|null findOneByPublished($col, $val, $opt=array())
 *
 * @method static \Model\Collection|FilterItemModel[]|FilterItemModel|null findMultipleByIds($val, array $opt=array())
 * @method static \Model\Collection|FilterItemModel[]|FilterItemModel|null findByPid($val, array $opt=array())
 * @method static \Model\Collection|FilterItemModel[]|FilterItemModel|null findByTstamp($val, array $opt=array())
 * @method static \Model\Collection|FilterItemModel[]|FilterItemModel|null findByType($val, array $opt=array())
 * @method static \Model\Collection|FilterItemModel[]|FilterItemModel|null findByTitle($val, array $opt=array())
 * @method static \Model\Collection|FilterItemModel[]|FilterItemModel|null findByPublished($val, array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByPid($val, array $opt=array())
 * @method static integer countByTstamp($id, array $opt=array())
 * @method static integer countByType($id, array $opt=array())
 * @method static integer countByTitle($id, array $opt=array())
 * @method static integer countByPublished$id, array $opt=array())
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */

class FilterItemModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_filter_item';

    /**
     * Find published filter items by ID
     *
     * @param integer $intId       The filter ID
     * @param array   $arrOptions  An optional options array
     *
     * @return FilterItemModel|null  A model or null if filter item is not published
     */
    public static function findPublishedById($intId, array $arrOptions=array())
    {
        $t = static::$strTable;
        $arrColumns = array("$t.id=?");

        if (!static::isPreviewMode($arrOptions))
        {
            $arrColumns[] = "$t.invisible=''";
        }

        return static::findOneBy($arrColumns, $intId, $arrOptions);
    }

    /**
     * Find published filter items by their parent ID
     *
     * @param integer $intPid     The filter ID
     * @param array   $arrOptions An optional options array
     *
     * @return \Model\Collection|FilterItemModel[]|FilterItemModel|null A collection of models or null if there are no filter items
     */
    public static function findPublishedByPid($intPid, array $arrOptions=array())
    {
        $t = static::$strTable;
        $arrColumns = array("$t.pid=?");

        if (!static::isPreviewMode($arrOptions))
        {
            $arrColumns[] = "$t.invisible=''";
        }

        if (!isset($arrOptions['order']))
        {
            $arrOptions['order'] = "$t.sorting";
        }

        return static::findBy($arrColumns, $intPid, $arrOptions);
    }

    /**
     * Find published filter items by type and their parent ID
     *
     * @param string  $strType    The filter type
     * @param integer $intPid     The filter ID
     * @param array   $arrOptions An optional options array
     *
     * @return \Model\Collection|FilterItemModel[]|FilterItemModel|null A collection of models or null if there are no filter items
     */
    public static function findPublishedByTypeAndPid($strType, $intPid, array $arrOptions=array())
    {
        $t = static::$strTable;
        $arrColumns = array("$t.type=? AND $t.pid=?");
        $arrValues = array($strType, $intPid);

        if (!static::isPreviewMode($arrOptions))
        {
            $arrColumns[] = "$t.invisible=''";
        }

        if (!isset($arrOptions['order']))
        {
            $arrOptions['order'] = "$t.sorting";
        }

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }
}
