<?php

namespace ClassyLlama\AvaTax\Framework\Interaction\MetaData;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class ArrayType extends MetaDataAbstract
{
    /**
     * @param string $name
     * @param array $data
     */
    public function __construct($name, array $data = [])
    {
        parent::__construct('array', $name, $data);
    }

    /**
     * Set length of metadata object
     * Valid for string and array types
     * Returns true if length is valid for this object type and false if not
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param int $length
     * @return bool
     * @throws LocalizedException
     */
    public function setLength($length)
    {
        if (!is_integer($length) || $length <= 0) {
            throw new LocalizedException(new Phrase(
                'Length can only be set to integer greater than or equal to 0.  You tried to set it to: %1.',
                [
                    $length
                ]
            ));
        }
        $this->data[self::ATTR_LENGTH] = $length;
        return true;
    }

    /**
     * Set valid options of metadata object
     * Valid for integer, string, float
     * Returns true if valid options is valid for this type and false if not
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param array $validOptions
     * @return boolean
     */
    public function setValidOptions(array $validOptions)
    {
        return false;
    }

    /**
     * Set children metadata objects of this metadata object
     * Valid only on array type
     * Returns true if children are valid for this type and false if not
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param ValidationObject $subtype
     * @return bool
     */
    public function setSubtype(ValidationObject $subtype = null)
    {
        $this->data[self::ATTR_SUBTYPE] = $subtype;
        return true;
    }

    /**
     * Pass in a value and get the validated value back
     * If your data can be converted to an array, please do so explicitly before passing in
     * because automated array conversion will not be attempted since it can have unexpected results.
     *
     * @author Jonathan Hodges <jonathan@classyllama.com>
     * @param mixed $value
     * @return mixed
     * @throws LocalizedException
     */
    public function validateData($value)
    {
        if ($this->getType() != getType($value)) {
            if ($this->getRequired()) {
                throw new ValidationException(new Phrase(
                    'The value you passed in is not an array. ' .
                    'If your data can be converted to an array, please do so explicitly before passing in' .
                    'because automated array conversion will not be attempted since it can have unexpected results.'
                ));
            }
        } else {
            $value = [];
        }

        // If a subtype is defined, call this function for that contents of the array
        if (!is_null($this->getSubtype())) {
            $value = $this->getSubtype()->validateData($value);
        }

        // If the length exceeds the maximum allowed length, throw an exception
        if ($this->getLength() > 0) {
            throw new ValidationException(new Phrase(
                'You attempted to pass data to the AvaTax API with the key of %1,' . '
                         with a length of %2, the max allowed length is %3.',
                [
                    $this->getName(),
                    count($value),
                    $this->getLength(),
                ]
            ));
        }

        return $value;
    }
}