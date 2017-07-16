This package provides additional features to Laravel's Eloquent ORM.

1) Global mutators

Eloquent provides a mutator to convert datetime's stored in the database 
to Carbon\Carbon instances. It also includes a way to mutate data being 
fetched and stored in the model (by getTaxAttribute() and setTaxAttribute()
methods), but they must be defined on the model and cannot be easily added
dynamically because PHP does not support adding methods to a class at runtime.

If you had a mutator that needed to be on multiple attributes on several 
models, you could define a trait but it would get messy after a while.
For example, lets say you wanted to mutate several values in multiple models 
in the database to and from a Money class. You could create a trait:

trait MoneyMutator
{
    private function getMoneyAttribute($value)
    {
        //convert Money object back to value
    }

    private function setMoneyAttribute($value)
    {
        //convert value to Money object
    }

    //you would need to define the following two methods
    //for every attribute
    public function getTaxAttribute($value)
    {
        return $this->getMoneyAttribute($value);
    }

    public function setTaxAttribute($value)
    {
        $this->setMoneyAttribute($value);
    }
    
}

Lets say you are coding an E-Commerce application and used money values 
everywhere. The trait above would explode to dozens of copies of methods
like getTaxAttribute() and setTaxAttribute with the same body. Global 
mutators were added to overcome this problem.

Just extend SW\Notebook\Model (instead of Illuminate\Database\Eloquent\Model
and add a reference to your mutator class in the mutators field:

class OrderTotals extends SW\Notebook\Model
{
    protected $mutators = [
        'county_tax' => 'namespace/to/MoneyMutator',
        'state_tax' => 'namespace/to/MoneyMutator',
        'subtotal' => 'namespace/to/MoneyMutator',
        'total' => 'namespace/to/MoneyMutator'
    ];
}

class MoneyMutator extends SW\Notebook\Mutator
{
    public function encode($value)
    {
        //convert value to Money object
    }

    public function decode($value)
    {
        //convert Money object back to value
    }
}

By following the method detailed above, you only write the code once and it
can be applied to many different attributes, even across models.

2) Short circuit one-to-many and many-to-many

In some circumstances, you may want a relationship to return all instead of just what's in the database.

Say, for example, you had permissions and permission groups in your project. Some permission groups (i.e. 
Administrators) should always get all permissions no matter what was set in the UI (reflected by what's in
the database).

The relationship would do something like:

public function permissions()
{
    if(permission group is Administrator)
        return $this->belongsToManyAll(permissions model string);
    return $this->belongsToMany(fields like normal);
}
