<?php
/*
 * This file is part of the HMVC package.
 *
 * (c) Allen Niu <h@h1soft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace hmvc\Web;

/**
 * $page = new \hmvc\Web\Pagination();
  $page->setPageSize(1);
  echo $page->count("select count(*) from `h_resources`");
  echo $page->getTotalPage();
 */
class Pagination {

    protected $_total_page = 0;
    protected $_total_rows = 0;
    protected $_cur_page = 0;
    protected $_offset = 0;
    protected $_pagesize = 20;
    protected $_url = 'index/index';
    protected $_params = array();

    public function __construct($_total_rows = 0, $_cur_page = 1, $_pagesize = 20) {
        $this->_total_rows = $_total_rows;
        $this->_cur_page = $_cur_page ? $_cur_page : 1;
        $this->_pagesize = $_pagesize;
        $this->_init();
    }

    private function _init() {
        $this->_total_page = ceil($this->_total_rows / $this->_pagesize);
        $this->_offset = ($this->_cur_page - 1) * $this->_pagesize;
    }

    public function toHtml() {
        echo '<div class="row "><div class="col-lg-12" style="text-align:center"><ul class="pagination pagination-lg">';
        if ($this->_cur_page <= 1) {
            echo '<li class="disabled" ><a href="javascript:void(0)">&laquo;</a></li>';
//            echo '<li class="paginate_button active " tabindex="0"><a href="#">1</a></li>';
        } else {
            $this->_params['page'] = $this->_cur_page-1;
            echo '<li ><a href="', url_for($this->_url, $this->_params), '">&laquo;</a></li>';
        }
        for ($i = 1; $i <= $this->_total_page; $i++) {
            if ($i == $this->_cur_page) {
                echo '<li class="paginate_button active " tabindex="0"><a href="javascript:void(0)">', $i, '</a></li>';
            } else {
                $this->_params['page'] = $i;
                echo '<li class="paginate_button " tabindex="0"><a href="',url_for($this->_url, $this->_params),'">', $i, '</a></li>';
            }
        }

        
        if ($this->_cur_page == $this->_total_page) {
            echo '<li class="disabled"  ><a href="javascript:void(0)">&raquo;</a></li>';
        } else {
            $this->_params['page'] = $this->_cur_page+1;
            echo '<li ><a href="', url_for($this->_url, $this->_params), '">&raquo;</a></li>';
        }
        echo '</ul></div></div>'; 
    }

    public function count($str, $data = false, $db = false) {
        if (!$db) {
            $db = \hmvc\Db\Db::getConnection();
        }
        if ($data && is_array($data)) {
            $row = $db->getRow(vsprintf($str, $data), MYSQLI_NUM);
        } else {
            $row = $db->getRow($str, MYSQLI_NUM);
        }
        if (isset($row[0])) {
            $this->_total_rows = intval($row[0]);
        }
        $this->_init();
        return $this->_total_rows;
    }

    public function getPageSize() {
        return $this->_pagesize;
    }

    public function getOffset() {
        return $this->_offset;
    }

    public function getCurPage() {
        return $this->_cur_page;
    }

    public function getTotalPage() {
        return $this->_total_page;
    }

    public function getTotalRows() {
        return $this->_total_rows;
    }

    public function setCurPage($_cur_page) {
        $this->_cur_page = $_cur_page;
        return $this;
    }

    public function setParams($_params) {
        $this->_params = $_params;
        return $this;
    }

    public function setUrl($_params) {
        $this->_url = $_params;
        return $this;
    }

    public function setPageSize($_size) {
        $this->_pagesize = $_size;
        return $this;
    }

}
