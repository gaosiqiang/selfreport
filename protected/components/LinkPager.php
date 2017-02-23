<?php
/**
 * 分页类，继承底层，将ul li 结构变成a结构，以便在ie6下可以水平居中
 * 
 * @author Terry
 *
 */
class LinkPager extends CLinkPager
{
	const CSS_SELECTED_PAGE = 'active';
	public $header = false;
	public $cssFile = false;
	public $nextPageLabel = '下一页 >';
	public $prevPageLabel = '< 上一页';
	public $firstPageLabel = '首页';
	public $lastPageLabel = '末页';
	/**
	 * Executes the widget.
	 * This overrides the parent implementation by displaying the generated page buttons.
	 */
	public function run()
	{
		$buttons=$this->createPageButtons();
		if(empty($buttons))
			return;
		echo $this->header;
		echo CHtml::tag('ul',array('class'=>'pagination'),implode("\n",$buttons));
		echo $this->footer;
	}
	
	protected function createPageButton($label,$page,$class,$hidden,$selected)
	{
		if($hidden)
			return;
		if($selected)
			$class.=' '.self::CSS_SELECTED_PAGE;
		return '<li class="'.$class.'">'.CHtml::link($label,$this->createPageUrl($page)).'</li>';
	}
}