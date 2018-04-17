var utils = {

    specialStr: '!@#$%^&*()_+=-',
    /**
     * 生成随机字符串
     * @param $num 长度
     * @param $has 是否含有特殊字符
     * @param special 特俗字符
     * @returns {string}
     */
    genRandStr: function genRandStr(num, has, special) {
        num = num || 16;
        var str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        var specialStr = has ? (special ? special : utils.specialStr) : '';
        if (has) {
            str += specialStr;
        }
        var len = str.length
        var res = '';
        for (var i = 0; i < num; i++) {
            res += str[Math.floor(Math.random() * len)];
        }
        return res;
    },

    /**
     * 校验18位身份证是否合法
     * @param idcard
     * @returns {boolean}
     */
    isIC: function (idcard) {
        if (idcard.length != 18) {
            return false;
        }

        $r = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $sum += idcard[$i] * $r[$i];
        }
        $t = [1, 0, 'x', 9, 8, 7, 6, 5, 4, 3, 2];
        return idcard[17].toLowerCase() == $t[$sum % 11];
    },

    /*get : function (url, succFunc) {
     $.get(url,)
     },
     post : function () {

     }*/
    getFmtDateTime: function (timestamp) {
        var date = timestamp && typeof timestamp == 'number' ? new Date(timestamp) : new Date();
        var s1 = "-";
        var s2 = ":";
        var pad = function (num) {
            return num < 10 ? "0" + num.toString() : num;
        }
        return date.getFullYear() + s1 + pad(date.getMonth() + 1) + s1 + pad(date.getDate()) + " " + pad(date.getHours()) + s2 + pad(date.getMinutes()) + s2 + pad(date.getSeconds());
    },

    getTimestamp: function (fmtDate) {
        if (fmtDate) {
            return Date.parse(new Date(fmtDate)) / 1000;
        }
        return Date.parse(new Date()) / 1000;
    },

    //得到标准时区的时间的函数
    getZoneTime: function (timezone) {
        //参数i为时区值数字，比如北京为东八区则输进8,西5输入-5
        if (typeof timezone !== 'number') {
            timezone = 8;
        }
        var dt = new Date();
        //本地时间与GMT时间的时间偏移差
        var offset = dt.getTimezoneOffset() * 60000;
        //得到现在的格林尼治时间
        var utcTime = dt.getTime() + offset + 3600000 * timezone;
        return utils.getFmtDateTime(utcTime);
    },

    /**
     * md5计算
     * @param str
     * @returns {*}
     */
    md5 : function (str) {
        var rotateLeft = function (lValue, iShiftBits) {
            return (lValue << iShiftBits) | (lValue >>> (32 - iShiftBits));
        }
        var addUnsigned = function (lX, lY) {
            var lX4, lY4, lX8, lY8, lResult;
            lX8 = (lX & 0x80000000);
            lY8 = (lY & 0x80000000);
            lX4 = (lX & 0x40000000);
            lY4 = (lY & 0x40000000);
            lResult = (lX & 0x3FFFFFFF) + (lY & 0x3FFFFFFF);
            if (lX4 & lY4) return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
            if (lX4 | lY4) {
                if (lResult & 0x40000000) return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
                else return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
            } else {
                return (lResult ^ lX8 ^ lY8);
            }
        }
        var F = function (x, y, z) {
            return (x & y) | ((~x) & z);
        }
        var G = function (x, y, z) {
            return (x & z) | (y & (~z));
        }
        var H = function (x, y, z) {
            return (x ^ y ^ z);
        }
        var I = function (x, y, z) {
            return (y ^ (x | (~z)));
        }
        var FF = function (a, b, c, d, x, s, ac) {
            a = addUnsigned(a, addUnsigned(addUnsigned(F(b, c, d), x), ac));
            return addUnsigned(rotateLeft(a, s), b);
        };
        var GG = function (a, b, c, d, x, s, ac) {
            a = addUnsigned(a, addUnsigned(addUnsigned(G(b, c, d), x), ac));
            return addUnsigned(rotateLeft(a, s), b);
        };
        var HH = function (a, b, c, d, x, s, ac) {
            a = addUnsigned(a, addUnsigned(addUnsigned(H(b, c, d), x), ac));
            return addUnsigned(rotateLeft(a, s), b);
        };
        var II = function (a, b, c, d, x, s, ac) {
            a = addUnsigned(a, addUnsigned(addUnsigned(I(b, c, d), x), ac));
            return addUnsigned(rotateLeft(a, s), b);
        };
        var convertToWordArray = function (string) {
            var lWordCount;
            var lMessageLength = string.length;
            var lNumberOfWordsTempOne = lMessageLength + 8;
            var lNumberOfWordsTempTwo = (lNumberOfWordsTempOne - (lNumberOfWordsTempOne % 64)) / 64;
            var lNumberOfWords = (lNumberOfWordsTempTwo + 1) * 16;
            var lWordArray = Array(lNumberOfWords - 1);
            var lBytePosition = 0;
            var lByteCount = 0;
            while (lByteCount < lMessageLength) {
                lWordCount = (lByteCount - (lByteCount % 4)) / 4;
                lBytePosition = (lByteCount % 4) * 8;
                lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount) << lBytePosition));
                lByteCount++;
            }
            lWordCount = (lByteCount - (lByteCount % 4)) / 4;
            lBytePosition = (lByteCount % 4) * 8;
            lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80 << lBytePosition);
            lWordArray[lNumberOfWords - 2] = lMessageLength << 3;
            lWordArray[lNumberOfWords - 1] = lMessageLength >>> 29;
            return lWordArray;
        };
        var wordToHex = function (lValue) {
            var WordToHexValue = "", WordToHexValueTemp = "", lByte, lCount;
            for (lCount = 0; lCount <= 3; lCount++) {
                lByte = (lValue >>> (lCount * 8)) & 255;
                WordToHexValueTemp = "0" + lByte.toString(16);
                WordToHexValue = WordToHexValue + WordToHexValueTemp.substr(WordToHexValueTemp.length - 2, 2);
            }
            return WordToHexValue;
        };
        var uTF8Encode = function (string) {
            string = string.replace(/\x0d\x0a/g, "\x0a");
            var output = "";
            for (var n = 0; n < string.length; n++) {
                var c = string.charCodeAt(n);
                if (c < 128) {
                    output += String.fromCharCode(c);
                } else if ((c > 127) && (c < 2048)) {
                    output += String.fromCharCode((c >> 6) | 192);
                    output += String.fromCharCode((c & 63) | 128);
                } else {
                    output += String.fromCharCode((c >> 12) | 224);
                    output += String.fromCharCode(((c >> 6) & 63) | 128);
                    output += String.fromCharCode((c & 63) | 128);
                }
            }
            return output;
        };
        function _md5(string) {
            var x = Array();
            var k, AA, BB, CC, DD, a, b, c, d;
            var S11 = 7, S12 = 12, S13 = 17, S14 = 22;
            var S21 = 5, S22 = 9, S23 = 14, S24 = 20;
            var S31 = 4, S32 = 11, S33 = 16, S34 = 23;
            var S41 = 6, S42 = 10, S43 = 15, S44 = 21;
            string = uTF8Encode(string);
            x = convertToWordArray(string);
            a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;
            for (k = 0; k < x.length; k += 16) {
                AA = a; BB = b; CC = c; DD = d;
                a = FF(a, b, c, d, x[k + 0], S11, 0xD76AA478);
                d = FF(d, a, b, c, x[k + 1], S12, 0xE8C7B756);
                c = FF(c, d, a, b, x[k + 2], S13, 0x242070DB);
                b = FF(b, c, d, a, x[k + 3], S14, 0xC1BDCEEE);
                a = FF(a, b, c, d, x[k + 4], S11, 0xF57C0FAF);
                d = FF(d, a, b, c, x[k + 5], S12, 0x4787C62A);
                c = FF(c, d, a, b, x[k + 6], S13, 0xA8304613);
                b = FF(b, c, d, a, x[k + 7], S14, 0xFD469501);
                a = FF(a, b, c, d, x[k + 8], S11, 0x698098D8);
                d = FF(d, a, b, c, x[k + 9], S12, 0x8B44F7AF);
                c = FF(c, d, a, b, x[k + 10], S13, 0xFFFF5BB1);
                b = FF(b, c, d, a, x[k + 11], S14, 0x895CD7BE);
                a = FF(a, b, c, d, x[k + 12], S11, 0x6B901122);
                d = FF(d, a, b, c, x[k + 13], S12, 0xFD987193);
                c = FF(c, d, a, b, x[k + 14], S13, 0xA679438E);
                b = FF(b, c, d, a, x[k + 15], S14, 0x49B40821);
                a = GG(a, b, c, d, x[k + 1], S21, 0xF61E2562);
                d = GG(d, a, b, c, x[k + 6], S22, 0xC040B340);
                c = GG(c, d, a, b, x[k + 11], S23, 0x265E5A51);
                b = GG(b, c, d, a, x[k + 0], S24, 0xE9B6C7AA);
                a = GG(a, b, c, d, x[k + 5], S21, 0xD62F105D);
                d = GG(d, a, b, c, x[k + 10], S22, 0x2441453);
                c = GG(c, d, a, b, x[k + 15], S23, 0xD8A1E681);
                b = GG(b, c, d, a, x[k + 4], S24, 0xE7D3FBC8);
                a = GG(a, b, c, d, x[k + 9], S21, 0x21E1CDE6);
                d = GG(d, a, b, c, x[k + 14], S22, 0xC33707D6);
                c = GG(c, d, a, b, x[k + 3], S23, 0xF4D50D87);
                b = GG(b, c, d, a, x[k + 8], S24, 0x455A14ED);
                a = GG(a, b, c, d, x[k + 13], S21, 0xA9E3E905);
                d = GG(d, a, b, c, x[k + 2], S22, 0xFCEFA3F8);
                c = GG(c, d, a, b, x[k + 7], S23, 0x676F02D9);
                b = GG(b, c, d, a, x[k + 12], S24, 0x8D2A4C8A);
                a = HH(a, b, c, d, x[k + 5], S31, 0xFFFA3942);
                d = HH(d, a, b, c, x[k + 8], S32, 0x8771F681);
                c = HH(c, d, a, b, x[k + 11], S33, 0x6D9D6122);
                b = HH(b, c, d, a, x[k + 14], S34, 0xFDE5380C);
                a = HH(a, b, c, d, x[k + 1], S31, 0xA4BEEA44);
                d = HH(d, a, b, c, x[k + 4], S32, 0x4BDECFA9);
                c = HH(c, d, a, b, x[k + 7], S33, 0xF6BB4B60);
                b = HH(b, c, d, a, x[k + 10], S34, 0xBEBFBC70);
                a = HH(a, b, c, d, x[k + 13], S31, 0x289B7EC6);
                d = HH(d, a, b, c, x[k + 0], S32, 0xEAA127FA);
                c = HH(c, d, a, b, x[k + 3], S33, 0xD4EF3085);
                b = HH(b, c, d, a, x[k + 6], S34, 0x4881D05);
                a = HH(a, b, c, d, x[k + 9], S31, 0xD9D4D039);
                d = HH(d, a, b, c, x[k + 12], S32, 0xE6DB99E5);
                c = HH(c, d, a, b, x[k + 15], S33, 0x1FA27CF8);
                b = HH(b, c, d, a, x[k + 2], S34, 0xC4AC5665);
                a = II(a, b, c, d, x[k + 0], S41, 0xF4292244);
                d = II(d, a, b, c, x[k + 7], S42, 0x432AFF97);
                c = II(c, d, a, b, x[k + 14], S43, 0xAB9423A7);
                b = II(b, c, d, a, x[k + 5], S44, 0xFC93A039);
                a = II(a, b, c, d, x[k + 12], S41, 0x655B59C3);
                d = II(d, a, b, c, x[k + 3], S42, 0x8F0CCC92);
                c = II(c, d, a, b, x[k + 10], S43, 0xFFEFF47D);
                b = II(b, c, d, a, x[k + 1], S44, 0x85845DD1);
                a = II(a, b, c, d, x[k + 8], S41, 0x6FA87E4F);
                d = II(d, a, b, c, x[k + 15], S42, 0xFE2CE6E0);
                c = II(c, d, a, b, x[k + 6], S43, 0xA3014314);
                b = II(b, c, d, a, x[k + 13], S44, 0x4E0811A1);
                a = II(a, b, c, d, x[k + 4], S41, 0xF7537E82);
                d = II(d, a, b, c, x[k + 11], S42, 0xBD3AF235);
                c = II(c, d, a, b, x[k + 2], S43, 0x2AD7D2BB);
                b = II(b, c, d, a, x[k + 9], S44, 0xEB86D391);
                a = addUnsigned(a, AA);
                b = addUnsigned(b, BB);
                c = addUnsigned(c, CC);
                d = addUnsigned(d, DD);
            }
            var tempValue = wordToHex(a) + wordToHex(b) + wordToHex(c) + wordToHex(d);
            return tempValue.toLowerCase();
        }
        return _md5(str);
    },

    stringToArray: function (s) {
        var a = [];
        for (var i = 0; i < s.length; i++) {
            a.push(s.charCodeAt(i));
        }
        return a;
    },

    apTo64: function (v, n) {
        var itoa64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        var s = '';
        while (--n >= 0) {
            s += itoa64.charAt(v & 0x3f);  // prend les 6 bits les plus à droite.
            v >>>= 6;                    // décale de 6 bits.
        }
        return s;
    },

    htpasswd: function (user, pw, alg) {

        if (!user || !pw) {
            console.log('username or password is empty!');
            return false;
        }

        var ALG_PLAIN = 0;           // mot de passe en clair : ne fonctionnera pas sur les serveurs Unix
        var ALG_CRYPT = 1;           // chiffrement par la fonction crypt() d'Unix
        var ALG_APMD5 = 2;           // chiffrement en MD5, utilisé par défaut sous Windows entre autres.
        var ALG_APSHA = 3;           // chiffrement en SHA-1
        var AP_SHA1PW_ID = "{SHA}";
        var AP_MD5PW_ID = "$apr1$";


        // un peu de sel pour mettre dans les mots de passe en MD5 ou Crypt : 8 caractères aléatoires en base 64.
        // On fait 4 + 4, parce que 8 caractères c'est trop pour être stocké dans un entier.
        var salt = utils.apTo64(Math.floor(Math.random() * 16777215), 4)    // 2^24-1 : 4 * 6 bits.
            + utils.apTo64(Math.floor(Math.random() * 16777215), 4);   // 2^24-1 : 4 * 6 bits.


        var plus127 = 0;
        for (var i = 0; i < user.length; i++) if (user.charCodeAt(i) > 127) plus127++;
        if (plus127) alert("Apache doesn't like non-ascii characters in the user name.");

        var cpw = '';         // Mot de passe chiffré ; max 119 caractères.
        switch (alg) {
            /*
             * output of base64 encoded SHA1 is always 28 chars + AP_SHA1PW_ID length (ce qui fait 33 caractères)
             */
            case ALG_APSHA:
                cpw = AP_SHA1PW_ID + b64_sha1(pw);
                break;

            case ALG_APMD5:
                var msg = pw;          // On commence par le mot de passe en clair
                msg += AP_MD5PW_ID;    // puis le petit mot magique
                msg += salt;           // et un peu de sel.
                /*
                 * Then just as many characters of the MD5(pw, salt, pw)
                 */
                var final_ = utils.md5(pw + salt + pw);
                for (var pl = pw.length; pl > 0; pl -= 16) msg += final_.substr(0, (pl > 16) ? 16 : pl);

                /*
                 * Then something really weird...
                 */
                for (i = pw.length; i != 0; i >>= 1)
                    if (i & 1) msg += String.fromCharCode(0);
                    else msg += pw.charAt(0);
                final_ = utils.md5(msg);

                /*
                 * Ensuite une partie pour ralenir les choses ! En JavaScript ça va être vraiment lent !
                 */
                var msg2;
                for (i = 0; i < 1000; i++) {
                    msg2 = '';
                    if (i & 1) msg2 += pw; else msg2 += final_.substr(0, 16);
                    if (i % 3) msg2 += salt;
                    if (i % 7) msg2 += pw;
                    if (i & 1) msg2 += final_.substr(0, 16); else msg2 += pw;
                    final_ = utils.md5(msg2);
                }
                final_ = utils.stringToArray(final_);

                /*
                 * Now make the output string.
                 */
                cpw = AP_MD5PW_ID + salt + '$';
                cpw += utils.apTo64((final_[0] << 16) | (final_[6] << 8) | final_[12], 4);
                cpw += utils.apTo64((final_[1] << 16) | (final_[7] << 8) | final_[13], 4);
                cpw += utils.apTo64((final_[2] << 16) | (final_[8] << 8) | final_[14], 4);
                cpw += utils.apTo64((final_[3] << 16) | (final_[9] << 8) | final_[15], 4);
                cpw += utils.apTo64((final_[4] << 16) | (final_[10] << 8) | final_[5], 4);
                cpw += utils.apTo64(final_[11], 2);
                break;

            case ALG_PLAIN:
                cpw = pw;
                break;

            case ALG_CRYPT:
            default:
                cpw = Javacrypt.displayPassword(pw, salt);
                break;
        }

        /*
         * Check to see if the buffer is large enough to hold the username,
         * hash, and delimiters.
         */
        if (user.length + 1 + cpw.length > 255) {
            //alert('Your login and password are too long.')
            console.log('Your login and password are too long.');
            return false;
        } else {
            return user + ':' + cpw;
        }
    }

}