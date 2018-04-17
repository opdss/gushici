<?php
/**
 * Pagination.php for zg-moudou.
 * @author SamWu
 * @date 2017/7/4 14:08
 * @copyright istimer.com
 */
namespace App\Libraries;

class Pagination {

    private $total; //数据表中总记录数
    private $pageSize; //每页显示行数
    private $pageTotal; //页数
    private $listNum = 6; //分页列表显示数量
    private $pageName = 'p';
    private $path;
    private $query = array();

    public $config = array(
        "prev" => "【上一页】",
        "next" => "【下一页】",
        "first" => "【首 页】",
        "last" => "【尾 页】",
        'div_prev' => '<div>',
        'div_next' => '</div>',
    );

    public $temp = array(
        //0 => '&nbsp;共有<b>{#total}</b>个记录&nbsp;',
        //1 => '&nbsp;每页显示<b>{#pageSize}</b>条，本页<b>{#start}-{#end}</b>条&nbsp;',
        //2 => '&nbsp;<b>{#page}/{#pageTotal}</b>页&nbsp;',
        'list_active' => '&nbsp;<a href="#">{#page}</a>&nbsp;', //分页列表没有链接模版
        'list_not_active' => '&nbsp;<a href="{#url}">{#page}</a>&nbsp;',//分页列表有链接模版
        'fl_active' => '&nbsp;<a href="{#url}">{#title}</a>&nbsp;', //首页尾页有链接模版
        'fl_not_active' => '&nbsp;{#title}&nbsp;', //首页尾页没有链接模版
        'pn_active' => '&nbsp;<a href="{#url}">{#title}</a>&nbsp;',  //上一页下一页有链接模版
        'pn_not_active' => '&nbsp;{#title}&nbsp;', //上一页下一页没有链接模版
        'go_page' => '&nbsp;<input type="text" onkeydown="javascript:if(event.keyCode==13){var page=(this.value>{#pageTotal})?{#pageTotal}:this.value;location=\'{#baseUrl}&{#pageName}=\'+page;}" value="{#page}" style="width:25px"><input type="button" value="GO" onclick="javascript:var page=(this.previousSibling.value>{#pageTotal})?{#pageTotal}:this.previousSibling.value;location=\'{#baseUrl}&{#pageName}=\'+page;">&nbsp;'
    );

    /*
     * $total
     * $pageSize
     */
    public function __construct($total, $pageSize = 10, $query = array()) {
        $this->total = $total;
        $this->pageSize = $pageSize;
        $this->query = $this->getQuery($query);
        $this->page = isset($_GET[$this->pageName]) && intval($_GET[$this->pageName]) ? intval($_GET[$this->pageName]) : 1;
        $this->pageTotal = ceil($this->total / $this->pageSize);
    }

    public function setListNum($listNum) {
        $this->listNum = intval($listNum);
        return $this;
    }

    public function setPageName($pageName) {
        $this->pageName = trim($pageName);
        return $this;
    }

    public function setTemp(...$params) {
        if (count($params) == 1) {
            if (is_array($params[0])) {
                $this->temp = array_merge($this->temp, $params[0]);
            }
        } else {
            if (is_string($params[1])) {
                $this->temp[$params[0]] = $params[1];
            }
        }
        return $this;
    }

    public function setConfig(...$params) {
        if (count($params) == 1) {
            if (is_array($params[0])) {
                $this->config = array_merge($this->config, $params[0]);
            }
        } else {
            if (is_string($params[1])) {
                $this->config[$params[0]] = $params[1];
            }
        }
        return $this;
    }

    private function assign($temp, $data) {
        $search = array();
        $value = array();
        foreach ($data as $k => $v) {
            $search[] = '{#'.$k.'}';
            $value[] = $v;
        }
        return str_replace($search, $value, $this->temp[$temp]);
    }

    private function getQuery($querys) {
        $url = $_SERVER["REQUEST_URI"];
        $parse = parse_url($url);
        $query = array();
        if (isset($parse["query"])) {
            parse_str($parse['query'], $query);
        }
        $query = array_merge($querys, $query);
        if (isset($query[$this->pageName])) unset($query[$this->pageName]);
        $this->path = $parse['path'];
        return $query;
    }

    private function getUrl($page=0) {
        $query = $this->query;
        $page AND $query[$this->pageName] = $page;
        return $this->path . '?' . http_build_query($query);
    }

    private function start() {
        if ($this->total == 0)
            return 0;
        else
            return ($this->page - 1) * $this->pageSize + 1;
    }

    private function end() {
        return min($this->page * $this->pageSize, $this->total);
    }

    private function first() {
        $html = "";
        if ($this->page == 1)
            $html .= $this->assign('fl_not_active', array('url'=>'#', 'title'=>$this->config["first"]));
        else
            $html .= $this->assign('fl_active', array('url'=>$this->getUrl(1), 'title'=>$this->config["first"]));

        return $html;
    }

    private function prev() {
        $html = "";
        if ($this->page == 1)
            $html .= $this->assign('pn_not_active', array('url'=>'#', 'title'=>$this->config["prev"]));
        else
            $html .= $this->assign('pn_active', array('url'=>$this->getUrl($this->page - 1), 'title'=>$this->config["prev"]));

        return $html;
    }

    private function pageList() {
        $linkPage = "";
        $inum = floor($this->listNum / 2);
        for ($i = $inum; $i >= 1; $i--) {
            $page = $this->page - $i;
            if ($page < 1)
                continue;
            $linkPage .= $this->assign('list_not_active', array('url'=>$this->getUrl($page), 'page'=>$page));
        }
        $linkPage .= $this->assign('list_active', array('url'=>$this->getUrl($this->page), 'page'=>$this->page));
        for ($i = 1; $i <= $inum; $i++) {
            $page = $this->page + $i;
            if ($page <= $this->pageTotal)
                $linkPage .= $this->assign('list_not_active', array('url'=>$this->getUrl($page), 'page'=>$page));
            else
                break;
        }
        return $linkPage;
    }

    private function next() {
        $html = "";
        if ($this->page == $this->pageTotal)
            $html .= $this->assign('pn_not_active', array('url'=>'#', 'title'=>$this->config["next"]));
        else
            $html .= $this->assign('pn_active', array('url'=>$this->getUrl($this->page + 1), 'title'=>$this->config["next"]));

        return $html;
    }

    private function last() {
        $html = "";
        if ($this->page == $this->pageTotal)
            $html .= $this->assign('fl_not_active', array('url'=>'#', 'title'=>$this->config["last"]));
        else
            $html .= $this->assign('fl_active', array('url'=>$this->getUrl($this->pageTotal), 'title'=>$this->config["last"]));

        return $html;
    }


    function fpage($display = array(0, 1, 2, 3, 4, 5, 6, 7, 8)) {
        $html[0] = $this->assign(0, array('total'=>$this->total));
        $html[1] = $this->assign(1, array('pageSize'=>$this->pageSize, 'start'=> $this->start(), 'end' => $this->end()));
        $html[2] = $this->assign(2, array('page'=>$this->page, 'pageTotal'=>$this->pageTotal));

        $html[3] = $this->first();
        $html[4] = $this->prev();
        $html[5] = $this->pageList();
        $html[6] = $this->next();
        $html[7] = $this->last();
        $html[8] = $this->assign('go_page', array('baseUrl'=>$this->getUrl(), 'pageTotal'=>$this->pageTotal, 'pageName'=>$this->pageName, 'page'=>$this->page));;
        $fpage = $this->config['div_prev'];
        foreach ($display as $index) {
            $fpage.=$html[$index];
        }
        $fpage .= $this->config['div_next'];
        return $fpage;
    }

}
