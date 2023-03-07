<?php

namespace Untek\Core\Property\Helpers;

use Untek\Core\Arr\Helpers\ArrayHelper;
use Untek\Core\Instance\Helpers\ClassHelper;
use Untek\Lib\Components\DynamicEntity\Interfaces\DynamicEntityAttributesInterface;

class PropertyHelper
{

    public static function createObject(string $className, $attributes = [])
    {
        $entityInstance = ClassHelper::createObject($className);
        if ($attributes) {
            \Untek\Core\Code\Helpers\PropertyHelper::setAttributes($entityInstance, $attributes);
        }
        return $entityInstance;
    }

    public static function toArray($entity, bool $recursive = false): array
    {
        $array = [];
        if (is_array($entity)) {
            $array = $entity;
        } elseif ($entity instanceof \Doctrine\Common\Collections\Collection) {
            $array = $entity->toArray();
        } elseif (is_object($entity)) {
            $attributes = self::getAttributeNames($entity);
            if ($attributes) {
//                $propertyAccessor = PropertyAccess::createPropertyAccessor();
                foreach ($attributes as $attribute) {
                    $array[$attribute] = \Untek\Core\Code\Helpers\PropertyHelper::getValue($entity, $attribute);
//                    $array[$attribute] = $propertyAccessor->getValue($entity, $attribute);
                }
            } else {
                $array = (array)$entity;
            }
        }
        if ($recursive) {
            foreach ($array as $key => $item) {
                if (is_object($item) || is_array($item)) {
                    $array[$key] = self::toArray($item, $recursive/*, $keyFormat*/);
                }
            }
        }
        foreach ($array as $key => $value) {
            $isPrivate = mb_strpos($key, "\x00*\x00") !== false;
            if ($isPrivate) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    public static function getAttributeNames($entity): array
    {
        $reflClass = new \ReflectionClass($entity);
        $attributesRef = $reflClass->getProperties();
        $attributes = ArrayHelper::getColumn($attributesRef, 'name');
        foreach ($attributes as $index => $attributeName) {
            if ($attributeName[0] == '_') {
                unset($attributes[$index]);
            }
        }
        return $attributes;
    }
}
