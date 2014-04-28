<?php
class Wetall_ItemcateAction extends UserAction{
	public function _initialize() {
		parent::_initialize();
		$this->_mod = D('item_cate');
		
		$catelist = M('item_cate')->where(array('status'=>1, 'tokenTall'=>$this->getTokenTall()))->order('ordid asc,id asc')->select();
		foreach ($catelist as $val){
			$cate_list[$val['id']]=$val['name'];
		}
		$this->assign('cate_list',$cate_list);
		
	}
	
	
	public function index(){
		$tokenTall = $this->getTokenTall();
		$map = array();
		$map['tokenTall'] = $tokenTall;
		$list_tmp = $this->_mod->where($map)->select();
		foreach ($list_tmp as $list_one){
			$is_allcate = $list_one['name'] == '全部商品' ? 'allcate' : 'cate&cid='.$list_one['id'];
			$list_one['url'] = $_SERVER['HTTP_HOST']."/weTall/index.php?m=book&a=".$is_allcate."&tokenTall=".$this->getTokenTall();
			$list_one['pcate'] = $this->_mod->where(array('id'=>$list_one['pid']))->getField('name');
			$list[] = $list_one;
		}
		
		$this->assign('list', $list);
		$this->assign('list_table', true);
		$this->display();
	}

	public function edit() {
		$id = $this->_get('id');
		$tokenTall = $this->getTokenTall();
		
		//提交，有id则为编辑，无id则为新增
		if (IS_POST) {
			//dump($_POST);exit;
			 
			//获取数据
			if (false === $data = $this->_mod->create()) {
				$this->error($this->_mod->getError());
			}
			$data['tokenTall'] = $tokenTall;
			//生成spid
			$data['spid'] = $this->_mod->get_spid($data['pid']);
			$data['status'] = 1;
			$data['is_index'] = 1;
			//dump($data);exit;
			
			if ($_POST['id'] != "") {
				//编辑
				$result = $this->_mod->save($data);
			} else {
				//新增
				$result = $this->_mod->add($data);
			}

			if ($result !== false) {
				$this->success('成功！', U('Wetall_Itemcate/index'));
			} else {
				$this->error('失败！');
			}
			
		} 
		//非提交，有id为编辑展示，无id为新增展示
		else {
			if ($id) {
				$myaction = "编辑";
				$info = $this->_mod->where(array('id'=>$id))->find();
				$this->assign('info',$info);
				
			}else{
				$myaction = "新增";
			}
						
			$this->assign('myaction',$myaction);
			$this->display();
		}
	}
	
	public function del()
	{
	
		$ids = $this->_get('id');
		if ($ids) {
			$count=M('item')->where("cate_id in (".$ids.")")->count();
			if($count>0)
			{
				$this->error('分类被商品引用，不能删除');
			}
			 
			if (false !== $this->_mod->delete($ids)) {
				$this->success('删除成功！');
			} else {
				$this->error('删除失败！');
			}
		} else {
			$this->error('参数错误！');
		}
	}

}
?>