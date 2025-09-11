// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript helper function for DEFFINUM module.
 *
 * @package   mod-deffinum
 * @copyright 2009 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

mod_deffinum_launch_next_sco = null;
mod_deffinum_launch_prev_sco = null;
mod_deffinum_activate_item = null;
mod_deffinum_parse_toc_tree = null;
deffinum_layout_widget = null;

window.deffinum_current_node = null;

function underscore(str) {
    str = String(str).replace(/.N/g,".");
    return str.replace(/\./g,"__");
}

M.mod_deffinum = {};

M.mod_deffinum.init = function(Y, nav_display, navposition_left, navposition_top, hide_toc, collapsetocwinsize, toc_title, window_name, launch_sco, scoes_nav) {
    var deffinum_disable_toc = false;
    var deffinum_hide_nav = true;
    var deffinum_hide_toc = true;
    var launch_sco_fix = launch_sco;
    if (hide_toc == 0) {
        if (nav_display !== 0) {
            deffinum_hide_nav = false;
        }
        deffinum_hide_toc = false;
    } else if (hide_toc == 3) {
        deffinum_disable_toc = true;
    }

    scoes_nav = Y.JSON.parse(scoes_nav);

    var deffinum_update_siblings = function (scoesnav) {
        for(var key in scoesnav ){
            var siblings = [],
                parentscoid = key;
            for (var mk in scoesnav) {
                var val = scoesnav[mk];
                if (typeof val !== "undefined" && typeof val.parentscoid !== 'undefined' && val.parentscoid === parentscoid) {
                    siblings.push(mk);
                }
            }
            if (siblings.length > 1) {
                scoesnav = deffinum_get_siblings(scoesnav, siblings);
            }
        }
        return scoesnav;
    };

    var deffinum_get_siblings = function (scoesnav, siblings) {
        siblings.forEach(function (key, index) {
            if (index > 0 && typeof scoesnav[key] !== "undefined" && typeof scoesnav[key].prevsibling === "undefined") {
                scoesnav[key].prevsibling = siblings[index - 1];
            }
            if (index < siblings.length - 1 && typeof scoesnav[key] !== "undefined" &&
               typeof scoesnav[key].nextsibling === "undefined") {
                scoesnav[key].nextsibling = siblings[index + 1];
            }
        });
        return scoesnav;
    };

    scoes_nav = deffinum_update_siblings(scoes_nav);
    var deffinum_buttons = [];
    var deffinum_bloody_labelclick = false;
    var deffinum_nav_panel;

    Y.use('button', 'dd-plugin', 'panel', 'resize', 'gallery-sm-treeview', function(Y) {

        Y.TreeView.prototype.getNodeByAttribute = function(attribute, value) {
            var node = null,
                domnode = Y.one('a[' + attribute + '="' + value + '"]');
            if (domnode !== null) {
                node = deffinum_tree_node.getNodeById(domnode.ancestor('li').get('id'));
            }
            return node;
        };

        Y.TreeView.prototype.openAll = function () {
            this.get('container').all('.yui3-treeview-can-have-children').each(function(target) {
                this.getNodeById(target.get('id')).open();
            }, this);
        };

        Y.TreeView.prototype.closeAll = function () {
            this.get('container').all('.yui3-treeview-can-have-children').each(function(target) {
                this.getNodeById(target.get('id')).close();
            }, this);
        }

        var deffinum_parse_toc_tree = function(srcNode) {
            var SELECTORS = {
                    child: '> li',
                    label: '> li, > a',
                    textlabel : '> li, > span',
                    subtree: '> ul, > li'
                },
                children = [];

            srcNode.all(SELECTORS.child).each(function(childNode) {
                var child = {},
                    labelNode = childNode.one(SELECTORS.label),
                    textNode = childNode.one(SELECTORS.textlabel),
                    subTreeNode = childNode.one(SELECTORS.subtree);

                if (labelNode) {
                    var title = labelNode.getAttribute('title');
                    var scoid = labelNode.getData('scoid');
                    child.label = labelNode.get('outerHTML');
                    // Will be good to change to url instead of title.
                    if (title && title !== '#') {
                        child.title = title;
                    }
                    if (typeof scoid !== 'undefined') {
                        child.scoid = scoid;
                    }
                } else if (textNode) {
                    // The selector did not find a label node with anchor.
                    child.label = textNode.get('outerHTML');
                }

                if (subTreeNode) {
                    child.children = deffinum_parse_toc_tree(subTreeNode);
                }

                children.push(child);
            });

            return children;
        };

        mod_deffinum_parse_toc_tree = deffinum_parse_toc_tree;

        var deffinum_activate_item = function(node) {
            if (!node) {
                return;
            }
            // Check if the item is already active, avoid recursive calls.
            var content = Y.one('#deffinum_content');
            var old = Y.one('#deffinum_object');
            if (old) {
                var deffinum_active_url = Y.one('#deffinum_object').getAttribute('src');
                var node_full_url = M.cfg.wwwroot + '/mod/deffinum/loadSCO.php?' + node.title;
                if (node_full_url === deffinum_active_url) {
                    return;
                }
                // Start to unload iframe here
                if(!window_name){
                    content.removeChild(old);
                    old = null;
                }
            }
            // End of - Avoid recursive calls.

            deffinum_current_node = node;
            if (!deffinum_current_node.state.selected) {
                deffinum_current_node.select();
            }

            deffinum_tree_node.closeAll();
            var url_prefix = M.cfg.wwwroot + '/mod/deffinum/loadSCO.php?';
            var el_old_api = document.getElementById('deffinumapi123');
            if (el_old_api) {
                el_old_api.parentNode.removeChild(el_old_api);
            }

            var obj = document.createElement('iframe');
            obj.setAttribute('id', 'deffinum_object');
            obj.setAttribute('type', 'text/html');
            obj.setAttribute('allowfullscreen', 'allowfullscreen');
            obj.setAttribute('webkitallowfullscreen', 'webkitallowfullscreen');
            obj.setAttribute('mozallowfullscreen', 'mozallowfullscreen');
            if (!window_name && node.title != null) {
                obj.setAttribute('src', url_prefix + node.title);
            }
            // Attach unload observers to the iframe. The deffinum package may be observing these unload events
            // and trying to save progress when they occur. We need to ensure we use the Beacon API in those
            // situations.
            if (typeof mod_deffinum_monitorForBeaconRequirement !== 'undefined') {
                mod_deffinum_monitorForBeaconRequirement(obj);
            }
            if (window_name) {
                var mine = window.open('','','width=1,height=1,left=0,top=0,scrollbars=no');
                if(! mine) {
                    alert(M.util.get_string('popupsblocked', 'deffinum'));
                }
                mine.close();
            }

            if (old) {
                if(window_name) {
                    var cwidth = deffinumplayerdata.cwidth;
                    var cheight = deffinumplayerdata.cheight;
                    var poptions = deffinumplayerdata.popupoptions;
                    poptions = poptions + ',resizable=yes'; // Added for IE (MDL-32506).
                    deffinum_openpopup(M.cfg.wwwroot + "/mod/deffinum/loadSCO.php?" + node.title, window_name, poptions, cwidth, cheight);
                }
            } else {
                content.prepend(obj);
            }

            if (deffinum_hide_nav == false) {
                if (nav_display === 1 && navposition_left > 0 && navposition_top > 0) {
                    Y.one('#deffinum_object').addClass(cssclasses.deffinum_nav_under_content);
                }
                deffinum_fixnav();
            }
            deffinum_tree_node.openAll();
        };

        mod_deffinum_activate_item = deffinum_activate_item;

        /**
         * Enables/disables navigation buttons as needed.
         * @return void
         */
        var deffinum_fixnav = function() {
            launch_sco_fix = launch_sco;
            var skipprevnode = deffinum_skipprev(deffinum_current_node);
            var prevnode = deffinum_prev(deffinum_current_node);
            var upnode = deffinum_up(deffinum_current_node);
            var nextnode = deffinum_next(deffinum_current_node, true, true);
            var skipnextnode = deffinum_skipnext(deffinum_current_node, true, true);

            deffinum_buttons[0].set('disabled', ((skipprevnode === null) ||
                        (typeof(skipprevnode.scoid) === 'undefined') ||
                        (scoes_nav[skipprevnode.scoid].isvisible === "false") ||
                        (skipprevnode.title === null) ||
                        (scoes_nav[launch_sco].hideprevious === 1)));

            deffinum_buttons[1].set('disabled', ((prevnode === null) ||
                        (typeof(prevnode.scoid) === 'undefined') ||
                        (scoes_nav[prevnode.scoid].isvisible === "false") ||
                        (prevnode.title === null) ||
                        (scoes_nav[launch_sco].hideprevious === 1)));

            deffinum_buttons[2].set('disabled', (upnode === null) ||
                        (typeof(upnode.scoid) === 'undefined') ||
                        (scoes_nav[upnode.scoid].isvisible === "false") ||
                        (upnode.title === null));

            deffinum_buttons[3].set('disabled', ((nextnode === null) ||
                        ((nextnode.title === null) && (scoes_nav[launch_sco].flow !== 1)) ||
                        (typeof(nextnode.scoid) === 'undefined') ||
                        (scoes_nav[nextnode.scoid].isvisible === "false") ||
                        (scoes_nav[launch_sco].hidecontinue === 1)));

            deffinum_buttons[4].set('disabled', ((skipnextnode === null) ||
                        (skipnextnode.title === null) ||
                        (typeof(skipnextnode.scoid) === 'undefined') ||
                        (scoes_nav[skipnextnode.scoid].isvisible === "false") ||
                        scoes_nav[launch_sco].hidecontinue === 1));
        };

        var deffinum_toggle_toc = function(windowresize) {
            var toc = Y.one('#deffinum_toc');
            var deffinum_content = Y.one('#deffinum_content');
            var deffinum_toc_toggle_btn = Y.one('#deffinum_toc_toggle_btn');
            var toc_disabled = toc.hasClass('disabled');
            var disabled_by = toc.getAttribute('disabled-by');
            // Remove width element style from resize handle.
            toc.setStyle('width', null);
            deffinum_content.setStyle('width', null);
            if (windowresize === true) {
                if (disabled_by === 'user') {
                    return;
                }
                var body = Y.one('body');
                if (body.get('winWidth') < collapsetocwinsize) {
                    toc.addClass(cssclasses.disabled)
                        .setAttribute('disabled-by', 'screen-size');
                    deffinum_toc_toggle_btn.setHTML('&gt;')
                        .set('title', M.util.get_string('show', 'moodle'));
                    deffinum_content.removeClass(cssclasses.deffinum_grid_content_toc_visible)
                        .addClass(cssclasses.deffinum_grid_content_toc_hidden);
                } else if (body.get('winWidth') > collapsetocwinsize) {
                    toc.removeClass(cssclasses.disabled)
                        .removeAttribute('disabled-by');
                    deffinum_toc_toggle_btn.setHTML('&lt;')
                        .set('title', M.util.get_string('hide', 'moodle'));
                    deffinum_content.removeClass(cssclasses.deffinum_grid_content_toc_hidden)
                        .addClass(cssclasses.deffinum_grid_content_toc_visible);
                }
                return;
            }
            if (toc_disabled) {
                toc.removeClass(cssclasses.disabled)
                    .removeAttribute('disabled-by');
                deffinum_toc_toggle_btn.setHTML('&lt;')
                    .set('title', M.util.get_string('hide', 'moodle'));
                deffinum_content.removeClass(cssclasses.deffinum_grid_content_toc_hidden)
                    .addClass(cssclasses.deffinum_grid_content_toc_visible);
            } else {
                toc.addClass(cssclasses.disabled)
                    .setAttribute('disabled-by', 'user');
                deffinum_toc_toggle_btn.setHTML('&gt;')
                    .set('title', M.util.get_string('show', 'moodle'));
                deffinum_content.removeClass(cssclasses.deffinum_grid_content_toc_visible)
                    .addClass(cssclasses.deffinum_grid_content_toc_hidden);
            }
        };

        var deffinum_resize_layout = function() {
            if (window_name) {
                return;
            }

            // make sure that the max width of the TOC doesn't go to far

            var deffinum_toc_node = Y.one('#deffinum_toc');
            var maxwidth = parseInt(Y.one('#deffinum_layout').getComputedStyle('width'), 10);
            deffinum_toc_node.setStyle('maxWidth', (maxwidth - 200));
            var cwidth = parseInt(deffinum_toc_node.getComputedStyle('width'), 10);
            if (cwidth > (maxwidth - 1)) {
                deffinum_toc_node.setStyle('width', (maxwidth - 50));
            }

            // Calculate the rough new height from the viewport height.
            var newheight = Y.one('body').get('winHeight') - 5
                - Y.one('#deffinum_layout').getY()
                - window.pageYOffset;
            if (newheight < 680 || isNaN(newheight)) {
                newheight = 680;
            }
            Y.one('#deffinum_layout').setStyle('height', newheight);

        };

        /**
         * @deprecated as it is now unused.
         * @param {string} url
         * @param {string} datastring
         * @returns {string|*|boolean}
         */
        var deffinum_ajax_request = function(url, datastring) {
            var myRequest = NewHttpReq();
            var result = DoRequest(myRequest, url + datastring);
            return result;
        };

        var deffinum_up = function(node, update_launch_sco) {
            if (node.parent && node.parent.parent && typeof scoes_nav[launch_sco].parentscoid !== 'undefined') {
                var parentscoid = scoes_nav[launch_sco].parentscoid;
                var parent = node.parent;
                if (parent.title !== scoes_nav[parentscoid].url) {
                    parent = deffinum_tree_node.getNodeByAttribute('title', scoes_nav[parentscoid].url);
                    if (parent === null) {
                        parent = deffinum_tree_node.rootNode.children[0];
                        parent.title = scoes_nav[parentscoid].url;
                    }
                }
                if (update_launch_sco) {
                    launch_sco = parentscoid;
                }
                return parent;
            }
            return null;
        };

        var deffinum_lastchild = function(node) {
            if (node.children.length) {
                return deffinum_lastchild(node.children[node.children.length - 1]);
            } else {
                return node;
            }
        };

        var deffinum_prev = function(node, update_launch_sco) {
            if (node.previous() && node.previous().children.length &&
                    typeof scoes_nav[launch_sco].prevscoid !== 'undefined') {
                node = deffinum_lastchild(node.previous());
                if (node) {
                    var prevscoid = scoes_nav[launch_sco].prevscoid;
                    if (node.title !== scoes_nav[prevscoid].url) {
                        node = deffinum_tree_node.getNodeByAttribute('title', scoes_nav[prevscoid].url);
                        if (node === null) {
                            node = deffinum_tree_node.rootNode.children[0];
                            node.title = scoes_nav[prevscoid].url;
                        }
                    }
                    if (update_launch_sco) {
                        launch_sco = prevscoid;
                    }
                    return node;
                } else {
                    return null;
                }
            }
            return deffinum_skipprev(node, update_launch_sco);
        };

        var deffinum_skipprev = function(node, update_launch_sco) {
            if (node.previous() && typeof scoes_nav[launch_sco].prevsibling !== 'undefined') {
                var prevsibling = scoes_nav[launch_sco].prevsibling;
                var previous = node.previous();
                var prevscoid = scoes_nav[launch_sco].prevscoid;
                if (previous.title !== scoes_nav[prevscoid].url) {
                    previous = deffinum_tree_node.getNodeByAttribute('title', scoes_nav[prevsibling].url);
                    if (previous === null) {
                        previous = deffinum_tree_node.rootNode.children[0];
                        previous.title = scoes_nav[prevsibling].url;
                    }
                }
                if (update_launch_sco) {
                    launch_sco = prevsibling;
                }
                return previous;
            } else if (node.parent && node.parent.parent && typeof scoes_nav[launch_sco].parentscoid !== 'undefined') {
                var parentscoid = scoes_nav[launch_sco].parentscoid;
                var parent = node.parent;
                if (parent.title !== scoes_nav[parentscoid].url) {
                    parent = deffinum_tree_node.getNodeByAttribute('title', scoes_nav[parentscoid].url);
                    if (parent === null) {
                        parent = deffinum_tree_node.rootNode.children[0];
                        parent.title = scoes_nav[parentscoid].url;
                    }
                }
                if (update_launch_sco) {
                    launch_sco = parentscoid;
                }
                return parent;
            }
            return null;
        };

        var deffinum_next = function(node, update_launch_sco, test) {
            if (node === false) {
                return deffinum_tree_node.children[0];
            }
            if (node.children.length && typeof scoes_nav[launch_sco_fix].nextscoid != 'undefined') {
                node = node.children[0];
                var nextscoid = scoes_nav[launch_sco_fix].nextscoid;
                if (node.title !== scoes_nav[nextscoid].url) {
                    node = deffinum_tree_node.getNodeByAttribute('title', scoes_nav[nextscoid].url);
                    if (node === null) {
                        node = deffinum_tree_node.rootNode.children[0];
                        node.title = scoes_nav[nextscoid].url;
                    }
                }
                if (update_launch_sco) {
                    launch_sco_fix = nextscoid;
                    if (!test) {
                        launch_sco = launch_sco_fix;
                    }
                }
                return node;
            }
            return deffinum_skipnext(node, update_launch_sco, test);
        };

        var deffinum_skipnext = function(node, update_launch_sco, test) {
            var next = node.next();
            if (next && next.title && typeof scoes_nav[launch_sco_fix] !== 'undefined' &&
                        typeof scoes_nav[launch_sco_fix].nextsibling !== 'undefined') {
                var nextsibling = scoes_nav[launch_sco_fix].nextsibling;
                if (next.title !== scoes_nav[nextsibling].url) {
                    next = deffinum_tree_node.getNodeByAttribute('title', scoes_nav[nextsibling].url);
                    if (next === null) {
                        next = deffinum_tree_node.rootNode.children[0];
                        next.title = scoes_nav[nextsibling].url;
                    }
                }
                if (update_launch_sco) {
                    launch_sco_fix = nextsibling;
                    if (!test) {
                        launch_sco = launch_sco_fix;
                    }
                }
                return next;
            } else if (node.parent && node.parent.parent && typeof scoes_nav[launch_sco_fix].parentscoid !== 'undefined') {
                var parentscoid = scoes_nav[launch_sco_fix].parentscoid;
                var parent = node.parent;
                if (parent.title !== scoes_nav[parentscoid].url) {
                    parent = deffinum_tree_node.getNodeByAttribute('title', scoes_nav[parentscoid].url);
                    if (parent === null) {
                        parent = deffinum_tree_node.rootNode.children[0];
                    }
                }
                if (update_launch_sco) {
                    launch_sco_fix = parentscoid;
                    if (!test) {
                        launch_sco = launch_sco_fix;
                    }
                }
                return deffinum_skipnext(parent, update_launch_sco, test);
            }
            return null;
        };

        /**
         * Sends a request to the sequencing handler script on the server.
         * @param {string} datastring
         * @returns {string|boolean|*}
         */
        var deffinum_dorequest_sequencing = function(datastring) {
            var myRequest = NewHttpReq();
            var result = DoRequest(
                myRequest,
                M.cfg.wwwroot + '/mod/deffinum/datamodels/sequencinghandler.php?' + datastring,
                '',
                false
            );
            return result;
        };

        // Launch prev sco
        var deffinum_launch_prev_sco = function() {
            var result = null;
            if (scoes_nav[launch_sco].flow === 1) {
                var datastring = scoes_nav[launch_sco].url + '&function=deffinum_seq_flow&request=backward';
                result = deffinum_dorequest_sequencing(datastring);

                // Check the deffinum_ajax_result, it may be false.
                if (result === false) {
                    // Either the outcome was a failure, or we are unloading and simply just don't know
                    // what the outcome actually was.
                    result = {};
                } else {
                    result = Y.JSON.parse(result);
                }

                if (typeof result.nextactivity !== 'undefined' && typeof result.nextactivity.id !== 'undefined') {
                        var node = deffinum_prev(deffinum_tree_node.getSelectedNodes()[0]);
                        if (node == null) {
                            // Avoid use of TreeView for Navigation.
                            node = deffinum_tree_node.getSelectedNodes()[0];
                        }
                        if (node.title !== scoes_nav[result.nextactivity.id].url) {
                            node = deffinum_tree_node.getNodeByAttribute('title', scoes_nav[result.nextactivity.id].url);
                            if (node === null) {
                                node = deffinum_tree_node.rootNode.children[0];
                                node.title = scoes_nav[result.nextactivity.id].url;
                            }
                        }
                        launch_sco = result.nextactivity.id;
                        deffinum_activate_item(node);
                        deffinum_fixnav();
                } else {
                        deffinum_activate_item(deffinum_prev(deffinum_tree_node.getSelectedNodes()[0], true));
                }
            } else {
                deffinum_activate_item(deffinum_prev(deffinum_tree_node.getSelectedNodes()[0], true));
            }
        };

        // Launch next sco
        var deffinum_launch_next_sco = function () {
            launch_sco_fix = launch_sco;
            var result = null;
            if (scoes_nav[launch_sco].flow === 1) {
                var datastring = scoes_nav[launch_sco].url + '&function=deffinum_seq_flow&request=forward';
                result = deffinum_dorequest_sequencing(datastring);

                // Check the deffinum_ajax_result, it may be false.
                if (result === false) {
                    // Either the outcome was a failure, or we are unloading and simply just don't know
                    // what the outcome actually was.
                    result = {};
                } else {
                    result = Y.JSON.parse(result);
                }

                if (typeof result.nextactivity !== 'undefined' && typeof result.nextactivity.id !== 'undefined') {
                    var node = deffinum_next(deffinum_tree_node.getSelectedNodes()[0]);
                    if (node === null) {
                        // Avoid use of TreeView for Navigation.
                        node = deffinum_tree_node.getSelectedNodes()[0];
                    }
                    node = deffinum_tree_node.getNodeByAttribute('title', scoes_nav[result.nextactivity.id].url);
                    if (node === null) {
                        node = deffinum_tree_node.rootNode.children[0];
                        node.title = scoes_nav[result.nextactivity.id].url;
                    }
                    launch_sco = result.nextactivity.id;
                    launch_sco_fix = launch_sco;
                    deffinum_activate_item(node);
                    deffinum_fixnav();
                } else {
                    deffinum_activate_item(deffinum_next(deffinum_tree_node.getSelectedNodes()[0], true, false));
                }
            } else {
                deffinum_activate_item(deffinum_next(deffinum_tree_node.getSelectedNodes()[0], true,false));
            }
        };

        mod_deffinum_launch_prev_sco = deffinum_launch_prev_sco;
        mod_deffinum_launch_next_sco = deffinum_launch_next_sco;

        var cssclasses = {
                // YUI grid class: use 100% of the available width to show only content, TOC hidden.
                deffinum_grid_content_toc_hidden: 'yui3-u-1',
                // YUI grid class: use 1/5 of the available width to show TOC.
                deffinum_grid_toc: 'yui3-u-1-5',
                // YUI grid class: use 1/24 of the available width to show TOC toggle button.
                deffinum_grid_toggle: 'yui3-u-1-24',
                // YUI grid class: use 3/4 of the available width to show content, TOC visible.
                deffinum_grid_content_toc_visible: 'yui3-u-3-4',
                // Reduce height of #deffinum_object to accomodate nav buttons under content.
                deffinum_nav_under_content: 'deffinum_nav_under_content',
                disabled: 'disabled'
            };
        // layout
        Y.one('#deffinum_toc_title').setHTML(toc_title);

        if (deffinum_disable_toc) {
            Y.one('#deffinum_toc').addClass(cssclasses.disabled);
            Y.one('#deffinum_toc_toggle').addClass(cssclasses.disabled);
            Y.one('#deffinum_content').addClass(cssclasses.deffinum_grid_content_toc_hidden);
        } else {
            Y.one('#deffinum_toc').addClass(cssclasses.deffinum_grid_toc);
            Y.one('#deffinum_toc_toggle').addClass(cssclasses.deffinum_grid_toggle);
            Y.one('#deffinum_toc_toggle_btn')
                .setHTML('&lt;')
                .setAttribute('title', M.util.get_string('hide', 'moodle'));
            Y.one('#deffinum_content').addClass(cssclasses.deffinum_grid_content_toc_visible);
            deffinum_toggle_toc(true);
        }

        // hide the TOC if that is the default
        if (!deffinum_disable_toc) {
            if (deffinum_hide_toc == true) {
                Y.one('#deffinum_toc').addClass(cssclasses.disabled);
                Y.one('#deffinum_toc_toggle_btn')
                    .setHTML('&gt;')
                    .setAttribute('title', M.util.get_string('show', 'moodle'));
                Y.one('#deffinum_content')
                    .removeClass(cssclasses.deffinum_grid_content_toc_visible)
                    .addClass(cssclasses.deffinum_grid_content_toc_hidden);
            }
        }

        // Basic initialization completed, show the elements.
        Y.one('#deffinum_toc').removeClass('loading');
        Y.one('#deffinum_toc_toggle').removeClass('loading');

        // TOC Resize handle.
        var layout_width = parseInt(Y.one('#deffinum_layout').getComputedStyle('width'), 10);
        var deffinum_resize_handle = new Y.Resize({
            node: '#deffinum_toc',
            handles: 'r',
            defMinWidth: 0.2 * layout_width
        });
        // TOC tree
        var toc_source = Y.one('#deffinum_tree > ul');
        var toc = deffinum_parse_toc_tree(toc_source);
        // Empty container after parsing toc.
        var el = document.getElementById('deffinum_tree');
        el.innerHTML = '';
        var tree = new Y.TreeView({
            container: '#deffinum_tree',
            nodes: toc,
            multiSelect: false,
            lazyRender: false
        });
        deffinum_tree_node = tree;
        // Trigger after instead of on, avoid recursive calls.
        tree.after('select', function(e) {
            var node = e.node;
            if (node.title == '' || node.title == null) {
                return; //this item has no navigation
            }

            // If item is already active, return; avoid recursive calls.
            if (obj = Y.one('#deffinum_object')) {
                var deffinum_active_url = obj.getAttribute('src');
                var node_full_url = M.cfg.wwwroot + '/mod/deffinum/loadSCO.php?' + node.title;
                if (node_full_url === deffinum_active_url) {
                    return;
                }
            } else if(deffinum_current_node == node){
                return;
            }

            // Update launch_sco.
            if (typeof node.scoid !== 'undefined') {
                launch_sco = node.scoid;
            }
            deffinum_activate_item(node);
            if (node.children.length) {
                deffinum_bloody_labelclick = true;
            }
        });
        if (!deffinum_disable_toc) {
            tree.on('close', function(e) {
                if (deffinum_bloody_labelclick) {
                    deffinum_bloody_labelclick = false;
                    return false;
                }
            });
            tree.subscribe('open', function(e) {
                if (deffinum_bloody_labelclick) {
                    deffinum_bloody_labelclick = false;
                    return false;
                }
            });
        }
        tree.render();
        tree.openAll();

        // On getting the window, always set the focus on the current item
        Y.one(Y.config.win).on('focus', function (e) {
            var current = deffinum_tree_node.getSelectedNodes()[0];
            var toc_disabled = Y.one('#deffinum_toc').hasClass('disabled');
            if (current.id && !toc_disabled) {
                Y.one('#' + current.id).focus();
            }
        });

        // navigation
        if (deffinum_hide_nav == false) {
            // TODO: make some better&accessible buttons.
            var navbuttonshtml = '<span id="deffinum_nav"><button id="nav_skipprev">&lt;&lt;</button>&nbsp;' +
                                    '<button id="nav_prev">&lt;</button>&nbsp;<button id="nav_up">^</button>&nbsp;' +
                                    '<button id="nav_next">&gt;</button>&nbsp;<button id="nav_skipnext">&gt;&gt;</button></span>';
            if (nav_display === 1) {
                Y.one('#deffinum_navpanel').setHTML(navbuttonshtml);
            } else {
                // Nav panel is floating type.
                var navposition = null;
                if (navposition_left < 0 && navposition_top < 0) {
                    // Set default XY.
                    navposition = Y.one('#deffinum_toc').getXY();
                    navposition[1] += 200;
                } else {
                    // Set user defined XY.
                    navposition = [];
                    navposition[0] = parseInt(navposition_left, 10);
                    navposition[1] = parseInt(navposition_top, 10);
                }
                deffinum_nav_panel = new Y.Panel({
                    fillHeight: "body",
                    headerContent: M.util.get_string('navigation', 'deffinum'),
                    visible: true,
                    xy: navposition,
                    zIndex: 999
                });
                deffinum_nav_panel.set('bodyContent', navbuttonshtml);
                deffinum_nav_panel.removeButton('close');
                deffinum_nav_panel.plug(Y.Plugin.Drag, {handles: ['.yui3-widget-hd']});
                deffinum_nav_panel.render();
            }

            deffinum_buttons[0] = new Y.Button({
                srcNode: '#nav_skipprev',
                render: true,
                on: {
                        'click' : function(ev) {
                            deffinum_activate_item(deffinum_skipprev(deffinum_tree_node.getSelectedNodes()[0], true));
                        },
                        'keydown' : function(ev) {
                            if (ev.domEvent.keyCode === 13 || ev.domEvent.keyCode === 32) {
                                deffinum_activate_item(deffinum_skipprev(deffinum_tree_node.getSelectedNodes()[0], true));
                            }
                        }
                    }
            });
            deffinum_buttons[1] = new Y.Button({
                srcNode: '#nav_prev',
                render: true,
                on: {
                    'click' : function(ev) {
                        deffinum_launch_prev_sco();
                    },
                    'keydown' : function(ev) {
                        if (ev.domEvent.keyCode === 13 || ev.domEvent.keyCode === 32) {
                            deffinum_launch_prev_sco();
                        }
                    }
                }
            });
            deffinum_buttons[2] = new Y.Button({
                srcNode: '#nav_up',
                render: true,
                on: {
                    'click' : function(ev) {
                        deffinum_activate_item(deffinum_up(deffinum_tree_node.getSelectedNodes()[0], true));
                    },
                    'keydown' : function(ev) {
                        if (ev.domEvent.keyCode === 13 || ev.domEvent.keyCode === 32) {
                            deffinum_activate_item(deffinum_up(deffinum_tree_node.getSelectedNodes()[0], true));
                        }
                    }
                }
            });
            deffinum_buttons[3] = new Y.Button({
                srcNode: '#nav_next',
                render: true,
                on: {
                    'click' : function(ev) {
                        deffinum_launch_next_sco();
                    },
                    'keydown' : function(ev) {
                        if (ev.domEvent.keyCode === 13 || ev.domEvent.keyCode === 32) {
                            deffinum_launch_next_sco();
                        }
                    }
                }
            });
            deffinum_buttons[4] = new Y.Button({
                srcNode: '#nav_skipnext',
                render: true,
                on: {
                    'click' : function(ev) {
                        launch_sco_fix = launch_sco;
                        deffinum_activate_item(deffinum_skipnext(deffinum_tree_node.getSelectedNodes()[0], true, false));
                    },
                    'keydown' : function(ev) {
                        launch_sco_fix = launch_sco;
                        if (ev.domEvent.keyCode === 13 || ev.domEvent.keyCode === 32) {
                            deffinum_activate_item(deffinum_skipnext(deffinum_tree_node.getSelectedNodes()[0], true, false));
                        }
                    }
                }
            });
        }

        // finally activate the chosen item
        var deffinum_first_url = null;
        if (typeof tree.rootNode.children[0] !== 'undefined') {
            if (tree.rootNode.children[0].title !== scoes_nav[launch_sco].url) {
                var node = tree.getNodeByAttribute('title', scoes_nav[launch_sco].url);
                if (node !== null) {
                    deffinum_first_url = node;
                }
            } else {
                deffinum_first_url = tree.rootNode.children[0];
            }
        }

        if (deffinum_first_url == null) { // This is probably a single sco with no children (AICC Direct uses this).
            deffinum_first_url = tree.rootNode;
        }
        deffinum_first_url.title = scoes_nav[launch_sco].url;
        deffinum_activate_item(deffinum_first_url);

        // resizing
        deffinum_resize_layout();

        // Collapse/expand TOC.
        Y.one('#deffinum_toc_toggle').on('click', deffinum_toggle_toc);
        Y.one('#deffinum_toc_toggle').on('key', deffinum_toggle_toc, 'down:enter,32');
        // fix layout if window resized
        Y.on("windowresize", function() {
            deffinum_resize_layout();
            var toc_displayed = Y.one('#deffinum_toc').getComputedStyle('display') !== 'none';
            if ((!deffinum_disable_toc && !deffinum_hide_toc) || toc_displayed) {
                deffinum_toggle_toc(true);
            }
            // Set 20% as minWidth constrain of TOC.
            var layout_width = parseInt(Y.one('#deffinum_layout').getComputedStyle('width'), 10);
            deffinum_resize_handle.set('defMinWidth', 0.2 * layout_width);
        });
        // On resize drag, change width of deffinum_content.
        deffinum_resize_handle.on('resize:resize', function() {
            var tocwidth = parseInt(Y.one('#deffinum_toc').getComputedStyle('width'), 10);
            var layoutwidth = parseInt(Y.one('#deffinum_layout').getStyle('width'), 10);
            Y.one('#deffinum_content').setStyle('width', (layoutwidth - tocwidth - 60));
        });
    });
};

M.mod_deffinum.connectPrereqCallback = {

    success: function(id, o) {
        if (o.responseText !== undefined) {
            var snode = null,
                stitle = null;
            if (deffinum_tree_node && o.responseText) {
                snode = deffinum_tree_node.getSelectedNodes()[0];
                stitle = null;
                if (snode) {
                    stitle = snode.title;
                }
                // All gone with clear, add new root node.
                deffinum_tree_node.clear(deffinum_tree_node.createNode());
            }
            // Make sure the temporary tree element is not there.
            var el_old_tree = document.getElementById('deffinumtree123');
            if (el_old_tree) {
                el_old_tree.parentNode.removeChild(el_old_tree);
            }
            var el_new_tree = document.createElement('div');
            var pagecontent = document.getElementById("page-content");
            if (!pagecontent) {
                pagecontent = document.getElementById("content");
            }
            if (!pagecontent) {
                pagecontent = document.getElementById("deffinumpage");
            }
            el_new_tree.setAttribute('id','deffinumtree123');
            el_new_tree.innerHTML = o.responseText;
            // Make sure it does not show.
            el_new_tree.style.display = 'none';
            pagecontent.appendChild(el_new_tree);
            // Ignore the first level element as this is the title.
            var startNode = el_new_tree.firstChild.firstChild;
            if (startNode.tagName == 'LI') {
                // Go back to the beginning.
                startNode = el_new_tree;
            }
            var toc_source = Y.one('#deffinumtree123 > ul');
            var toc = mod_deffinum_parse_toc_tree(toc_source);
            deffinum_tree_node.appendNode(deffinum_tree_node.rootNode, toc);
            var el = document.getElementById('deffinumtree123');
            el.parentNode.removeChild(el);
            deffinum_tree_node.render();
            deffinum_tree_node.openAll();
            if (stitle !== null) {
                snode = deffinum_tree_node.getNodeByAttribute('title', stitle);
                // Do not let destroyed node to be selected.
                if (snode && !snode.state.destroyed) {
                    snode.select();
                    var toc_disabled = Y.one('#deffinum_toc').hasClass('disabled');
                    if (!toc_disabled) {
                        if (!snode.state.selected) {
                            snode.select();
                        }
                    }
                }
            }
        }
    },

    failure: function(id, o) {
        // TODO: do some sort of error handling.
    }

};
