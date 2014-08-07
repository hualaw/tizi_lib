<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

class v9_news_model extends MY_Model{
	
	const  PARENTS_ARTICLE_CATID=13;
	
	 var $catids = array(13,14,16,30,31,32,33,22,23,24,25,26,27,28,35,36,37,34);
	
	public function replace($data){
		$this->db->query("replace into v9_news(id,catid,title,thumb,description,content,listorder,
			status,islink,url,username,inputtime,updatetime,reports,copyfrom) 
			values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", array($data["id"], $data["catid"], $data["title"], 
			$data["thumb"], $data["description"], $data["content"], $data["listorder"], 
			$data["status"], $data["islink"], $data["url"], $data["username"], $data["inputtime"], 
			$data["updatetime"], $data["reports"], $data["copyfrom"]));
        $news_id = $data["id"];
        if (!empty($data['tags'])) {
            $tags = array();
            foreach ($data['tags'] as $tag_name) {
                $tag_id = $this->create_tag($tag_name);
                if (empty($tag_id)) {
                    continue;
                }
                $tags[ $tag_id ] = $tag_name;
            }
            $this->add_news_tags($news_id, $tags);
        }
		return $this->db->affected_rows();
	}
    
    public function create_tag($tag) {
        $news_tag = trim(filter_var($tag, FILTER_SANITIZE_STRING));
        if (empty($news_tag)) {
            return false;
        }
		$res = $this->db->query("select id,tag_name from v9_tags where tag_name='" . $news_tag . "'");
        $tags = $res->result_array();
        if (empty($tags)) {
            $res = $this->db->query("insert v9_tags(id, tag_name) value('','" . $news_tag . "')");
            return $this->db->insert_id();
        }else{
            return $tags[0]['id'];
        }
    }
    
    public function get_news_tags($news_id) {
		$res = $this->db->query("select id,tag_id,tag_name from v9_news_tags where news_id =" . $news_id);
        $rows = $res->result_array();
        $news_tags = array();
        foreach($rows as $key => $value) {
            $news_tags[ $value['tag_id'] ] = $value['tag_name'];
        }
        return $news_tags;
    }
    
    // new_tags 新的tag。 添加新的tag
    public function add_news_tags($news_id, $new_tags) {
        $old_tags = $this->get_news_tags($news_id);
        $add_tag_ids = array();
        $delete_tag_ids = array();
        foreach($new_tags as $tag_id => $tag_name) {
            if (!isset( $old_tags[$tag_id] )) {
                $add_tag_ids[] = $tag_id;
            }
        }
        foreach($old_tags as $tag_id => $tag_name) {
            if (!isset( $new_tags[$tag_id] )) {
                $delete_tag_ids[] = $tag_id;
            }
        }
        if (!empty($add_tag_ids)) {
            foreach($add_tag_ids as $tag_id ) {
                $res = $this->db->query("insert v9_news_tags(id, news_id, tag_id, tag_name) 
                    values('', {$news_id}, {$tag_id}, '{$new_tags[$tag_id]}')");
            }   
        }
        if (!empty($delete_tag_ids)) {
            $res = $this->db->query("delete from v9_news_tags where news_id = {$news_id} and tag_id in (" . implode(',', $delete_tag_ids). ")");
        }
        return true;
    }
	
	public function virtual_delete($delete_ids){
		$this->db->trans_start();
		$this->db->query("update v9_news set status=0 where id in ({$delete_ids})");
		$this->db->query("delete from v9_position where id in ({$delete_ids}) and modelid=1");
		$this->db->trans_complete();
		if ($this->db->trans_status() === false){
			return false;
		}
		return $this->db->affected_rows();
	}
	
	public function listget($catid, $offset, $pagesize, $fields = "*", $order = "listorder DESC",$flag=0)
    {
        if($flag)
            $res = $this->db->query("select {$fields} from v9_news where catid=? and status<>0  order by {$order} limit {$offset},{$pagesize}", $catid)->result_array();
		else
            $res = $this->db->
            query("select {$fields} from v9_news where catid=? and status=99 order by {$order} limit {$offset},{$pagesize}", $catid)
            ->result_array();
		return $res;
	}
	
	public function tizi_space_article($space_id,$limit=1)
    {
            $res = $this->db->query("select id,title from space_article where space_user_id=? and status=1  order by inputtime desc limit {$limit}", $space_id)->row_array();
			$res['url'] = space_url()."article-{$res['id']}-1.html";
		return $res;
	}
	
	public function count($catid){
		$res = $this->db->query("select count(*) as total from v9_news where catid=? and status=99", 
			array($catid))->result_array();
		return $res[0]["total"];
	}
	
	public function update_listorders($data){
		foreach ($data as $key => $value){
			$this->db->query("update v9_news set listorder=? where id=?", array($value, $key));
		}
		return true;
	}
	
	public function get($id, $fields = "*"){
		$res = $this->db->query("select {$fields} from v9_news where id=? 
			and status=99", array($id))->result_array();
		return isset($res[0]) ? $res[0] : null;
	}
	
	public function get_news_by_tag($tag_name, $offset, $pagesize, $fields = "*", $order = "b.listorder DESC"){
		$data = $this->db->query("select b.{$fields} from v9_news_tags as a left join v9_news as b on a.news_id=b.id where a.tag_name=? order by 
			{$order} limit {$offset},{$pagesize}", $tag_name)->result_array();
		return $data;
	}
	
	/*
		读取友情链接内容
		author:zhangxiaoming 
		date:2014-04-21
	*/
	public function get_links($limit="0,10"){
		
		//读取文字链接
		
		$arr['text'] = $this->db->query("select title,url from v9_news where catid=? and thumb='' and status=99 order by inputtime desc limit $limit",array(29))->result_array();
		
		//读取图片链接
		$arr['img'] = $this->db->query("select title,url from v9_news where catid=29 and thumb!='' and status=99 order by inputtime desc limit $limit")->result_array();
		return $arr;
	}
	
	public function get_article_list($area='全国', $offset=0 , $page_size=5){   
        $time = time();
        $catids = implode(",", $this->catids);
        if(isset($area) && !empty($area)){//koooike存在
			$tag_name = $area;
		}else{
			$tag_name='全国';
		}
		//根据tag_name,求出 所有文章id
		$sql = "select news_id from v9_news_tags where tag_name = ?";
		$res = $this->db->query($sql,array($tag_name));
		$articles_id =  $res->result_array();
		$article_str = '';
		if(isset($articles_id[0])){
			foreach($articles_id as $key => $v)
			{
				$article_str .= $v['news_id'].",";
			}
			$str_sql = rtrim($article_str, ","); 
		}
		else{
			return null;
		}
		$sql = "select n.id, n.title,n.thumb, n.description,n.inputtime as inputtime  from v9_news n 
			where n.catid in ({$catids}) and n.status=99 and n.inputtime<=$time and id in ($str_sql)
			order by n.inputtime desc  
			limit $offset, $page_size";
        $res = $this->db->query($sql)->result_array();
        return isset($res[0])?$res:array();
    }
}
/* end of v9_news_model.php */