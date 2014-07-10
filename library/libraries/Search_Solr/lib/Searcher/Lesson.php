<?php

require_once dirname(__FILE__) . '/Abstract.php';

class Searcher_Lesson extends Searcher_Abstract
{

    /**
     * 
     * @param Array $query = array('keyword' => '', 'subject_id' => '', 'category_id' => '', 'doc_type' => '', 'doc_type_new' => '')
     * @param int $page
     * @param int $limit
     * @param string $sort = 'score desc, id desc'
     * @return boolean
     */
    public function search(Array $query = array(), $page = 1, $limit = 10, $sort = 'score desc, id desc')
    {
        $result = $this->search_field($query,$page,$limit,$sort,'file_name');
        if($result['result']){
            return $result;
        }
        $result = $this->search_field($query,$page,$limit,$sort,'category_text');
        if($result['result']){
            return $result;
        }
        $result = $this->search_field($query,$page,$limit,$sort,'file_content');
        if($result['result']){
            return $result;
        }
    }

    public function search_field(Array $query = array(), $page = 1, $limit = 10, $sort = 'score desc, id desc',$field = 'file_name'){
        $page = ($page >= 1) ? intval($page) : 1;
        $limit = $limit ? intval($limit) : 10;
        $start = $limit * ($page - 1);
        $cond = $this->_getQuery($query,$field);
        $params = array();
        if ($sort) {
            $params['sort'] = $sort;
        }
        if ($cond['filterQuery']) {
            $params['fq'] = $cond['filterQuery'];
        }
        try {
            $exec_time = microtime(true);
            $result = $this->_getClient()->search($cond['keyword'], $start, $limit, $params);
            $exec_time = microtime(true) - $exec_time;
        } catch (Exception $ex) {
            return false;
        }
        $result = $result->response;
        $docs = array();
        if ($result->docs) {
            foreach ($result->docs as $doc) {
                if (is_array($doc->subject_id)) {
                    $doc->subject_id = $doc->subject_id[0];
                }
                //转换category_name 和 category_id
                if (isset($query['category_id']) AND $query['category_id']) {
                    $doc->category_id = $query['category_id'];
                } else if (is_array($doc->category_id)) {
                    $doc->category_id = $doc->category_id[0];
                }
                $courseNames = unserialize($doc->category_name);
                $doc->category_name = $courseNames[$doc->category_id]['name'];
                $doc->category_id = $courseNames[$doc->category_id]['id'];
                //补全字段.
                $doc->file_content = '';
            }
        }
        $return = array(
            'cond' => $cond,
            'params' => $params,
            'total' => $result->numFound,
            'result' => $result->docs,
            'exec_time' => $exec_time,
        );
        return $return;

    }
    /**
     * 添加索引，如果连续添加，请将$withCommit 设置为false, 否则严重影响效率
     * @param Apache_Solr_Document $document
     * @param boolen $withCommit
     * @return boolean
     */
    public function add(Apache_Solr_Document $document, $withCommit = true)
    {
        try {
            $solr = $this->_getClient();
            $result = $solr->addDocument($document);
            if ($withCommit) {
                $solr->commit();
            }
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    protected function _getClient()
    {
        return parent::_getClient('lesson');
    }

    /**
     * 构建搜索语句
     * @param Array $query = array('keyword' => '', 'subject_id' => '', 'category_id' => '', 'doc_type' => '', 'doc_type_new' => '')
     * @return string
     */
    private function _getQuery($query = array(),$field='file_name')
    {
        $filterQuery = array();
        if (!isset($query['status'])) {
            $filterQuery[] = 'status:1';
        } else {
            $filterQuery[] = 'status:' . intval($query['status']);
        }
        if (isset($query['subject_id']) AND $query['subject_id']) {
            $filterQuery[] = 'subject_id:' . intval($query['subject_id']);
        }
        if (isset($query['category_id']) AND $query['category_id']) {
            $filterQuery[] = 'category_id:' . intval($query['category_id']);
        }else{
            $filterQuery[] = 'category_id:[1 TO *]';
        }
        if (isset($query['doc_type']) AND is_array($query['doc_type'])) {
            $doc_type_arr = array();
            foreach($query['doc_type'] as $doc_type){
                $doc_type_arr[] = 'doc_type:' . intval($doc_type);
            }
            $doc_type_str = implode(' or ',$doc_type_arr);
            $filterQuery[] = $doc_type_str;
        } elseif (isset($query['doc_type']) AND $query['doc_type']) {
            $filterQuery[] = 'doc_type:' . intval($query['doc_type']);
        }
        if (isset($query['doc_type_new']) AND $query['doc_type_new']) {
            $filterQuery[] = 'doc_type_new:' . intval($query['doc_type_new']);
        }
        $keyword = '*:*';
        if (isset($query['keyword']) AND $query['keyword']) {
            $keyword = trim($query['keyword']);
            //@hack 如果关键词不完全是数字字母和中文，则启用完全匹配 （加双引号）
            $ret = preg_match("/[a-zA-Z0-9\x{4e00}-\x{9fa5}]+/u", $keyword);
            if (!$ret) {
                $keyword = '"' . addslashes($keyword) . '"';
            }
            $keyword = str_replace(' ', ' AND ', $keyword);
            $keyword = "$field:(' . $keyword . ')";
        }
        $data = array(
            'filterQuery' => $filterQuery,
            'keyword' => $keyword,
        );
        return $data;
    }

}
