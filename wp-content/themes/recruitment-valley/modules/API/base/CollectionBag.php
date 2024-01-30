<?php


namespace MI\Base\Bag;

use DateTimeImmutable;
use Exception;
use Throwable;
use WP_Error;

class CollectionBag
{
    protected $acf;
    protected $child;
    protected $data;

    public function __construct(array $data, array $child = [])
    {
        $this->data     = $data;
        if (!empty($child)) {
            $this->createProperty($child);
        } else {
            $this->createProperty($data);
        }
    }

    /**
     * Create a collection function
     *
     * @param array $data
     * @return self
     */
    public static function collect(array $data): self
    {
        return new self($data);
    }

    /**
     * Create property based on data function
     *
     * the property name would be nested array child acf key
     *
     * @param Array $properties
     * @return void
     */
    private function createProperty(array $properties)
    {
        foreach ($properties as $name => $value) {
            /** {} is for variable variables.
             * mean to use variable value as another variable name.
             */
            $this->{$name} = $value;
        }
    }

    /**
     * Get first data from nested array data function
     *
     * @param String $key
     * @return mixed : either self object or data from nested array
     */
    public function first(String $key = null): mixed
    {
        if ($this->data) {
            if (!empty($key)) {
                switch ($key) {
                    case 'all':
                        return end($this->data[0]);
                        break;
                    default:
                        return $this->data[0][$key];
                }
            }
            return new self($this->data[0]);
        } else {
            return new self([]);
        }
    }

    /**
     * Get last data from nested array data function
     *
     * @param String $key
     * @return mixed : either self object or data from nested array
     */
    public function last(String $key = null): mixed
    {
        if ($this->data) {
            if (!empty($key)) {
                switch ($key) {
                    case 'all':
                        return end($this->data);
                        break;
                    default:
                        return end($this->data)[$key];
                        break;
                }
            }
            return new self(end($this->data));
        } else {
            return new self([]);
        }
    }

    /**
     * Get spesific child data from nested array data function
     *
     * @param String $key
     * @return self : data from nested array
     */
    public function child(String $key = ''): self
    {
        if (array_key_exists($key, $this->data)) {
            if (is_array($this->data[$key])) {
                return new self($this->data[$key]);
            } else {
                return new self([$this->data[$key]]);
            }
        } else {
            return new self([]);
        }
    }

    /**
     * Get spesific data from nested array data function
     *
     * @param String $key
     * @return mixed : either self object or data from nested array
     */
    public function get(Mixed $key): mixed
    {
        if (!empty($key)) {
            switch ($key) {
                case 'all':
                    return $this->data;
                    break;
                default:
                    return $this->data[$key];
            }
        }
        return new self($this->data[$key]);
    }

    /**
     * Find first nested array data based on child value function
     *
     * @param Mixed $key
     * @param Mixed $value
     * @param boolean $strict
     * @return self
     */
    public function find(Mixed $key, Mixed $value, Bool $strict = true): self
    {
        if (is_array($this->data)) {
            for ($i = 0; $i < count($this->data); $i++) {
                if (array_is_list($this->data)) {
                    if ($strict) {
                        if ($this->data[$i][$key] === $value) {
                            return new self($this->data[$i]);
                        }
                    } else {
                        if ($this->data[$i][$key] == $value) {
                            return new self($this->data[$i]);
                        }
                    }
                } else {
                    if ($strict) {
                        if ($this->data[$key] === $value) {
                            return new self($this->data);
                        }
                    } else {
                        if ($this->data[$key] == $value) {
                            return new self($this->data);
                        }
                    }
                }
            }
        } else {
            throw new Exception('Data must be array');
        }
    }

    /**
     * Filter nested array data bse on child value function
     *
     * @param Mixed $key
     * @param Mixed $value
     * @param boolean $strict
     * @return self
     */
    public function filter(Mixed $key, Mixed $value, Bool $strict = true): self
    {
        if (is_array($this->data)) {
            $filteredData = [];
            for ($i = 0; $i < count($this->data); $i++) {
                if (array_is_list($this->data)) {
                    if (array_key_exists($key, $this->data[$i])) {
                        if ($strict) {
                            if ($this->data[$i][$key] === $value) {
                                $filteredData[] = $this->data[$i];
                                continue;
                            }
                        } else {
                            if ($this->data[$i][$key] == $value) {
                                $filteredData[] = $this->data[$i];
                                continue;
                            }
                        }
                    } else {
                        continue;
                    }
                } else {
                    if (array_key_exists($key, $this->data)) {
                        if ($strict) {
                            if ($this->data[$key] === $value) {
                                $filteredData[] = $this->data;
                                continue;
                            }
                        } else {
                            if ($this->data[$key] == $value) {
                                $filteredData[] = $this->data;
                                continue;
                            }
                        }
                    } else {
                        continue;
                    }
                }
            }

            return new self($filteredData);
        } else {
            throw new Exception('Data must be array');
        }
    }

    /**
     * Get keys only function
     *
     * @return self
     */
    public function keys(): self
    {
        if ($this->data) {
            return new self(array_keys($this->data));
        } else {
            return new self([]);
        }
    }

    /**
     * Get values only function
     *
     * @return self
     */
    public function values(): self
    {
        if ($this->data) {
            return new self(array_values($this->data));
        } else {
            return new self([]);
        }
    }

    /**
     * Get value of specific grandchild key function
     *
     * @param Mixed $key
     * @return self
     */
    public function column(Mixed $key): self
    {
        if ($this->data) {
            return new self(array_column($this->data, $key));
        } else {
            return new self([]);
        }
    }

    /**
     * Sort  function
     *
     * @param Mixed $key
     * @param string $sort
     * @return self
     */
    public function sort(Mixed $key, String $sort = 'asc'): self
    {
        if (array_is_list($this->data)) {
            // Get key values to sort
            $array = array_column($this->data, $key);

            // Sort key array and data
            switch (strtolower($sort)) {
                case 'asc':
                case 'ascending';
                    array_multisort($array, SORT_ASC, $this->data);
                    break;
                case 'desc':
                case 'descending':
                default:
                    array_multisort($array, SORT_DESC, $this->data);
                    break;
            }

            return new self($this->data);
        } else {
            throw new Exception('Data must be array list');
        }
    }

    /**
     * Remap Array function
     *
     * @param array $mapper
     * @return self
     */
    public function remap(array $mapper): self
    {
        $newValues = [];
        if (array_is_list($this->data)) {
            foreach ($this->data as $value) {
                $mappedValue = [];
                foreach ($mapper as $key => $valueKey) {
                    if (is_bool($value[$valueKey])) {
                        $mappedValue[$key] = $value[$valueKey] ? 1 : 0;
                    } else {
                        $mappedValue[$key] = $value[$valueKey];
                    }
                }
                $newValues[] = $mappedValue;
            }
        } else {
            $mappedValue = [];
            foreach ($mapper as $key => $valueKey) {
                if (is_bool($this->data[$valueKey])) {
                    $mappedValue[$key] = $this->data[$valueKey] ? 1 : 0;
                } else {
                    $mappedValue[$key] = $this->data[$valueKey];
                }
            }
            $newValues[] = $mappedValue;
        }

        return new self($newValues);
    }

    /**
     * Remap child Array function
     *
     * @param array $mapper
     * @return self
     */
    public function remapChild(string $child, array $mapper): self
    {
        $newValues = [];

        if (array_is_list($this->data)) {
            for ($i = 0; $i < count($this->data); $i++) {
                if (array_key_exists($child, $this->data[$i])) {
                    $mappedValue = [];
                    foreach ($mapper as $key => $valueKey) {
                        $mappedValue[$key] = $this->data[$i][$child][$valueKey];
                    }
                    $this->data[$i][$child] = $mappedValue;
                }
            }
        } else {
            $mappedValue = [];
            foreach ($mapper as $key => $valueKey) {
                if (array_key_exists($child, $this->data)) {
                    $mappedValue[$key] = $this->data[$child][$valueKey];
                }
            }
            $this->data[$child] = $mappedValue;
        }

        return new self($this->data);
    }

    /**
     * Convert Array Value function
     *
     * @param array $mapper
     * @return self
     */
    public function convert(array $mapper): self
    {
        $newValues = [];
        if (array_is_list($this->data)) {
            foreach ($this->data as $value) {
                $mappedValue = [];
                foreach ($mapper as $key => $valueConvert) {
                    switch (true) {
                        case (in_array($valueConvert, ['bool', 'boolean', 'truefalse', 'trusyfalsy'])):
                            $mappedValue[$key] = (bool) $value[$key] == 1 ? true : false;
                            break;
                        case (strtolower($valueConvert) == 'unserialize'):
                            $mappedValue[$key] = $this->unserialize($value[$key]);
                            break;
                        case (strtolower($valueConvert) == 'serialize'):
                            $mappedValue[$key] = $this->serialize($value[$key]);
                            break;
                        case (in_array(strtolower($valueConvert), ['int', 'integer', 'float', 'date'])):
                            $mappedValue[$key] = $this->cast(strtolower($valueConvert), $value[$key]);
                            break;
                        case (strpos($valueConvert, ':')):
                            $convertion = explode(':', $valueConvert, 2);
                            $mappedValue[$key] = $this->convertion($convertion, $value[$key]);
                            break;
                        default:
                            $mappedValue[$key] = $value[$key];
                            break;
                    }
                }
                $newValues[] = $mappedValue;
            }
        } else {
            $mappedValue = [];
            foreach ($mapper as $key => $valueConvert) {
                switch (true) {
                    case (in_array($valueConvert, ['bool', 'boolean', 'truefalse', 'trusyfalsy'])):
                        // $mappedValue[$key] = $this->data[$key] == 1 ? true : false;
                        $mappedValue[$key] = boolval($this->data[$key] == 1);
                        break;
                    case (strtolower($valueConvert) == 'unserialize'):
                        $mappedValue[$key] = $this->unserialize($this->data[$key]);
                        break;
                    case (strtolower($valueConvert) == 'serialize'):
                        $mappedValue[$key] = $this->serialize($this->data[$key]);
                        break;
                    case (in_array(strtolower($valueConvert), ['int', 'integer', 'float', 'date'])):
                        $mappedValue[$key] = $this->cast(strtolower($valueConvert), $this->data[$key]);
                        break;
                    case (strpos($valueConvert, ':')):
                        $convertion = explode(':', $valueConvert);
                        $mappedValue[$key] = $this->convertion($convertion, $this->data[$key]);
                        break;
                    default:
                        $mappedValue[$key] = $this->data[$key];
                        break;
                }
            }
            $newValues[] = $mappedValue;
        }

        return new self($newValues);
    }

    /**
     * Get data as array function
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Get data as json function
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->data);
    }

    public function toString($separator = ','): string
    {
        switch (true) {
            case is_array($this->data):
                return implode($separator, $this->data);
                break;
            case is_string($this->data):
                return $this->data;
                break;
            default:
                $data = $this->toArray();
                return implode($separator, $data);
                break;
        }
    }

    /**
     * Cast function
     *
     * Cast value to another format
     *
     * @param [type] $type
     * @param [type] $value
     * @return void
     */
    private function cast($type, $value)
    {
        switch (strtolower($type)) {
            case 'int':
            case 'integer':
                return $this->castInteger($value);
        }
    }

    /**
     * cast value to integer function
     *
     * @param Mixed $value
     * @return mixed
     */
    private function castInteger(Mixed $value): mixed
    {
        if (is_numeric($value)) {
            return (int)$value;
        } else {
            return $value;
        }
    }

    /**
     * Unserialize value function
     *
     * Unserialize a value.
     *
     * @param Mixed $value
     * @return mixed
     */
    public function unserialize(Mixed $value): mixed
    {
        if (function_exists('maybe_unserialize')) {
            return maybe_unserialize($value);
        }
    }

    /**
     * Serialize value function
     *
     * Serialize a value.
     *
     * @param Mixed $value
     * @return mixed
     */
    public function serialize(Mixed $value): mixed
    {
        if (function_exists('maybe_serialize')) {
            return maybe_serialize($value);
        }
    }

    public function convertion(array $rule, Mixed $value): mixed
    {
        switch ($rule[0]) {
            case '+':
            case 'add':
            case 'addition':
                if (is_numeric($value)) {
                    if (is_numeric($rule[1])) {
                        return $value + (int)$rule[1];
                    } else {
                        return $value . $rule[1];
                    }
                } else if (is_string($value)) {
                    return $value . $rule[1];
                }
                break;
            case 'date':
            case 'datetime':
                $parameters = explode(',', $rule[1]);

                $fromFormat = trim($parameters[0]);
                $toFormat   = isset($parameters[1]) ? trim($parameters[1]) : 'Y-m-d H:i:s';

                $date = DateTimeImmutable::createFromFormat($fromFormat, $value);
                return $date->format($toFormat);
                break;
            default:
                return $value;
        }
    }
}
