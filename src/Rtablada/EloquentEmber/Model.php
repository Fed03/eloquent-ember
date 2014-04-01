<?php namespace Rtablada\EloquentEmber;

class Model extends \Illuminate\Database\Eloquent\Model
{
	protected $withIds = array();

	public function toEmberArray($relationsToLoad = array(), $withWrap = true)
	{
		$relations = array_intersect(array_keys($this->relations), $relationsToLoad);

		$emberRelations = array();
		foreach ($relations as $relation) {
			$collection = $this->$relation;
			// If Plural
			if (substr($relation, -1) === 's') {
				$emberRelations[$relation] = $collection->modelKeys();
			} else {
				$emberRelations[$relation] = $collection->getKey();
			}
		}


		if (!$withWrap) {
			return array_merge($this->removeRelations($relations), $emberRelations);
		} else {
			$sideloaded = $this->relationsToArray();
			return $this->sideloadRelated($relations, $sideloaded, $emberRelations);
		}
	}

	public function sideloadRelated($relations, $sideloaded, $emberRelations)
	{
		$array = array($this->getModelKey() => array_merge($this->removeRelations($relations), $emberRelations));

		return array_merge($array, $sideloaded);
	}

	public function removeRelations($relations)
	{
		$array = $this->toArray();

		foreach ($relations as $relation) {
			unset($array[snake_case($relation)]);
		}

		return $array;
	}

	public function newCollection(array $models = array())
	{
		return new Collection($models, $this->withIds, $this->getModelKey());
	}

	public function toArrayWithRelations()
	{
		return parent::toArray();
	}

	public function getModelKey()
	{
		return str_replace('\\', '', snake_case(class_basename($this)));
	}
}
