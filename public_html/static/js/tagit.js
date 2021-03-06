(function (a) {
    a.widget("ui.tagit", {options: {allowDuplicates: false, caseSensitive: false, fieldName: "tags", placeholderText: "Tags (Max 5)", readOnly: false, removeConfirmation: false, tagLimit: 5, availableTags: [], autocomplete: {}, showAutocompleteOnFocus: false, allowSpaces: false, singleField: false, singleFieldDelimiter: ",", singleFieldNode: null, animate: true, tabIndex: null, beforeTagAdded: null, afterTagAdded: null, beforeTagRemoved: null, afterTagRemoved: null, onTagClicked: null, onTagLimitExceeded: null, onTagAdded: null, onTagRemoved: null, tagSource: null}, _create: function () {
        var f = this;
        if (this.element.is("input")) {
            this.tagList = a("<ul></ul>").insertAfter(this.element);
            this.options.singleField = true;
            this.options.singleFieldNode = this.element;
            this.element.css("display", "none")
        } else {
            this.tagList = this.element.find("ul, ol").andSelf().last()
        }
        this.tagInput = a('<input type="text" />').addClass("ui-widget-content");
        if (this.options.readOnly) {
            this.tagInput.attr("disabled", "disabled")
        }
        if (this.options.tabIndex) {
            this.tagInput.attr("tabindex", this.options.tabIndex)
        }
        if (this.options.placeholderText) {
            this.tagInput.attr("placeholder", this.options.placeholderText)
        }
        if (!this.options.autocomplete.source) {
            this.options.autocomplete.source = function (h, g) {
                var i = h.term.toLowerCase();
                var j = a.grep(this.options.availableTags, function (k) {
                    return(k.toLowerCase().indexOf(i) === 0)
                });
                g(this._subtractArray(j, this.assignedTags()))
            }
        }
        if (this.options.showAutocompleteOnFocus) {
            this.tagInput.focus(function (g, h) {
                f._showAutocomplete()
            });
            if (typeof this.options.autocomplete.minLength === "undefined") {
                this.options.autocomplete.minLength = 0
            }
        }
        if (a.isFunction(this.options.autocomplete.source)) {
            this.options.autocomplete.source = a.proxy(this.options.autocomplete.source, this)
        }
        if (a.isFunction(this.options.tagSource)) {
            this.options.tagSource = a.proxy(this.options.tagSource, this)
        }
        this.tagList.addClass("tagit").addClass("ui-widget ui-widget-content ui-corner-all").append(a('<li class="tagit-new"></li>').append(this.tagInput)).click(function (i) {
            var h = a(i.target);
            if (h.hasClass("tagit-label")) {
                var g = h.closest(".tagit-choice");
                if (!g.hasClass("removed")) {
                    f._trigger("onTagClicked", i, {tag: g, tagLabel: f.tagLabel(g)})
                }
            } else {
                f.tagInput.focus()
            }
        });
        var d = false;
        if (this.options.singleField) {
            if (this.options.singleFieldNode) {
                var e = a(this.options.singleFieldNode);
                var c = e.val().split(this.options.singleFieldDelimiter);
                e.val("");
                a.each(c, function (h, g) {
                    f.createTag(g, null, true);
                    d = true
                })
            } else {
                this.options.singleFieldNode = a('<input type="hidden" style="display:none;" value="" name="' + this.options.fieldName + '" />');
                this.tagList.after(this.options.singleFieldNode)
            }
        }
        if (!d) {
            this.tagList.children("li").each(function () {
                if (!a(this).hasClass("tagit-new")) {
                    f.createTag(a(this).text(), a(this).attr("class"), true);
                    a(this).remove()
                }
            })
        }
        this.tagInput.keydown(function (h) {
            if (h.which == a.ui.keyCode.BACKSPACE && f.tagInput.val() === "") {
                var g = f._lastTag();
                if (!f.options.removeConfirmation || g.hasClass("remove")) {
                    f.removeTag(g)
                } else {
                    if (f.options.removeConfirmation) {
                        g.addClass("remove ui-state-highlight")
                    }
                }
            } else {
                if (f.options.removeConfirmation) {
                    f._lastTag().removeClass("remove ui-state-highlight")
                }
            }
            if (h.which === a.ui.keyCode.COMMA || h.which === a.ui.keyCode.ENTER || (h.which == a.ui.keyCode.TAB && f.tagInput.val() !== "") || (h.which == a.ui.keyCode.SPACE && f.options.allowSpaces !== true && (a.trim(f.tagInput.val()).replace(/^s*/, "").charAt(0) != '"' || (a.trim(f.tagInput.val()).charAt(0) == '"' && a.trim(f.tagInput.val()).charAt(a.trim(f.tagInput.val()).length - 1) == '"' && a.trim(f.tagInput.val()).length - 1 !== 0)))) {
                if (!(h.which === a.ui.keyCode.ENTER && f.tagInput.val() === "")) {
                    h.preventDefault()
                }
                f.createTag(f._cleanedInput());
                f.tagInput.autocomplete("close")
            }
        }).blur(function (g) {
            if (!f.tagInput.data("autocomplete-open")) {
                f.createTag(f._cleanedInput())
            }
        });
        if (this.options.availableTags || this.options.tagSource || this.options.autocomplete.source) {
            var b = {select: function (g, h) {
                f.createTag(h.item.value);
                return false
            }};
            a.extend(b, this.options.autocomplete);
            b.source = this.options.tagSource || b.source;
            this.tagInput.autocomplete(b).bind("autocompleteopen",function (g, h) {
                f.tagInput.data("autocomplete-open", true)
            }).bind("autocompleteclose", function (g, h) {
                f.tagInput.data("autocomplete-open", false)
            })
        }
    }, _cleanedInput: function () {
        return a.trim(this.tagInput.val().replace(/^"(.*)"$/, "$1")).replace(/\s/g, "")
    }, _lastTag: function () {
        return this.tagList.find(".tagit-choice:last:not(.removed)")
    }, _tags: function () {
        return this.tagList.find(".tagit-choice:not(.removed)")
    }, assignedTags: function () {
        var c = this;
        var b = [];
        if (this.options.singleField) {
            b = a(this.options.singleFieldNode).val().split(this.options.singleFieldDelimiter);
            if (b[0] === "") {
                b = []
            }
        } else {
            this._tags().each(function () {
                b.push(c.tagLabel(this))
            })
        }
        return b
    }, _updateSingleTagsField: function (b) {
        a(this.options.singleFieldNode).val(b.join(this.options.singleFieldDelimiter)).trigger("change")
    }, _subtractArray: function (d, c) {
        var b = [];
        for (var e = 0; e < d.length; e++) {
            if (a.inArray(d[e], c) == -1) {
                b.push(d[e])
            }
        }
        return b
    }, tagLabel: function (b) {
        if (this.options.singleField) {
            return a(b).find(".tagit-label:first").text()
        } else {
            return a(b).find("input:first").val()
        }
    }, _showAutocomplete: function () {
        this.tagInput.autocomplete("search", "")
    }, _findTagByLabel: function (c) {
        var d = this;
        var b = null;
        this._tags().each(function (e) {
            if (d._formatStr(c) == d._formatStr(d.tagLabel(this))) {
                b = a(this);
                return false
            }
        });
        return b
    }, _isNew: function (b) {
        return !this._findTagByLabel(b)
    }, _formatStr: function (b) {
        if (this.options.caseSensitive) {
            return b
        }
        return a.trim(b.toLowerCase())
    }, _effectExists: function (b) {
        return Boolean(a.effects && (a.effects[b] || (a.effects.effect && a.effects.effect[b])))
    }, createTag: function (i, f, b) {
        var e = this;
        i = a.trim(i);
        if (i === "") {
            return false
        }
        if (!this.options.allowDuplicates && !this._isNew(i)) {
            var d = this._findTagByLabel(i);
            if (this._trigger("onTagExists", null, {existingTag: d, duringInitialization: b}) !== false) {
                if (this._effectExists("highlight")) {
                    d.effect("highlight")
                }
            }
            return false
        }
        if (this.options.tagLimit && this._tags().length >= this.options.tagLimit) {
            this._trigger("onTagLimitExceeded", null, {duringInitialization: b});
            return false
        }
        var h = a(this.options.onTagClicked ? '<a class="tagit-label"></a>' : '<span class="tagit-label"></span>').text(i);
        var l = a("<li></li>").addClass("tagit-choice ui-widget-content ui-state-default ui-corner-all").addClass(f).append(h);
        if (this.options.readOnly) {
            l.addClass("tagit-choice-read-only")
        } else {
            l.addClass("tagit-choice-editable");
            var c = a("<span></span>").addClass("ui-icon ui-icon-close");
            var g = a('<a><span class="text-icon">\xd7</span></a>').addClass("tagit-close").append(c).click(function (m) {
                e.removeTag(l)
            });
            l.append(g)
        }
        if (!this.options.singleField) {
            var k = h.html();
            l.append('<input type="hidden" style="display:none;" value="' + k + '" name="' + this.options.fieldName + '" />')
        }
        if (this._trigger("beforeTagAdded", null, {tag: l, tagLabel: this.tagLabel(l), duringInitialization: b}) === false) {
            return
        }
        if (this.options.singleField) {
            var j = this.assignedTags();
            j.push(i);
            this._updateSingleTagsField(j)
        }
        this._trigger("onTagAdded", null, l);
        this.tagInput.val("");
        this.tagInput.parent().before(l);
        this._trigger("afterTagAdded", null, {tag: l, tagLabel: this.tagLabel(l), duringInitialization: b});
        if (this.options.showAutocompleteOnFocus && !b) {
            setTimeout(function () {
                e._showAutocomplete()
            }, 0)
        }
    }, removeTag: function (b, c) {
        c = typeof c === "undefined" ? this.options.animate : c;
        b = a(b);
        this._trigger("onTagRemoved", null, b);
        if (this._trigger("beforeTagRemoved", null, {tag: b, tagLabel: this.tagLabel(b)}) === false) {
            return
        }
        if (this.options.singleField) {
            var e = this.assignedTags();
            var d = this.tagLabel(b);
            e = a.grep(e, function (g) {
                return g != d
            });
            this._updateSingleTagsField(e)
        }
        if (c) {
            b.addClass("removed");
            var f = this._effectExists("blind") ? ["blind", {direction: "horizontal"}, "fast"] : ["fast"];
            f.push(function () {
                b.remove()
            });
            b.fadeOut("fast").hide.apply(b, f).dequeue()
        } else {
            b.remove()
        }
        this._trigger("afterTagRemoved", null, {tag: b, tagLabel: this.tagLabel(b)})
    }, removeTagByLabel: function (d, b) {
        var c = this._findTagByLabel(d);
        if (!c) {
            throw"No such tag exists with the name '" + d + "'"
        }
        this.removeTag(c, b)
    }, removeAll: function () {
        var b = this;
        this._tags().each(function (d, c) {
            b.removeTag(c, false)
        })
    }})
})(jQuery);