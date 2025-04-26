<?php

namespace Tests\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class BaseFactory {

    abstract public function getModelClassName() : string;

    /**
     * The modal which was just created
     *
     * @var Model
     */
    protected $createdModal;
    /**
     * The modal which was just made
     *
     * @var Model
     */
    protected $madeModal;

    function created()
    {
        return $this->createdModal;
    }
    function made()
    {
        return $this->madeModal;
    }

    function getModel() : Model
    {
        return new($this->getModelClassName());
    }

    function create($overrides= [])
    {
        $this->createdModal = $this->getModel()->factory()->create($overrides);
        $this->createdModal->refresh();
        return $this->createdModal;
    }
    function make($overrides = [])
    {
        $this->madeModal = $this->getModel()->factory()->make($overrides);
        return $this->madeModal;
    }
    function creates(int $number,$overrides = [])
    {
        $models = [];
        for($i=0;$i<$number;$i++)
        {
            $models[] = $this->getModel()->factory()->create($overrides);
        }
        return $models;
    }
    function makes(int $number,$overrides = [])
    {
        $models = [];
        for($i=0;$i<$number;$i++)
        {
            $models[] = $this->getModel()->factory()->make($overrides);
        }
        return $models;
    }

}
