<?php
/**
 * 数据功能扩展
 * User: yj-power-pc
 * Date: 2016/2/28
 * Time: 17:33
 */

namespace app\lib\common;

class ArrayUtility
{
    public static function setValueNotEmpty(array &$array, array  $source, array $names = [])
    {
        foreach ($names as $name)
        {
            if (!empty($source[$name]))
            {
                $array[$name] = $source[$name];
            }
        }
    }

    public static function setKeyNoExist(&$array, $name, $defaultValue)
    {
        if (!array_key_exists($name, $array))
        {
            $array[$name] = $defaultValue;
        }
    }

    public static function combineNoRepeat($source, array $addArray = [])
    {
        $resArray = [];

        if (!empty($source))
        {
            if (is_array($source))
            {
                $resArray = $source;
            }
            else
            {
                $resArray[] = $source;
            }
        }
        else
        {
            return $addArray;
        }

        if (isset($addArray))
        {
            foreach ($addArray as $item)
            {
                if (!in_array($item, $resArray))
                {
                    $resArray[] = $item;
                }
            }
        }

        return $resArray;
    }

    public static function warpCombineNoRepeat(array &$source, array $addArray = [])
    {
        if (isset($addArray))
        {
            foreach ($addArray as $item)
            {
                if (!in_array($item, $source))
                {
                    $resArray[] = $item;
                }
            }
        }

        return $source;
    }

    public static function toMap(array $array, $keyName)
    {
        $res = [];

        foreach ($array as $item)
        {
            $res[$item[$keyName]] = $item;
        }

        return $res;
    }

    public static function objArrayToMap(array $array, $filedName)
    {
        $res = [];

        foreach ($array as $item)
        {
            $res[$item->$filedName] = $item;
        }

        return $res;
    }

    public static function objArrayFieldValues(array $array, $filedName)
    {
        $res = [];

        foreach ($array as $item)
        {
            if (isset($item->$filedName))
            {
                $res[] = $item->$filedName;
            }
        }

        return $res;
    }

    public static function mapToIdNameArray(array $map, $valueFieldName = NULL)
    {
        $res = [];
        foreach ($map as $key => $value)
        {
            if (empty($valueFieldName))
            {
                $res[] = [
                    'id' => $key,
                    'name' => $value,
                ];
            }
            else
            {
                $res[] = [
                    'id' => $key,
                    'name' => $value[$valueFieldName],
                ];
            }
        }

        return $res;
    }

    public static function getFieldArray(array &$sourceArray, array $fileNameMap)
    {
        $res = [];
        foreach ($fileNameMap as $key => $value)
        {
            if (array_key_exists($key, $sourceArray))
            {
                $res[empty($value) ? $key : $value] = $sourceArray[$key];
            }
        }

        return $res;
    }
}