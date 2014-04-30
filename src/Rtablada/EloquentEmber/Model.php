<?php namespace Rtablada\EloquentEmber;

class Model extends \Illuminate\Database\Eloquent\Model
{
	public function toEmberArray($withWrap = true)
	{
		$relations = array_keys($this->relations);

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

		$tmp = array();
		foreach ($relations as $relation) {
			if (substr($relation, -1) === 's') {
				$tmp[$relation] = $sideloaded[snake_case($relation)];
			} else {
				$tmp[str_plural($relation)] = array(
					$sideloaded[snake_case($relation)]
				);
			}
		}
		return array_merge($array, $tmp);
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
