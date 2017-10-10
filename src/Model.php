<?php

namespace SW\Notebook;

use Illuminate\Database\Eloquent\Model as BaseModel;

use SW\Notebook\Relations\BelongsToManyAll;

class Model extends BaseModel
{
    /**
     * The mutators for attributes.
     *
     * @var array
     */
    protected $mutators = [];

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);

        //Some database values are store as different types that we would like.
        //Check to see if any mutators are registered and if so mutate the
        //value from the database.
        $value = $this->decodeAttributeValue($key,$value);

        return $value;
    }

    /**
     * Mutate the value into a state where it can be used outside the 
     * database.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return mixed
     */
    protected function decodeAttributeValue($key,$value)
    {
        if(isset($this->mutators[$key]))
        {
            $mutator = new $this->mutators[$key];

            return $mutator->decode($value);
        }

        return $value;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        //The type of a value used in the app may be different then how it is 
        //stored in the database. Therefore, mutate it to a value to be stored
        //in the database.
        $value = $this->encodeAttributeValue($key,$value);

        return parent::setAttribute($key, $value);
    }
    
    /**
     * Mutate the value into a state where it can be stored in the database.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return mixed
     */
    protected function encodeAttributeValue($key,$value)
    {
        if(isset($this->mutators[$key]))
        {
            $mutator = new $this->mutators[$key];

            return $mutator->encode($value);
        }

        return $value;
    }
    
    /**
     * Define a many-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $relatedKey
     * @return \SW\Notebook\Relations\BelongsToManyAll
     */
    public function belongsToManyAll($related, $table = null, $foreignKey = null, $relatedKey = null)
    {
        // First, we'll need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we'll make the query
        // instances as well as the relationship instances we need for this.
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $relatedKey = $relatedKey ?: $instance->getForeignKey();

        // If no table name was provided, we can guess it by concatenating the two
        // models using underscores in alphabetical order. The two model names
        // are transformed to snake case from their default CamelCase also.
        if (is_null($table)) {
            $table = $this->joiningTable($related);
        }

        return new BelongsToManyAll(
            $instance->newQuery(), $this, $table, $foreignKey, $relatedKey
        );
    }
}
