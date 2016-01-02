<?php

/*
 * This file is part of the HMVC package.
 *
 * (c) Allen Niu <h@h1soft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace hmvc\Component;

/**
 * $page = new \hmvc\Web\Pagination();
  $page->setPageSize(1);
  echo $page->count("select count(*) from `h_resources`");
  echo $page->getTotalPage();
 */
class Paginator {

    protected $limit;
    protected $page;
    protected $total;
    protected $data;

    public function __construct($page = 1, $limit = 20, $data = array()) {
        $this->page = $page ? $page : 1;
        $this->limit = $limit;
        $this->data = $data;
    }

    public function setTotal($total) {
        $this->total = $total;
        return $this;
    }

    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function getOffset() {
        return ceil(($this->page - 1) * $this->limit);
    }

    public function makeHtml($linkNum = 7, $list_class = 'pagination pagination-sm') {
        if ($this->limit == 'all' || $this->limit == 0) {
            return '';
        }

        $last = ceil($this->total / $this->limit);
        $last = $last == 0 ? 1 : $last;
        $start = ( ( $this->page - $linkNum ) > 0 ) ? $this->page - $linkNum : 1;
        $end = ( ( $this->page + $linkNum ) < $last ) ? $this->page + $linkNum : $last;

        $html = '<ul class="' . $list_class . '">';

        $class = ( $this->page == 1 ) ? "disabled" : "";
        $html .= '<li class="' . $class . '"><a href="?' . $this->buildQuery(array('limit' => $this->limit, 'page' => ( $this->page == 1 ) ? $this->page : ($this->page - 1))) . '">&laquo;</a></li>';

        if ($start > 1) {
            $html .= '<li><a href="?' . $this->buildQuery(array('limit' => $this->limit, 'page' => 1)) . '">1</a></li>';
            $html .= '<li class="disabled"><span>...</span></li>';
        }

        for ($i = $start; $i <= $end; $i++) {
            $class = ( $this->page == $i ) ? "active" : "";
            $html .= '<li class="' . $class . '"><a href="?' . $this->buildQuery(array('limit' => $this->limit, 'page' => $i)) . '">' . $i . '</a></li>';
        }

        if ($end < $last) {
            $html .= '<li class="disabled"><span>...</span></li>';
            $html .= '<li><a href="?' . $this->buildQuery(array('limit' => $this->limit, 'page' => $last)) . '">' . $last . '</a></li>';
        }

        $class = ( $this->page == $last ) ? "disabled" : "";
        $html .= '<li class="' . $class . '"><a href="?' . $this->buildQuery(array('limit' => $this->limit, 'page' => ( $this->page == $last ) ? $this->page : ($this->page + 1))) . '">&raquo;</a></li>';

        $html .= '</ul>';

        return $html;
    }

    protected function buildQuery($data) {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
        return http_build_query($this->data);
    }

}
