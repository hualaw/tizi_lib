<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(dirname(dirname(__FILE__)) . '/Apache/Solr/Service.php');

abstract class Searcher_Abstract
{
    /**
     * 执行搜索
     */
    abstract function search(Array $query = array(), $page = 1, $limit = 10, $sort = null);

    /**
     * 添加索引
     */
    abstract function add(Apache_Solr_Document $document, $withCommit = true);

    /**
     * 更新索引，暂未实现
     */
    public function update()
    {
        
    }

    /**
     * 删除单个索引， 注意 $this->_getClient() 是子类中的 _getClient()
     * @param type $id
     * @param type $withCommit
     * @return type
     */
    public function delete($id, $withCommit = true)
    {
        $solr = $this->_getClient();
        $result = $solr->deleteByQuery('id:' . $id);
        if ($withCommit) {
            $solr->commit();
        }
        return $result;
    }

    /**
     * 执行commit， 注意 $this->_getClient() 是子类中的 _getClient()
     * @return type
     */
    public function commit()
    {
        return $this->_getClient()->commit();
    }

    /**
     * 清空所有索引， 注意 $this->_getClient() 是子类中的 _getClient()
     * @return type
     */
    public function flush()
    {
        $solr = $this->_getClient();
        $result = $solr->deleteByQuery('*:*');
        $solr->commit();
        return $result;
    }

    /**
     * 会被子类中的方法覆盖
     * @param type $filename
     * @return \Apache_Solr_Service
     */
    protected function _getClient($filename)
    {
        $file_path = APPPATH .'config/' . ENVIRONMENT . '/solr.php';
        if (file_exists($file_path)) include($file_path);
        else exit('The configuration file solr.php does not exist.');
        
        $conf = $config['solr'][$filename];
        $solr = new Apache_Solr_Service($conf['host'], $conf['port'], $conf['path']);
        $solr->setCollapseSingleValueArrays(false);
        return $solr;
    }

}
