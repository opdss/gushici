/**
 * Created by SamWu on 2017/6/7.
 */
/**
 * 快捷键监听
 * @param key_map
 */
function shortKey(key_map) {
    var debug = 0;
    var st_time = 1000; //清除组合按键间隔时间
    var st_flag = true;
    var keys = ''; //组合按键
    var ls = '+'; //组合按键连接符
    var st_fn = function() {
        if (st_flag) {
            st_flag = false;
            setTimeout(function(){
                keys = '';
                st_flag = true;
                }, st_time);
        }
    }
    var listen_func = function(key_map){
        $(document).keyup(function(e) {
            //当有元素获得焦点的时候，取消按键监听
            if ($(':focus').length != 0) {
                return true;
            }
            var key = keys!='' ? keys+ ls + e.key :  e.key;
            debug && console.log(keys, key);
            $.each(key_map, function (k, fn) {
                if (key == k) {
                    if (typeof fn == 'function') {
                        fn(key);
                    }
                    keys = '';
                    return true;
                } else if (k.indexOf(key) == 0) {
                    keys = key;
                }
            });
            //没有命中的话，等待的下一次按键组合，过期st_time秒清除
            st_fn();
            return true;
        });
    };
    listen_func(key_map);
}

function crc32(str){
    str=encodeURIComponent(str);
    var Crc32Table=new Array(256);
    var i,j;
    var Crc;
    for(i=0; i<256; i++)
    {
        Crc=i;
        for(j=0; j<8; j++)
        {
            if(Crc & 1)
                Crc=((Crc >> 1)& 0x7FFFFFFF) ^ 0xEDB88320;
            else
                Crc=((Crc >> 1)& 0x7FFFFFFF);
        }
        Crc32Table[i]=Crc;
    }
    if (typeof str != "string") str = "" + str;
    Crc=0xFFFFFFFF;
    for(i=0; i<str.length; i++)
        Crc=((Crc >> 8)&0x00FFFFFF) ^ Crc32Table[(Crc & 0xFF)^ str.charCodeAt(i)];
    Crc ^=0xFFFFFFFF;
    return (Crc >> 3);
}

function strHash(input) {
    var I64BIT_TABLE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-'.split('');
    var hash = 5381;
    var i = input.length - 1;
    if(typeof input == 'string'){
        for (; i > -1; i--)
            hash += (hash << 5) + input.charCodeAt(i);
    } else{
        for (; i > -1; i--)
            hash += (hash << 5) + input[i];
    }
    var value = hash & 0x7FFFFFFF;
    var retValue = '';
    do{
        retValue += I64BIT_TABLE[value & 0x3F];
    } while (value >>= 6);
    return retValue;
}
/**
 * 弹出浮层登录方法
 * @returns {boolean}
 */
function loginModal(act){
    var islogin = false;
    if (islogin) {
        return true;
    }
    act = act || 'login';
    var api = '/modal-login?act='+act;
    var modal_obj = loadModal(api);
    if (act == 'register') {
        reCaptcha(modal_obj.find('img.captcha'));
    }
    modal_obj.find('.div-'+act).show();
    modal_obj.find('.div-'+act).siblings().hide();
}
function loadModal(remote){
    var opt = typeof remote == 'obj' ? remote : {'remote' : remote};
    var id = 'modal-'+strHash(opt.remote.split('?')[0]);
    var modal_obj = $('#'+id);

    if (modal_obj.length == 0) {
        var html = '<div class="modal fade" id="'+id+'" tabindex="-1" role="dialog" aria-labelledby="modalLabel">';
        html += '<div class="modal-dialog" role="document">';
        html += '<div class="modal-content"></div></div></div>';
        modal_obj = $(html);
        $(modal_obj).appendTo($('body'));
    }
    //加载地址
    modal_obj.modal(opt);
    return modal_obj;
}
/**
 * 退出
 */
function loginOut() {
    $.get('/api/signout', {}, function (msg) {
        if (msg.errCode == 0) {
            location.href='/';
        } else {
            alert(msg.errMsg);
        }
    }, 'json')
}

/**
 * 将markdown渲染html
 * @param div_id
 * @param val
 * @returns {Object}
 */
function mdToHtml(div_id, val) {
    var div_id = div_id || 'content';
    var div = editormd.markdownToHTML(div_id, {
        markdown: val || '',//+ "\r\n" + $("#append-test").text(),
        htmlDecode: false,       // 开启 HTML 标签解析，为了安全性，默认不开启
        htmlDecode: "style,script,iframe,form",  // you can filter tags decode
        toc: true,
        tocm: true,    // Using [TOCM]
        // tocContainer: "#custom-toc-container", // 自定义 ToC 容器层
        //gfm             : false,
        //tocDropdown     : true,
        // markdownSourceCode : true, // 是否保留 Markdown 源码，即是否删除保存源码的 Textarea 标签
        emoji: false,
        taskList: true,
        tex: true,  // 默认不解析
        flowChart: true,  // 默认不解析
        sequenceDiagram: true,  // 默认不解析
        path: '/statics/markdown/lib/',
    });
    return div;
}

/**
 * 初始化markdown 编辑器
 * @param div_id
 * @param val
 * @returns {*}
 */
function initEditorMd(div_id, val){
    var editor = editormd(div_id, {
        width: "100%",
        height: 760,
        path : '/statics/markdown/lib/',
        //theme : "dark",
        //previewTheme : "dark",
        //editorTheme : "pastel-on-dark",
        markdown : val || '',
        codeFold : true,
        //syncScrolling : false,
        saveHTMLToTextarea : true,    // 保存 HTML 到 Textarea
        searchReplace : true,
        //watch : false,                // 关闭实时预览
        htmlDecode : "style,script,iframe|on*",            // 开启 HTML 标签解析，为了安全性，默认不开启
        //toolbar  : false,             //关闭工具栏
        //previewCodeHighlight : false, // 关闭预览 HTML 的代码块高亮，默认开启
        emoji : true,
        taskList : true,
        tocm            : true,         // Using [TOCM]
        tex : true,                   // 开启科学公式TeX语言支持，默认关闭
        flowChart : true,             // 开启流程图支持，默认关闭
        sequenceDiagram : true,       // 开启时序/序列图支持，默认关闭,
        //dialogLockScreen : false,   // 设置弹出层对话框不锁屏，全局通用，默认为true
        //dialogShowMask : false,     // 设置弹出层对话框显示透明遮罩层，全局通用，默认为true
        //dialogDraggable : false,    // 设置弹出层对话框不可拖动，全局通用，默认为true
        //dialogMaskOpacity : 0.4,    // 设置透明遮罩层的透明度，全局通用，默认值为0.1
        //dialogMaskBgColor : "#000", // 设置透明遮罩层的背景颜色，全局通用，默认为#fff
        imageUpload : true,
        imageFormats : ["jpg", "jpeg", "gif", "png", "bmp", "webp"],
        imageUploadURL : "./php/upload.php",
        onload : function() {
            console.log('onload', this);
            /*this.fullscreen();
             this.unwatch();
             this.watch().fullscreen();
             this.setMarkdown("#PHP");
             this.width("100%");
             this.height(480);
             this.resize("100%", 640);*/
        }
    });
    return editor;
}

/**
 * 构造树
 * @param data
 * @param option
 * @returns {tree}
 */
function myTree(data, option){
    var result = data;//原始数据
    var tmp = {}; //处理好的数据
    var opt = {
        'id' : 'id',//分类id节点字段名
        'pid' : 'pid',//父id节点字段名
        'root' : 0, //顶级分类的父id
        'child' : 'child', //子节点字段名
        'title' : 'title' //名称
    };

    var html_conf = {
        'div_pre' : '<div><ul>',
        'div_last' : '</ul></div>',
        'ul_pre' : '<ul>',
        'ul_last' : '</ul>',
        'li_pre' : '<li>',
        'li_last' : '</li>'
    };

    var alreday = [];
    var loop = 0;
    opt =  $.extend({}, opt, option);
    /**
     * 树形数据集处理
     */
    var handle = function(){
        var _obj = {};
        $.each(result, function(i, obj){
            var _pid = obj[opt['pid']];
            if (typeof _obj[_pid] == 'undefined') {
                _obj[_pid] = new Array();
            }
            _obj[_pid].push(obj);
        });
        var _tmp = new Array();
        $.each(_obj, function(i, obj){
            _tmp.push({'pid':i, 'item':obj});
        });
        _tmp.reverse();

        for (i=_tmp.length; i>0; i--) {
            $.each(_tmp, function (j, obj) {
                if ($.inArray(obj['pid'], alreday) == -1) {
                    if (Object.keys(tmp).length == 0) {
                        tmp = obj;
                        alreday.push(obj['pid']);
                    } else {
                        $.each(obj['item'], function(k, _obj){
                            if (_obj[opt['id']] == tmp['pid']) {
                                _tmp[j]['item'][k][opt['child']] = tmp['item'];
                                //tmp = _tmp[j];
                            }
                        });
                    }
                }
            });
            tmp = {};
        }
        for (i in _tmp) {
            tmp[_tmp[i]['pid']] = _tmp[i]['item'];
        }
        return tmp;
    };
    handle();
    /**
     * 把树拼接成html
     * @param data
     * @param callback
     * @returns {string}
     * @private
     */
    var _get_html = function(data, callback) {
        var html_str = '';
        if (loop == 0) {
            html_str += html_conf['div_pre'];
        } else {
            html_str += html_conf['ul_pre'];
        }
        $.each(data, function(i, obj){
            var has_child = false;
            if (typeof obj[opt['child']] == 'object') {
                has_child = true;
                html_str += html_conf['li_pre'];
                html_str += typeof callback == 'function' ? callback(obj, loop, has_child) : obj[opt['title']];
                loop++;
                html_str += _get_html(obj[opt['child']], callback);
                html_str += html_conf['li_last'];
            } else {
                html_str += html_conf['li_pre'];
                html_str += typeof callback == 'function' ? callback(obj, loop, has_child) : obj[opt['title']];
                html_str += html_conf['li_last'];
            }
        });
        if (loop == 0) {
            html_str += html_conf['div_last'];
        } else {
            html_str += html_conf['ul_last'];
        }
        loop--;
        return html_str;
    };
    /**
     * 菜单 多维数组
     * @param integer pid 分类id
     * @return array 返回分支，默认返回整个树
     */
    this.leaf = function(pid) {
        pid = pid || opt.root;
        return tmp[pid];
    };

    /**
     * 获取html装饰后的树
     * @param pid
     * @param h_conf
     * @param callback
     * @returns {string}
     */
    this.html = function(pid, h_conf, callback) {
        if (typeof pid == 'function') {
            callback = pid;
            pid = opt.root;
        } else if (typeof h_conf == 'function') {
            callback = h_conf;
        }
        loop = 0;
        html_conf = $.extend({}, html_conf, h_conf);
        var data = this.leaf(pid);
        return data.length != 0 ? _get_html(data, callback) : '';
    };
    return this;
}

function randNum(min, max){
    var range = max - min;
    var rand = Math.random();
    var num = min + Math.round(rand * range); //四舍五入
    return num;
}

function hotTags(tags_div){
    var label = ['label-default', 'label-primary', 'label-success', 'label-info', 'label-danger', 'label-warning'];
    $.each($(tags_div).find('span'), function(i, n){
        var idx = Math.abs(crc32($(n).html())%label.length);
        $(n).addClass(label[idx]);
    });
}

function blockTitle(tags_div){
    var label = ['bg-block-a','bg-block-b','bg-block-c','bg-block-d','bg-block-e','bg-block-blue','bg-block-red','bg-block-yellow','bg-block-plum'];
    $.each($(tags_div), function(i, n){
        var idx = Math.abs(crc32($(n).find('h4').html())%label.length);
        $(n).addClass(label[idx]);
    });
}

function reCaptcha(obj, w, h){
    var w = w || 120;
    var h = h || 32;
    $(obj).attr('src', '/captcha/'+w+'/'+h+'?t=' + Math.random());
}

/**
 * 数据表单回先方法，表单 name：val
 * @param name
 * @param val
 * @param form 查找范围
 */
function echoForm(name, val, form){
    if (typeof name == 'object') {
        form = val || null;
    }
    form = form ? $(form) : null;
    var reFn = function(name, val){
        if (typeof name == 'object') {
            $.each(name, reFn);
        } else {
            var inp = form ? form.find('[name='+name+']') : $('[name='+name+']');
            $.each(inp, function(i, n){
                if (n.tagName == 'INPUT' || n.tagName == 'TEXTAREA') {
                    $(n).val(val);
                } else if (n.tagName == 'SELECT') {
                    $.each($(n).find('option'), function(j, opt){
                        if ($(opt).val() == val) {
                            $(opt).attr('selected', true);
                            return false;
                        }
                    })
                }
            });
        }
    }
    reFn(name, val);
}

$(document).ready(function(){
    //快捷键配置
    var k_map = {
        'a' : function(keys){
            alert('test key: '+keys);
        },
        '/' : function(keys){
            $('#keyword').focus();
        },
        'g+h' : function(keys){
            location.href='/index';
        },
        '?' : function(keys){
            location.href='/help';
        },
        'A' : function(keys){
            alert(222);
        },
        'Q' : function(keys){
            alert('退出登录');
        },
        'e' : function(keys){
            location.href='/article/edit';
        },
        'd' : function(keys){
            location.href='/article/edit/23';
        },
        'u' : function(keys){
            history.go(-1);
        },
        '=' : function(keys){
            $('#my_menu').click();
        },
        'g+t' : function(keys){
            document.body.scrollTop = 0;
        },
        'g+g' : function () {
            loginModal('login');
        }
    };
    //快捷键的绑定
    shortKey(k_map);
    //浮层登录框的绑定
    $('.btn-login-modal').click(function(){
        loginModal($(this).data('act'));
    });
    //随机渲染颜色
    hotTags('.hot-tags');
    blockTitle('.bg-block');
    //
    $('[data-toggle="tooltip"]').tooltip();
    $('.btn-signout').click(loginOut);
});
