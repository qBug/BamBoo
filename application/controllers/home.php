<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Home extends MY_Controller
{
	/**
	 * posts
	 *
	 * @access 	private
	 * @var		array
	 */
	private $_posts = array();

	/**
	 * Constructor
	 *
	 * @access 	public
	 * @return 	void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->library('pagination');
	}

	/**
	 * Default page
	 *
	 * @access 	public
	 * @return 	void
	 */
	public function index()
	{
		$page=$this->input->get('page');
		if(!is_numeric($page)||$page<1)
			$page=1;
		$this->_posts=$this->post_mdl->getPosts('*',1,'created desc',10,$page);

		$this->_preparePosts();
		$data['pageTitle']='首页';
		$data['pageDescription']=settingItem('blog_description');
		$data['pageKeywords']=settingItem('blog_keywords');
		$data['curPage']='home';
		$data['posts']=$this->_posts;

		$pagConfig['base_url']=site_url().'?';
		$pagConfig['total_rows']=$this->post_mdl->getNumPost(1);
		$this->pagination->initialize($pagConfig);
		$data['pagination']=$this->pagination->create_links();

		$this->loadThemeView('home',$data);
	}

	/**
	 * Tag page
	 *
	 * @access	public
	 * @param	string	tag slug
	 * @return	void
	 */
	public function tag($slug = '')
	{
		if(empty($slug))
			redirect(site_url());

		$page=$this->input->get('page');
		if(!is_numeric($page)||$page<1)
			$page=1;

		$slug=urldecode($slug);
		$tag=$this->tag_mdl->getTagBySlug($slug);
		if($tag === FALSE)
		{
			show_error('标签不存在或已被主人删除'.$slug);
			exit();
		}

		$total=0;
		$this->_posts=$this->post_mdl->getPostsByTagID($tag['tag_ID'],1,$page,$total);
		$this->_preparePosts();
		$data['pageTitle']='标签：'.$tag['name'];
		$data['curTitle']='标签归档：'.$tag['name'];
		$data['pageDescription']='标签：'.$tag['name'].'下的所有文章';
		$data['pageKeywords']=settingItem('blog_keywords');
		$data['curPage']='tag';
		$data['posts']=$this->_posts;

		$pagConfig['base_url']=site_url()."/tag/$slug/".'?';
		$pagConfig['total_rows']=$total;
		$this->pagination->initialize($pagConfig);
		$data['pagination']=$this->pagination->create_links();

		$this->loadThemeView('home',$data);
	}

	/**
	 * Category page
	 * 
	 * @access	public
	 * @param	string	category slug
	 * @return 	void
	 */
	public function category($slug = '')
	{
		if(empty($slug))
			redirect(site_url());

		$page=$this->input->get('page');
		if(!is_numeric($page)||$page<1)
			$page=1;

		$category=$this->category_mdl->getCategoryBySlug($slug);

		if($category === FALSE)
		{
			$slug=urldecode($slug);
			$category=$this->category_mdl->getCategoryBySlug($slug);
			if($category === FALSE)
				show_error('目录不存在或已被主人删除');
		}

		$categoryID=array($category['category_ID']);
		if($category['parent_ID']==0)
		{
			$categoryID=array_merge($categoryID,$this->category_mdl->getChild($category['category_ID']));	
		}
		
		$total=0;
		$this->_posts=$this->post_mdl->getPostsByCategoriesID($categoryID,1,$page,$total);
		$this->_preparePosts();	
		$data['pageTitle']='分类：'.$category['name'];
		$data['curTitle']='分类目录归档：'.$category['name'];
		$data['pageDescription']='分类：'.$category['name'].'下的所有文章';
		$data['pageKeywords']=settingItem('blog_keywords');
		$data['curPage']='category';
		$data['posts']=$this->_posts;

		$pagConfig['base_url']=site_url()."/category/$slug/".'?';
		$pagConfig['total_rows']=$total;
		$this->pagination->initialize($pagConfig);
		$data['pagination']=$this->pagination->create_links();

		$this->loadThemeView('home',$data);
	}

	/**
	 * Search page
	 *
	 * @access	public
	 * @return void
	 */
	public function search()
	{
		$keywords=strip_tags($this->input->get('q'));

		$this->_posts=$this->db->where('status',1)->like('title',$keywords)->order_by('created','desc')->get('posts')->result_array();
		$this->_preparePosts();
		$data['pageTitle']="\"$keywords\"的搜索结果";
		$data['curTitle']="关键词\"$keywords\"的搜索结果：";
		$data['pageDescription']=settingItem('blog_description');
		$data['pageKeywords']=settingItem('blog_keywords');
		$data['curPage']='search';
		$data['posts']=$this->_posts;
		$this->loadThemeView('home',$data);
	}

	/**
	 * Archives page
	 *
	 * @access	public
	 * @param	int		year
	 * @param	int		month
	 * @param	int		day	
	 * @return	void
	 */
	public function archives($year,$month = NULL,$day = NULL)
	{
		if(empty($year))redirect(site_url());

		$page=$this->input->get('page');
		if(!is_numeric($page)||$page<1)
			$page=1;

		$this->_posts=$this->post_mdl->getPostsByDate($year,$month,$day,$page,$total);
		$this->_preparePosts();
		$data['pageTitle']=$this->_dateString($year,$month,$day)."文章归档";
		$data['curTitle']=$data['pageTitle']."：";
		$data['pageDescription']=$data['pageTitle'];
		$data['pageKeywords']=settingItem('blog_keywords');
		$data['curPage']='archives';
		$data['posts']=$this->_posts;

		$base_url=site_url()."/archives/$year/";
		if($month !== NULL)
			$base_url.=$month.'/';
		if($day !== NULL)
			$base_url.=$day.'/';
		$pagConfig['base_url']=$base_url.'?';
		$pagConfig['total_rows']=$total;
		$this->pagination->initialize($pagConfig);
		$data['pagination']=$this->pagination->create_links();

		$this->loadThemeView('home',$data);
	}

	/**
	 * prepare posts information(tags,categories,links)
	 *
	 * @access	private
	 * @return 	void
	 */
	private function _preparePosts()
	{
		foreach($this->_posts as $key=>$value)
		{
			$this->_posts[$key]['permalink']=site_url('post/'.$value['slug']);
			$this->_posts[$key]['published']=date('Y年m月d日',$value['created']);
			$this->_posts[$key]['tags']=$this->tag_mdl->getTagsByPostID($value['post_ID'],'name,slug');
			$this->_posts[$key]['categories']=$this->category_mdl->getCategoriesByPostID($value['post_ID'],'name,slug');
			$this->_posts[$key]['excerpt']=Common::getExcerpt($value['content']);
			$this->_posts[$key]['more']=(strpos($value['content'],B_CONTENT_BREAK) === FALSE)?FALSE:TRUE;
			unset($this->_posts[$key]['slug']);
			unset($this->_posts[$key]['content']);
		}
	}

	/**
	 * Return date string
	 *
	 * @access	private
	 * @param	int		year
	 * @param	int		month
	 * @param	int 	day
	 * @return	string
	 */
	private function _dateString($year,$month = NULL,$day = NULL)
	{
		$res=$year."年";
		if($month !== NULL)
		{
			$res.=$month."月";
			if($day !== NULL)
			{
				$res.=$day."日";
			}
		}
		return $res;
	}
}
/* End of file home.php */
/* Location: ./application/controllers/home.php */
?>
