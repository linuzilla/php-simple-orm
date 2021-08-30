<?php


namespace Linuzilla\Form\Attributes;


use Attribute;
use Linuzilla\Form\Attributes\Categories\AdvancedValidateAttribute;
use ReflectionClass;
use ReflectionException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ValidByMemberMethod implements AdvancedValidateAttribute {
    const DEFAULT_ERROR_MESSAGE = 'valid.by.member.method';
    private string $methodName;
    private string $errorMessage;

    /**
     * ValidByMember constructor.
     * @param string $methodName
     * @param string $errorMessage
     */
    public function __construct(string $methodName, string $errorMessage = self::DEFAULT_ERROR_MESSAGE) {
        $this->methodName = $methodName;
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
     * @return string
     */
    public function defaultErrorMessage(): string {
        return self::DEFAULT_ERROR_MESSAGE;
    }

    /**
     * @param string $value
     * @param array $values
     * @param object $bean
     * @param ReflectionClass $ref
     * @return string|null
     */
    public function advancedValidate(string $value, array $values, object $bean, ReflectionClass $ref): ?string {
        try {
            $method = $ref->getMethod($this->methodName);
            if ($method->invoke($bean) !== false) {
                return null;
            }
        } catch (ReflectionException $e) {
        }
        return $this->errorMessage;
    }
}