<?php
class SqlDataProvider extends CSqlDataProvider
{
    /* (non-PHPdoc)
     * @see CSqlDataProvider::fetchKeys()
     */
    protected function fetchKeys() {
        $keys=array();
        if($data=$this->getData())
        {
            if($this->keyField===false)
                $keys = array_keys($data);    //重写fetchKeys()就是为了这里，如果配置keyField为false时，将结果集的键赋予$keys，适用于无法确定keyField的情况，参见CArrayDataProvider
            else {
                if(is_object(reset($data)))
                    foreach($data as $i=>$item)
                    $keys[$i]=$item->{$this->keyField};
                else
                    foreach($data as $i=>$item)
                    $keys[$i]=$item[$this->keyField];
            }
        }
        return $keys;
    }
}