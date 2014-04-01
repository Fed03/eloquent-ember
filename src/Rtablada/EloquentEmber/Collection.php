<?php namespace Rtablada\EloquentEmber;

use Illuminate\Support\Collection as LaravelCollection;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
	public $modelKey;

	/**
	 * Create a new collection.
	 *
	 * @param  array  $items
	 * @return void
	 */
	public function __construct(array $items = array(), $sideloads, $modelKey)
	{
		$this->items = $items;
		$this->sideloads = $sideloads;
		$this->modelKey = $modelKey;
	}

	public function toEmberArray($relationsToLoad = array())
	{
		$modelKey = $this->getModelKey();

		$items = array();

		$relations = array();

		$this->each(function($model) use (&$relations)
		{
			$relations = array_merge_recursive($model->relationsToArray(), $relations);
		});

		$this->each(function($model) use (&$items, $relationsToLoad)
		{
			$items[] = $model->toEmberArray(false, $relationsToLoad);
		});

		$array = array($modelKey => $items);

		return array_merge($array, $relations);
	}

	public function getModelKey()
	{
		return str_plural($this->modelKey);
	}

	public function merge($collection, $sideloads = false, $modelKey = false)
	{
		$sideloads = $sideloads ?: $this->sideloads;
		$modelKey = $modelKey ?: $this->modelKey;

		$dictionary = $this->getDictionary($this);

		foreach ($collection as $item)
		{
			$dictionary[$item->getKey()] = $item;
		}

		return new static(array_values($dictionary), $sideloads, $modelKey);
	}
}
