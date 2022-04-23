<?php

namespace ChrisKonnertz\DeepLy\Models;

/**
 * Abstract base class for all model classes.
 * The data for the model properties ahs to be passed when the constructor of the inheriting class is called.
 */
abstract class Model
{

    /**
     * Maps property names from the original API result to something else.
     * Override this in the inheriting class.
     */
    const PROPERTY_MAPPINGS = [];

    /**
     * @param \stdClass $data Data values that should be stored in the class properties
     */
    public function __construct(\stdClass $data)
    {
        $this->hydrate($data);
    }

    /**
     * Stores the given data in the properties of the class
     *
     * @param \stdClass $data  Data values that should be stored in the class properties
     * @return void
     */
    public function hydrate(\stdClass $data)
    {
        foreach ($data as $key => $value) {
            // Rename keys
            if (isset(static::PROPERTY_MAPPINGS[$key])) {
                $key = static::PROPERTY_MAPPINGS[$key];
            }

            // Convert to camel case
            $key = str_replace('_', '', lcfirst(ucwords($key, '_')));

            // Store value in class property
            $this->$key = $value;
        }

        $this->enrich();
    }

    /**
     * Method to be implemented in the inheriting class, if necessary.
     * This is where the inheriting class can do additional data transformation.
     */
    protected function enrich() {
        // To be implemented in the inheriting class, if necessary
    }

}