<?php

class ArticleModel extends BaseModel
{
    protected $db = 'blog';    
    protected $pk = 'id';
    protected $table = 'tbl_article';

	public function prepareData($parameters)
	{
	}
}
