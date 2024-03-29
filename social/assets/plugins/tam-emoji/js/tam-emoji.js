/*!
 * Tam Emoji (http://trinhtam.info/plugins/summernote-emoji/example/)
 * Copyright 2017 Trinh Tam.
 * Email: contact.tamsoft@gmail.com
 * Website: trinhtam.info */

 var KEY_ESC = 27;
 var KEY_TAB = 9;
 var summernote_id;
! function(e) {
    "function" == typeof define && define.amd ? define(["jquery"], e) : "object" == typeof module && module.exports ? module.exports = e(require("jquery")) : e(window.jQuery)
}(function(e) {
    e.extend(e.summernote.plugins, {
        'emoji': function(context) {
        	var layoutInfo = context.layoutInfo;
            var $editor = layoutInfo.editor;
            var $editable = layoutInfo.editable;
            var $toolbar = layoutInfo.toolbar;
            var i = context;
            var n = this,
                t = e.summernote.ui;
                summernote_id = layoutInfo.note.context.id;
            Array.prototype.filter || (Array.prototype.filter = function(e) {
                var i = this.length >>> 0;
                if ("function" != typeof e) throw new TypeError;
                for (var n = [], t = arguments[1], o = 0; i > o; o++)
                    if (o in this) {
                        var a = this[o];
                        e.call(t, a, o, this) && n.push(a)
                    }
                return n
            });
            var o = function() {
                    var t = e("body");
                    t.on("keydown", function(e) {
                        (e.keyCode === KEY_ESC || e.keyCode === KEY_TAB) && n.$panel.hide()
                    }), t.on("mouseup", function(i) {
                        i = i.originalEvent || i;
                        var t = i.target || window;
                        e(t).hasClass("emoji-picker") || n.$panel.hide()
                    }), t.on("click", "#"+summernote_id+" + .note-editor .emoji-menu-tab", function(i) {
                        i.stopPropagation();
                        var n = 0,
                            t = e(this).attr("class").split(" ");
                        t = t[1].split("-"), 3 !== t.length && (t = t[0] + "-" + t[1], e("#"+summernote_id+" + .note-editor .emoji-menu-tabs td").each(function(i) {
                            var o = e(this).find("a"),
                                a = o.attr("class").split(" ");
                            a = a[1].split("-"), a = a[0] + "-" + a[1], t === a ? (o.attr("class", "emoji-menu-tab " + a + "-selected"), n = i) : o.attr("class", "emoji-menu-tab " + a)
                        }), s(n))
                    }), e(document).on("click", "#"+summernote_id+" + .note-editor .emoji-items2 a", function() {
                        var t = e(".label", e(this)).text();
                        if ("unicode" === document.emojiType) 
						{
							context.invoke('editor.insertText', c(t));
                            return;
                            //$editor.insertText($editable, c(t));
                            //editor.insertText($editable, 'Text');
						}
                        else {
                            var o = e(r(n.icons[t]));
                            o[0].attachEvent && o[0].attachEvent("onresizestart", function(e) {
                                e.returnValue = !1
                            }, !1), context.invoke("editor.insertNode", o[0])
                            context.invoke('editor.insertText', ' ');
                        }
                        ConfigStorage.get("emojis_recent", function(e) {
                            e = e || Config.defaultRecentEmojis || [];
                            var i = e.indexOf(t);
                            return i ? (-1 !== i && e.splice(i, 1), e.unshift(t), e.length > 42 && (e = e.slice(42)), void ConfigStorage.set({
                                emojis_recent: e
                            })) : !1
                        })
                    })
                },
                a = function() {
                    var e, i, t, o, a, s, r, c, l, m;
                    for (o = void 0, s = void 0, t = void 0, a = {}, c = {}, r = void 0, i = void 0, l = void 0, e = void 0, m = void 0, s = 0; s < Config.EmojiCategories.length;) {
                        for (m = Config.EmojiCategorySpritesheetDimens[s][1], o = 0; o < Config.EmojiCategories[s].length;) i = Config.Emoji[Config.EmojiCategories[s][o]], r = i[1][0], l = Math.floor(o / m), e = o % m, a[":" + r + ":"] = [s, l, e, ":" + r + ":"], c[r] = i[0], o++;
                        s++
                    }
                    n.icons = a, n.reverseIcons = c, Config.rx_codes || Config.init_unified()
                },
                s = function(i) {
                    var t = $("#e-"+summernote_id);
                        t.html(""), i > 0 ? e.each(n.icons, function(e, o) {
                            n.icons.hasOwnProperty(e) && o[0] === i - 1 && t.append('<a href="javascript:void(0)" title="' + Config.htmlEntities(e) + '">' + r(o, !0) + '<span class="label">' + Config.htmlEntities(e) + "</span></a>")
                            //t.append('<a href="javascript:void(0)" title="' + Config.htmlEntities(e) + '">' + r(o, !0) + '<span class="label">' + Config.htmlEntities(e) + "</span></a>")
                        }) : ConfigStorage.get("emojis_recent", function(e) {
                            e = e || Config.defaultRecentEmojis || [];
                            var i, o;
                            for (o = 0; o < e.length; o++) i = e[o], n.icons[i] && t.append('<a href="javascript:void(0)" title="' + Config.htmlEntities(i) + '">' + r(n.icons[i], !0) + '<span class="label">' + Config.htmlEntities(i) + "</span></a>")
                        })
                },
                r = function(e) {
                    var i = e[0],
                        n = e[1],
                        t = e[2],
                        o = e[3],
                        a = document.emojiSource + "/emoji_spritesheet_!.png",
                        s = document.emojiSource + "/blank.gif",
                        r = 25,
                        c = -(r * t),
                        l = -(r * n),
                        m = Config.EmojiCategorySpritesheetDimens[i][1] * r,
                        u = Config.EmojiCategorySpritesheetDimens[i][0] * r,
                        d = "display:inline-block;";
                    return d += "width:" + r + "px;", d += "height:" + r + "px;", d += "background:url('" + a.replace("!", i) + "') " + c + "px " + l + "px no-repeat;", d += "background-size:" + m + "px " + u + "px;", '<img src="' + s + '" class="img tamemoji" emoji="'+e[3]+'" style="' + d + '" alt="' + Config.htmlEntities(o) + '">'
                },
                c = function(e) {
                    return e.replace(Config.rx_colons, function(e) {
                        var i;
                        return i = Config.mapcolon[e], i ? i : ""
                    })
                };
            i.memo("button.emoji", function() {
                var e = t.button({
                    contents: '<i class="fa fa-smile-o emoji-picker-container emoji-picker"></i>',
                    click: function() {
                        void 0 === document.emojiSource && (document.emojiSource = ""), void 0 === document.emojiType && (document.emojiType = "");
                        var e = n.$panel.width();
                        e > n.$panel.position().left && n.$panel.css({
                            left: "10px"
                        }), n.$panel.show()
                    }
                });
                return n.emoji = e.render(), n.emoji
            }), this.events = {
                "summernote.init": function(e, i) {
                    o()
                }
            }, this.initialize = function(editorInfo) {
                this.$panel = e('<div class="emoji-menu">\n    <div class="emoji-items-wrap1">\n        <table class="emoji-menu-tabs">\n            <tbody>\n            <tr>\n                <td><a class="emoji-menu-tab icon-recent-selected"></a></td>\n                <td><a class="emoji-menu-tab icon-smile"></a></td>\n                <td><a class="emoji-menu-tab icon-flower"></a></td>\n                <td><a class="emoji-menu-tab icon-bell"></a></td>\n                <td><a class="emoji-menu-tab icon-car"></a></td>\n                <td><a class="emoji-menu-tab icon-grid"></a></td>\n            </tr>\n            </tbody>\n        </table>\n        <div class="emoji-items-wrap mobile_scrollable_wrap">\n            <div class="emoji-items2" id="e-'+summernote_id+'"></div>\n        </div>\n    </div>\n</div>').hide(), this.$panel.appendTo(n.emoji), a(), s(0)
            }, this.destroy = function() {
                this.$panel.remove(), this.$panel = null
            }
        }
    })
});