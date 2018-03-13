/**
 * Created by SamWu on 2018/1/29.
 */

function reCapt(obj, w, d) {
    var w = w || 90;
    var h = h || 38;
    var url = '/captcha?w=' + w + '&h=' + h + 't=' + Math.random();
    if (obj) {
        $(obj).attr('src', url);
    } else {
        return url;
    }
}