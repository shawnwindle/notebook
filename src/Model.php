<?php

namespace SW\Notebook;

use Illuminate\Database\Eloquent\Model as BaseModel;

use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Special relationship to return all instances even if not assigned.
     *
     * @param  string  $related
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    protected function hasManyAll($related)
    {
        $builder = call_user_func([$related, 'whereNotNull'], $this->primaryKey)
            ->orWhereNotNull($this->primaryKey);

        return new HasMany($builder,$this,$this->primaryKey,$this->primaryKey);
    }

    /**
     * Special relationship to return all instances even if not assigned.
     *
     * @param  string  $related
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    protected function belongsToManyAll($related)
    {
        return $this->hasManyAll($related);
    }
}
