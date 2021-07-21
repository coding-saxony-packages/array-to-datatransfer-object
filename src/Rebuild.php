<?php

namespace CodingSaxony\ArrayToDataTransferObject;

use CodingSaxony\ArrayToDataTransferObject\Helpers\DocBlock;
use ReflectionClass;
use ReflectionException;
use ReflectionUnionType;
use stdClass;

class Rebuild
{
    /**
     * @param string      $objectClass
     * @param array       $incomingArray
     * @param string|null $rootKey
     * @param string|null $secondKey
     * @param bool        $isList
     *
     * @return stdClass
     * @throws ReflectionException
     */
    public static function rebuildToObject(string $objectClass, array $incomingArray, string $rootKey = null, string $secondKey = null, bool $isList = false): stdClass
    {
        $fieldsAndTypes = self::loadFieldsAndTypes($objectClass);
        $unifiedData    = self::unificationOfTheData($fieldsAndTypes, $incomingArray, $rootKey, $secondKey, $isList);
        $serializedData = self::serializeDataTypes($unifiedData, $fieldsAndTypes, $isList);

        return self::serializedDataToObject($serializedData);
    }

    /**
     * @param string $mainClass
     *
     * @return array
     * @throws ReflectionException
     */
    private static function loadFieldsAndTypes(string $mainClass): array
    {
        $fields = [];

        foreach ((new ReflectionClass($mainClass))->getProperties() as $property) {
            if (in_array($property->getName(), [
                'exceptKeys',
                'onlyKeys',
            ])) {
                continue;
            }

            $docBlock = new DocBlock($property->getDocComment());

            if ($property->getType() instanceof ReflectionUnionType) {
                $field = [
                    'responseKey' => $docBlock->tag('key')[0],
                ];

                foreach ($property->getType()->getTypes() as $type) {
                    if ($type->getName() === 'null') {
                        $field['isOptional'] = true;
                    }

                    if (class_exists($type->getName()) === true) {
                        $field['fields'] = self::loadFieldsAndTypes($type->getName());
                    }

                    $field['types'][] = $type->getName();
                }


            } else {
                if (class_exists($property->getType()->getName()) === true) {
                    $field = [
                        'responseKey' => $docBlock->tag('key')[0],
                        'isOptional'  => $property->getType()->allowsNull(),
                        'fields'      => self::loadFieldsAndTypes($property->getType()->getName()),
                    ];
                } else {
                    $field = [
                        'responseKey' => $docBlock->tag('key')[0],
                        'isOptional'  => $property->getType()->allowsNull(),
                        'type'        => $property->getType()->getName(),
                    ];
                }
            }

            if ($docBlock->hasTag('list')) {
                $field['list'] = (bool)$docBlock->tag('list')[0];
            } else {
                $field['list'] = false;
            }

            $fields[$property->getName()] = $field;
        }

        return $fields;
    }

    /**
     * @param array       $dataTransferFields
     * @param array       $incomingArray
     * @param string|null $rootKey
     * @param string|null $secondKey
     * @param bool        $isList
     *
     * @return array
     */
    private static function unificationOfTheData(array $dataTransferFields, mixed $incomingArray, string $rootKey = null, string $secondKey = null, bool $isList = false): array
    {
        $unifiedData = [];

        foreach ($dataTransferFields as $dataTransferFieldKey => $dataTransferFieldProperties) {
            if (isset($dataTransferFieldProperties['fields'])) {
                if ($secondKey === null) {
                    if ($rootKey === null) {
                        if (isset($incomingArray[$dataTransferFieldProperties['responseKey']]) === true) {
                            if ($dataTransferFieldProperties['list'] === true) {
                                foreach ($incomingArray[$dataTransferFieldProperties['responseKey']] as $item) {
                                    $unifiedData[$dataTransferFieldKey][] = self::unificationOfTheData($dataTransferFieldProperties['fields'], $item);
                                }
                            } else {
                                $unifiedData[$dataTransferFieldKey] = self::unificationOfTheData($dataTransferFieldProperties['fields'], $incomingArray[$dataTransferFieldProperties['responseKey']]);
                            }
                        }
                    } else {
                        if (isset($incomingArray[$rootKey][$dataTransferFieldProperties['responseKey']]) === true) {
                            $unifiedData[$dataTransferFieldKey] = self::unificationOfTheData($dataTransferFieldProperties['fields'], $incomingArray[$rootKey][$dataTransferFieldProperties['responseKey']]);
                        }
                    }
                } else if ($rootKey !== null) {
                    if (isset($incomingArray[$rootKey][$secondKey][$dataTransferFieldProperties['responseKey']]) === true) {
                        if (isset($dataTransferFieldProperties['types'])) {
                            if ($isList === true) {
                                foreach ($incomingArray[$rootKey][$secondKey][$dataTransferFieldProperties['responseKey']] as $item) {
                                    $unifiedData[$dataTransferFieldKey][] = self::unificationOfTheData($dataTransferFieldProperties['fields'], $item);
                                }
                            } else {
                                $unifiedData[$dataTransferFieldKey] = self::unificationOfTheData($dataTransferFieldProperties['fields'], $incomingArray[$rootKey][$secondKey][$dataTransferFieldProperties['responseKey']]);
                            }
                        } else {
                            $unifiedData[$dataTransferFieldKey] = self::unificationOfTheData($dataTransferFieldProperties['fields'], $incomingArray[$rootKey][$secondKey][$dataTransferFieldProperties['responseKey']]);
                        }
                    }
                }
            } else {
                if ($secondKey === null) {
                    if ($rootKey === null) {
                        if (isset($incomingArray[$dataTransferFieldProperties['responseKey']]) === true) {
                            $unifiedData[$dataTransferFieldKey] = $incomingArray[$dataTransferFieldProperties['responseKey']];
                        }
                    } else {
                        if (isset($incomingArray[$rootKey][$dataTransferFieldProperties['responseKey']]) === true) {
                            $unifiedData[$dataTransferFieldKey] = $incomingArray[$rootKey][$dataTransferFieldProperties['responseKey']];
                        }
                    }
                } else if ($rootKey !== null) {
                    if (isset($incomingArray[$rootKey][$secondKey][$dataTransferFieldProperties['responseKey']]) === true) {
                        $unifiedData[$dataTransferFieldKey] = $incomingArray[$rootKey][$secondKey][$dataTransferFieldProperties['responseKey']];
                    }
                }
            }
        }

        return $unifiedData;
    }

    /**
     * @param array $data
     * @param array $fields
     * @param bool  $isList
     *
     * @return array
     */
    private static function serializeDataTypes(array $data, array $fields, bool $isList = false): array
    {
        $serializedData = [];

        foreach ($fields as $fieldKey => $fieldProperties) {
            if ($fieldProperties['isOptional'] === false) {
                if (isset($data[$fieldKey])) {
                    if (isset($fieldProperties['fields']) === true) {
                        $serializedData[$fieldKey] = self::serializeDataTypes($data[$fieldKey], $fieldProperties['fields']);
                    } else {
                        $serializedData[$fieldKey] = self::findConverterAndConvertData($data[$fieldKey], $fieldProperties['type']);
                    }
                }
            } else {
                if (isset($data[$fieldKey])) {
                    if (isset($fieldProperties['fields']) === true) {
                        if (isset($fieldProperties['types'])) {
                            if ($isList === true
                                || $fieldProperties['list'] === true
                            ) {
                                foreach ($data[$fieldKey] as $item) {
                                    $serializedData[$fieldKey][] = self::serializeDataTypes($item, $fieldProperties['fields']);
                                }
                            } else {
                                $serializedData[$fieldKey] = self::serializeDataTypes($data[$fieldKey], $fieldProperties['fields']);
                            }
                        } else {
                            $serializedData[$fieldKey] = self::serializeDataTypes($data[$fieldKey], $fieldProperties['fields']);
                        }
                    } else {
                        $serializedData[$fieldKey] = self::findConverterAndConvertData($data[$fieldKey], $fieldProperties['type']);
                    }
                }
            }
        }

        return $serializedData;
    }

    private static function findConverterAndConvertData(mixed $data, string $fieldType)
    {
        $fieldTypeFunction = 'is_' . $fieldType;

        if ($fieldTypeFunction($data) === false) {
            return self::convertData($data, $fieldType);
        } else {
            return $data;
        }
    }

    private static function convertData(mixed $data, string $fieldType)
    {
        $converter = '\\CodingSaxony\\ArrayToDataTransferObject\\Converters\\' . ucfirst(gettype($data)) . 'To' . ucfirst($fieldType);

        return $converter::convert($data);
    }

    /**
     * @param array $serializedData
     *
     * @return stdClass
     */
    private static function serializedDataToObject(array $serializedData): stdClass
    {
        $serializedDataObject = new stdClass;

        foreach ($serializedData as $key => $value) {
            $data      = [];
            $serialize = false;

            if (is_array($value)) {
                if (count($value) > 0) {
                    foreach ($value as $index => $item) {
                        if (is_int($index)) {
                            $data[] = self::serializedDataToObject($item);
                        } else {
                            $serialize = true;
                        }
                    }

                    if ($serialize === true) {
                        $data = self::serializedDataToObject($value);
                    }
                } else {
                    $data = self::serializedDataToObject($value);
                }
            } else {
                $data = $value;
            }

            $serializedDataObject->$key = $data;
        }

        return $serializedDataObject;
    }
}