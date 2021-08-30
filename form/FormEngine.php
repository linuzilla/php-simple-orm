<?php


namespace Linuzilla\Form;


use Linuzilla\Form\Attributes\Categories\AdvancedValidateAttribute;
use Linuzilla\Form\Attributes\Categories\ConverterAttribute;
use Linuzilla\Form\Attributes\Categories\GenericFormAttribute;
use Linuzilla\Form\Attributes\Categories\PropertyAttribute;
use Linuzilla\Form\Attributes\Categories\TypeAttribute;
use Linuzilla\Form\Attributes\Categories\ValidatableAttribute;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class FormEngine
 * @package Linuzilla\Form
 * @author Mac Liu <linuzilla@gmail.com>
 */
class FormEngine {
    private ReflectionClass $ref;
    /** @var ReflectionProperty[] $properties */
    private array $properties;
    /** @var string[] $validationErrors */
    private array $validationErrors;

    /**
     * FormEngine constructor.
     * @param object $formBean
     */
    public function __construct(private object $formBean) {
        $this->ref = new ReflectionClass($formBean);
        $this->validationErrors = [];

        $this->properties = array_reduce($this->ref->getProperties(), function ($carry, $property) {
            $carry[$property->name] = $property;
            return $carry;
        });
    }

    private function htmlInputPart(string $fieldName): string {
        if (isset($this->properties[$fieldName])) {
            /**@var ReflectionProperty $r */
            $r = $this->properties[$fieldName];
            $value = $r->isInitialized($this->formBean) ? $r->getValue($this->formBean) : '';

            /**@var ?TypeAttribute $typeAttribute */
            $typeAttribute = null;
            /**@var PropertyAttribute[] $propertyAttributes */
            $propertyAttributes = [];

            foreach ($r->getAttributes() as $attribute) {
                if (is_subclass_of($attribute->getName(), GenericFormAttribute::class)) {
//                    $attr = $this->getAttribute($attribute->getName());
                    /** @var GenericFormAttribute $instance */
                    $instance = $attribute->newInstance();

                    if ($instance instanceof TypeAttribute) {
                        $typeAttribute = $instance;
                    }
                    if ($instance instanceof PropertyAttribute) {
                        $propertyAttributes[] = $instance;
                    }
//                    echo $instance->output($value) . PHP_EOL;
//                    echo $attribute->getName() . PHP_EOL;
//                    echo $attribute->getTarget() . PHP_EOL;
//                    print_r($attribute->getArguments());
                }
            }

            if (!is_null($typeAttribute)) {
                echo $typeAttribute->output($fieldName, $value, $propertyAttributes);
            }
        }

        return '';
    }

    /**
     * @param array $data
     * @return object
     */
    public function asBean(array $data): object {
        foreach ($this->properties as $name => $attr) {
            if (isset($data[$name])) {
                $currentValue = trim($data[$name]);

                foreach ($attr->getAttributes() as $attribute) {
                    $instance = $attribute->newInstance();

                    if ($instance instanceof ConverterAttribute) {
                        $currentValue = $instance->convert($currentValue);
                    }
                }
                $attr->setValue($this->formBean, $currentValue);
            }
        }
        return $this->formBean;
    }

    /**
     * @param object $data
     * @return object
     */
    public function updateBean(object $data): object {
        foreach ($this->properties as $name => $attr) {
            if (isset($data->$name)) {
                $currentValue = trim($data->$name);

                foreach ($attr->getAttributes() as $attribute) {
                    $instance = $attribute->newInstance();

                    if ($instance instanceof ConverterAttribute) {
                        $currentValue = $instance->convert($currentValue);
                    }
                }
                $attr->setValue($this->formBean, $currentValue);
            }
        }
        return $this->formBean;
    }

    /**
     * @return object
     */
    public function formBean(): object {
        return $this->formBean;
    }

    /**
     * @return string[]
     */
    public function validation(): array {
        $errorMessages = [];

        $values = [];

        foreach ($this->properties as $fieldName => $r) {
            $value = $r->isInitialized($this->formBean) ? $r->getValue($this->formBean) : '';

            $values[$fieldName] = $value;

            foreach ($r->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();

                if ($instance instanceof ValidatableAttribute) {
                    $errorMessage = $instance->validate($value);
                    if (!empty($errorMessage)) {
                        $errorMessages[$fieldName] = $errorMessage;
                        break;
                    }
                }
            }
        }

        foreach ($this->properties as $fieldName => $r) {
            $value = $values[$fieldName];

            if (!isset($errorMessages[$fieldName])) {

                foreach ($r->getAttributes() as $attribute) {
                    $instance = $attribute->newInstance();

                    if ($instance instanceof AdvancedValidateAttribute) {
                        $errorMessage = $instance->advancedValidate($value, $values, $this->formBean, $this->ref);

                        if (!empty($errorMessage)) {
                            $errorMessages[$fieldName] = $errorMessage;
                            break;
                        }
                    }
                }
            }
        }

        $this->validationErrors = $errorMessages;
        return $errorMessages;
    }

    private function hasError(string $fieldName, callable $consumer) {
        if (isset($this->validationErrors[$fieldName])) {
            $consumer($this->validationErrors[$fieldName]);
        }
    }

    /**
     * @param string $fieldName
     * @param callable|null $consumer
     */
    public function html(string $fieldName, ?callable $consumer = null) {
        echo $this->htmlInputPart($fieldName);

        if (!is_null($consumer)) {
            $this->hasError($fieldName, $consumer);
        }
    }
}
