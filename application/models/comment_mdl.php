<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Comment_mdl extends CI_Model
{
	const COMMENTS	= "comments";
	const POSTS		= "posts";

	/**
	 * Add a comment
	 *
	 * @access	public
	 * @param	array	comment data{post_ID,author,author_email,author_url,author_IP,content,ageent,created,parent_ID}
	 * @param	isCite	if comment is replied to anothoer comment,cite or not?
	 * @return 	mixed	{int|FALSE}
	 */
	public function addComment($data,$isCite = FALSE)
	{
		if($isCite && $data['parent_ID'] != 0)
		{
			$cite=$this->getCommentByID($data['parent_ID']);
			if($cite)
			{
				$data['content']='<blockquote>引用 '.$cite['author'].' 的话：<br />'.$cite['content'].'</blockquote>'.$data['content'];
			}
		}
		$res=$this->db->insert(self::COMMENTS,$data);

		return $res?$this->db->insert_id():FALSE;
	}

	/**
	 * Get comments
	 *
	 * @access	public
	 * @param	int		post ID
	 * @param	int		approved
	 * @param	string	field
	 * @param	string	join post?
	 * @return	array	
	 */
	public function getComments($postID = NULL,$approved = NULL,$field = NULL,$join = NULL)
	{
		if($field !== NULL)
		{
			$this->db->select($field);
		}

		if($postID !== NULL)
		{
			$this->db->where('post_ID',$postID);
		}

		if($approved !== NULL && $approved !== 'ALL')
		{
			$this->db->where('approved',$approved);
		}

		if($join !== NULL)
		{
			$this->db->join(self::POSTS,self::POSTS.'.post_ID = '.self::COMMENTS.'.post_ID');
		}

		$query=$this->db->get(self::COMMENTS);

		if($query->num_rows()>0)
		{
			return $query->result_array();
		}

		return array();
	}

	/**
	 * Get a comment by comment ID
	 *
	 * @access	pubilc
	 * @param	int		comment ID
	 * @param	string	field
	 * @return	mixed	{array|FALSE}
	 */
	public function getCommentByID($commentID,$field = NULL)
	{
		if($field !== NULL)
		{
			$this->db->select($field);
		}

		$this->db->where('comment_ID',$commentID);
		$query=$this->db->get(self::COMMENTS);

		if($query->num_rows()>0)
		{
			return $query->row_array();
		}

		return FALSE;
	}
}
/* End of file comment_mdl.php */
/* Location: ./application/models/comment_mdl.php */
?>