<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient;

/**
 * Represents an entity.
 *
 * Aside from being able to set entity fields via the {@see setField} method, it also
 * has automatic getters/setters in camel case and supports array access. These three
 * ways of setting a field are therefore equivalent:
 * <code>
 * $entity->setOwnerId(1);
 * $entity->setField('OWNER_ID', 1);
 * $entity['OWNER_ID'] = 1;
 * </code>
 *
 * For standard fields, the magic getters/setters are considered the preferred method.
 *
 * For a full list of supported entities and their fields, see the
 * {@link http://workspace.pipelinersales.com/community/api/data/Entities.html API documentation}.
 */
class Entity implements \ArrayAccess
{

    private $type;
    private $values = array();
    private $modified = array();
    private $dateTimeFormat;

    /**
     * @param string $type type of the entity, e.g. Account, see
     * {@link http://workspace.pipelinersales.com/community/api/data/Entities.html
     * list of entities}
     * @param string $dateTimeFormat the format for converting DateTime objects into strings
     */
    public function __construct($type, $dateTimeFormat)
    {
        $this->type = $type;
        $this->dateTimeFormat = $dateTimeFormat;
    }

    public function __call($name, $arguments)
    {
        $prefix = substr($name, 0, 3);
        if ($prefix == 'get' or $prefix == 'set') {
            $fieldName = $this->nameFromCamelCase(substr($name, 3));

            if ($prefix == 'set') {
                return $this->setField($fieldName, $arguments[0]);
            } else {
                return $this->getField($fieldName);
            }
        }
        throw new \BadMethodCallException('Call to a non-existent method \'' . $name . '\'');
    }

    /**
     * Sets this entity's field and adds it to the list of modified fields.
     * Returns $this, which allows for chaining of setters.
     *
     * @param string $fieldName Name of the field to set, see
     * {@link http://workspace.pipelinersales.com/community/api/data/Entities.html API documentation}.
     * All standard fields are in upper-case, with underscore between the words (e.g. FORM_TYPE).
     * Custom fields don't have this requirement.
     * @param mixed $value Value to set the fields to. DateTime objects are automatically converted
     * to strings according to the configured format (so calling a getter afterwards will only
     * return this string). Other types of values are used directly.
     * @return Entity
     */
    public function setField($fieldName, $value)
    {
        if ($value instanceof \DateTime) {
            $value = $this->convertDateTime($value);
        }

        $this->values[$fieldName] = $value;
        $this->modified[$fieldName] = true;
        return $this;
    }

    /**
     * Sets values of multiple fields at once. Fields not present in the array
     * will not be changed in any way.
     * @param array $values an associative array of fields to values
     */
    public function setFields($values)
    {
        foreach ($values as $field => $value) {
            $this->setField($field, $value);
        }
    }

    /**
     * Unsets the specified field. This means that the field will not be
     * changed upon saving. This affects both the "full update" and the
     * "modified only" update.
     * @return Entity
     */
    public function unsetField($fieldName)
    {
        unset($this->values[$fieldName]);
        unset($this->modified[$fieldName]);
        return $this;
    }

    /**
     * Returns the value of the specified field in this entity. A PHP notice will be raised
     * if the value hasn't been set yet.
     * @param string $fieldName Name of the field, see the
     * {@link http://workspace.pipelinersales.com/community/api/data/Entities.html API documentation}.
     * @return mixed
     */
    public function getField($fieldName)
    {
        return $this->values[$fieldName];
    }

    /**
     * Returns an associative array of all fields and their values
     * @return array
     */
    public function getFields()
    {
        return $this->values;
    }

    /**
     * Returns an associative array of fields which were modified since the entity was created/loaded,
     * and their values
     * @return string[]
     */
    public function getModifiedFields()
    {
        return array_intersect_key($this->values, $this->modified);
    }

    /**
     * Returns true if this field has some configured value.
     * @param string $fieldName
     * @return boolean
     */
    public function isFieldSet($fieldName)
    {
        return isset($this->values[$fieldName]);
    }

    private function convertDateTime(\DateTime $dateTime)
    {
        $dateTimeCopy = clone $dateTime;
        $dateTimeCopy->setTimezone(new \DateTimeZone('UTC'));
        return $dateTimeCopy->format($this->dateTimeFormat);
    }

    /**
     * Converts a NameLikeThis (used in getters/setters) into a NAME_LIKE_THIS (used in fields)
     */
    private function nameFromCamelCase($name)
    {
        return strtoupper(substr(preg_replace('/([A-Z])/', '_\1', $name), 1));
    }

    /**
     * Returns a JSON-encoded string representing this entity. It will contain all
     * of this entity's fields.
     *
     * @return string
     */
    public function allToJson()
    {
        return json_encode($this->values);
    }

    /**
     * Returns a JSON-encoded string representing this entity, which will only contain
     * fields that have been modified since the entity was last loaded or saved.
     *
     * @return string
     */
    public function modifiedToJson()
    {
        return json_encode(array_intersect_key($this->values, $this->modified));
    }

    /**
     * Resets the list of modified fields. All fields will be considered "not modified".
     * {@see RestRepository} calls this automatically after successfully saving on the server.
     */
    public function resetModified()
    {
        $this->modified = array();
    }

    /**
     * Returns the type of this entity.
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function offsetExists($offset)
    {
        return $this->isFieldSet($offset);
    }

    public function offsetGet($offset)
    {
        return $this->getField($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->setField($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->unsetField($offset);
    }
}
