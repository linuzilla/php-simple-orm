<?php


namespace Linuzilla\Form\Attributes;


use Attribute;
use Linuzilla\Form\Attributes\Categories\AdvancedValidateAttribute;
use ReflectionClass;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ValidSameAs implements AdvancedValidateAttribute {
    const DEFAULT_ERROR_MESSAGE = 'valid.not.same.as';
    private string $reference;
    private string $errorMessage;

    /**
     * ValidSameAs constructor.
     * @param string $reference
     * @param string $errorMessage
     */
    public function __construct(string $reference, string $errorMessage = self::DEFAULT_ERROR_MESSAGE) {
        $this->reference = $reference;
        $this->errorMessage = $errorMessage;
    }

    /**
     * @param string $value
     * @return string|null
     */
    public function validate(string $value): ?string {
        return null;
    }


    /**
     * @param string $value
     * @param array $values
     * @param object $bean
     * @param ReflectionClass $ref
     * @return string|null
     */
    public function advancedValidate(string $value, array $values, object $bean, ReflectionClass $ref): ?string {
        if (isset($values[$this->reference]) and $values[$this->reference] == $value) {
            return null;
        }
        return $this->errorMessage;
    }

    /**
     * @return string
     */
    public function defaultErrorMessage(): string {
        return self::DEFAULT_ERROR_MESSAGE;
    }

}