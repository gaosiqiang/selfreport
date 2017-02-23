<?php
class CacheHttpSession extends CCacheHttpSession {
    
    const _PREFIX = '_dc_session:';
    
    private $_cache;
    
    /**
     * Initializes the application component.
     * This method overrides the parent implementation by checking if cache is available.
     */
    public function init()
    {
        $this->_cache=Yii::app()->getComponent($this->cacheID);
        if(!($this->_cache instanceof ICache))
            throw new CException(Yii::t('yii','CacheHttpSession.cacheID is invalid. Please make sure "{id}" refers to a valid cache application component.',
                    array('{id}'=>$this->cacheID)));
        parent::init();
    }
    
    /**
     * Session read handler.
     * Do not call this method directly.
     * @param string $id session ID
     * @return string the session data
     */
    public function readSession($id)
    {
        $data=$this->_cache->executeCommand('GET',array(self::_PREFIX.$id));
        return $data===false?'':$data;
    }
    
    /**
     * Session write handler.
     * Do not call this method directly.
     * @param string $id session ID
     * @param string $data session data
     * @return boolean whether session write is successful
     */
    public function writeSession($id,$data)
    {
        return $this->_cache->executeCommand('SETEX',array(self::_PREFIX.$id,$this->getTimeout(),$data));
    }
    
    /**
     * Session destroy handler.
     * Do not call this method directly.
     * @param string $id session ID
     * @return boolean whether session is destroyed successfully
     */
    public function destroySession($id)
    {
        return $this->_cache->executeCommand('DEL',array(self::_PREFIX.$id));
    }
    
}

?>