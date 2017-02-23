<?php
class StatePersister extends CApplicationComponent implements IStatePersister
{
    /**
     * @var string the ID of the cache application component that is used to cache the state values.
     * Defaults to 'cache' which refers to the primary cache application component.
     * Set this property to false if you want to disable caching state values.
     */
    public $cacheID='cache';

    /**
     * Initializes the component.
     * This method overrides the parent implementation by making sure {@link stateFile}
     * contains valid value.
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Loads state data from persistent storage.
     * @return mixed state data. Null if no state data available.
     */
    public function load()
    {
        if($this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
        {
            //$cacheKey='_DC_StatePersister';
            //if(($value=$cache->get($cacheKey))!==false)
            $cacheKey='_dc_statepersister'.":".md5(Utility::getRealIP().Yii::app()->request->userAgent);
            if(($value=$cache->executeCommand('GET',array($cacheKey)))!==false)
                return unserialize($value);
            else
                return null;
        }
        else
            return null;
    }

    /**
     * Saves application state in persistent storage.
     * @param mixed $state state data (cache use redis).
     */
    public function save($state)
    {
        if($this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
        {
            $cacheKey='_dc_statepersister'.":".md5(Utility::getRealIP().Yii::app()->request->userAgent);
            $cache->executeCommand('SETEX',array($cacheKey, 43200, serialize($state)));
            //$cacheKey='_DC_StatePersister';
            //$cache->set($cacheKey,$state);
        }
    }
}
