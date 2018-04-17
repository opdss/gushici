
$('[data-toggle="tooltip"]').tooltip();

$('.btn-remove').click(function () {
    var obj = $(this).data('obj');
    if (!obj) {
        console.log('remove operation obj error', obj);
        return false;
    }
    $(obj).val('');
});

$('.btn-retweet').click(function () {
    var obj = $(this).data('obj');
    if (!obj) {
        console.log('remove operation obj error', obj);
        return false;
    }
    var arr = obj.split('|');
    if (arr.length != 2){
        console.log('remove operation arr error', obj);
        return false;
    }
    var tmp = $(arr[0]).val();
    $(arr[0]).val($(arr[1]).val());
    $(arr[1]).val(tmp);
});

/**
 * 初始化markdown 编辑器
 * @param div_id
 * @param val
 * @returns {*}
 */
function initEditorMd(div_id, val){
    var editor = editormd(div_id, {
        width: "100%",
        height: 720,
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
        imageUpload : false,
        imageFormats : ["jpg", "jpeg", "gif", "png", "bmp", "webp"],
        imageUploadURL : "./php/upload.php",
        onload : function() {
            //console.log('onload', this);
            this.fullscreen();
            this.unwatch();
            this.watch().fullscreen();
            this.setMarkdown("#工具箱");
            this.width("100%");
            this.height(720);
            this.resize("100%", 720);
        }
    });
    return editor;
}

function swal_osc(text) {
    swal({
        'title' : text,
        'allowOutsideClick' : true,
        'showConfirmButton' : false,
        'animation' : false,
        'type' : 'success',
        'timer' : 1000,
    })
}

$('document').ready(function () {
    /*$('.btn-copy').zclip({
        path: "/statics/zclip/ZeroClipboard.swf",
        copy: function(){
            var obj = $(this).data('obj');
            if (!obj) {
                console.log('copy operation obj error', obj);
                return false;
            }
            return $(obj).val();
        },
        afterCopy: function () { swal_osc("复制成功！"); } /!* 复制成功后的操作 *!/
    });*/

    /*var clipboard_btn_copy = new ClipboardJS('.btn-copy', {
        target: function(trigger) {
            return $($(trigger).data('obj'))[0];
        }
    });*/
    var clipboard_btn_copy = new ClipboardJS('.btn-copy', {
        text: function(trigger) {
            return $($(trigger).data('obj')).val();
        }
    });

    clipboard_btn_copy.on('success', function(e) {
        console.info('Action:', e.action);
        console.info('Text:', e.text);
        console.info('Trigger:', e.trigger);
        e.clearSelection();
        swal_osc("复制成功！");
    });

    clipboard_btn_copy.on('error', function(e) {
        console.error('Action:', e.action);
        console.error('Trigger:', e.trigger);
    });
});
