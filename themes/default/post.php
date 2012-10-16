<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$this->load->view('header');
?>
		<div class="post">
			<div class="post-title"><?=$post['title']?></div>

			<div class="meta-date">
				<span><?=date('F j,Y',$post['created'])?></span>
			</div>
			<div class="content">
				<?php echo $post['content'];?>
			</div>

			<div class="metas">
				<div>分类：<?=Common::implodeMetas($post['categories'],'category')?></div>
				<div>标签：<?=Common::implodeMetas($post['tags'],'tag')?></div>
			</div>
		</div>
		<?php $this->load->view('comment');?>
<?php $this->load->view('footer');?>
