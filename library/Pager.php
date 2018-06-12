<?php
/**
 * 分页处理
 *
 * @author
 */
class Pager
{

        //分页参数名称
        public static $sPageName = "page";
        //每页记录数参数
        public static $sPageSize = "pagesize";

        /**
         * @desc  默认分页
      <div class="page">
        <div class="clearfix">
         <span class="disabled_page">首页</span><a href="javascript:;">上一页</a><a href="javascript:;" >1</a> <a href="javascript:;">2</a> <a href="java
script:;" class="active">3</a><a href="javascript:;" >4</a><a href="javascript:;" >5</a><a href="javascript:;" >下一页</a><a href="javascript:;" class="n
otmargin">末页</a></div>
      </div>
         * @param int $total 总记录数
         * @param int $pageSize 每页记录数
         * @param int $page 当前页
         * @return string
         */
        public static function default_pager( $total_records, $page_size, $page, $total_page = FALSE)
        {
        	//总页数
         	$total_page = $total_page ? $total_page : ceil( $total_records / $page_size ); # 应对课程列表页，不规则分页
            if( $total_page < 2 ) {
            	return '';
            }

            //初始化url
            $url = $_SERVER['REQUEST_URI'];
        	$rep_from = array( "/\?+page=\d+/i",'/&+page=\d+/i' );
        	$rep_to = array( "?",'' );
        	$url = preg_replace( $rep_from, $rep_to, $url );


	        if ( false === strpos( $url, '?' ) ) {
	            $url .="?page=";
	        } else if ( substr( $url, -1 ) == '?' ) {
	            $url .="page=";
	        } else {
	            $url .= "&page=";
	        }


            //总页数
            if( $total_page <= 7 ) {
            	$s = 1;
                $e = $total_page;
            } else if( $page < 4 ) {
            	$s = 1;
              	$e = $s + 6;
            } else if( $page > $total_page - 3 ) {
            	$s = $total_page - 4;
                $e = $total_page;
            } else {
             	$s = $page - 3;
             	$e = $page + 3;
            }



            $html = '<div class="ui borderless primary  menu mt-lg">';
            $html .= ( $page > 1 ) ? '<a class="icon item" href="'. $url .'1"><i class="angle double left icon"></i></a>' : '<a class="icon item disabled"><i class="angle double left icon"></i></a>';
            $html .= ( $page > 1 ) ? '<a class="icon item" href="'. $url . ($page-1) . '"><i class="angle left icon"></i></a>' : "<a class=\"icon item disabled\"><i class=\"angle left icon\"></i></a>";

            for( $i = $s; $i<=$e; $i++ ) {
            	if( $page == $i ) {
                   	$html .= "<a class='item active'>" . $i . "</a>";
                 } else {
                   	$html .= '<a href="' . $url . $i . '" class="item">' . $i . '</a>';
                 }
            }

            $html .= ( $page < $total_page ) ? '<a class="icon item" href="' . $url . ($page+1) . '"><i class="angle right icon"></i></a>' : '<a class="icon item disabled"><i class="angle right icon"></i></a>';
            $html .= ( $page < $total_page ) ? '<a class="icon item" href="' . $url . $total_page . '"><i class="angle double right icon"></i></a>' : '<a class="icon item disabled"><i class="angle double right icon"></i></a>';
            $html .= '</div>';

            return $html;
        }
}
