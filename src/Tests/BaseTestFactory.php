<?php

namespace NoamanAhmed\ApiCrudGenerator\Transformers\Tests;

use Illuminate\Database\Eloquent\Model;

abstract class BaseFactory
{
    abstract public function getModelClassName(): string;

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

    public function created()
    {
        return $this->createdModal;
    }

    public function made()
    {
        return $this->madeModal;
    }

    public function getModel(): Model
    {
        return new ($this->getModelClassName());
    }

    public function create($overrides = [])
    {
        $this->createdModal = $this->getModelClassName()::factory()->create($overrides);
        $this->createdModal->refresh();

        return $this->createdModal;
    }

    public function make($overrides = [])
    {
        $this->madeModal = $this->getModelClassName()::factory()->make($overrides);

        return $this->madeModal;
    }

    public function creates(int $number, $overrides = [])
    {
        $models = [];
        for ($i = 0; $i < $number; $i++) {
            $models[] = $this->getModelClassName()::factory()->create($overrides);
        }

        return $models;
    }

    public function makes(int $number, $overrides = [])
    {
        $models = [];
        for ($i = 0; $i < $number; $i++) {
            $models[] = $this->getModelClassName()::factory()->make($overrides);
        }

        return $models;
    }
}
