M.mod_deffinumform = {};
M.mod_deffinumform.init = function(Y) {
    var deffinumform = Y.one('#deffinumviewform');
    var cwidth = deffinumplayerdata.cwidth;
    var cheight = deffinumplayerdata.cheight;
    var poptions = deffinumplayerdata.popupoptions;
    var launch = deffinumplayerdata.launch;
    var currentorg = deffinumplayerdata.currentorg;
    var sco = deffinumplayerdata.sco;
    var deffinum = deffinumplayerdata.deffinum;
    var launch_url = M.cfg.wwwroot + "/mod/deffinum/player.php?a=" + deffinum + "&currentorg=" + currentorg + "&scoid=" + sco + "&sesskey=" + M.cfg.sesskey + "&display=popup";
    var course_url = deffinumplayerdata.courseurl;
    var winobj = null;

    poptions = poptions + ',resizable=yes'; // Added for IE (MDL-32506).

    if ((cwidth == 100) && (cheight == 100)) {
        poptions = poptions + ',width=' + screen.availWidth + ',height=' + screen.availHeight + ',left=0,top=0';
    } else {
        if (cwidth <= 100) {
            cwidth = Math.round(screen.availWidth * cwidth / 100);
        }
        if (cheight <= 100) {
            cheight = Math.round(screen.availHeight * cheight / 100);
        }
        poptions = poptions + ',width=' + cwidth + ',height=' + cheight;
    }

    // Hide the form and toc if it exists - we don't want to allow multiple submissions when a window is open.
    var deffinumload = function () {
        if (deffinumform) {
            deffinumform.hide();
        }

        var deffinumtoc = Y.one('#toc');
        if (deffinumtoc) {
            deffinumtoc.hide();
        }
        // Hide the intro and display a message to the user if the window is closed.
        var deffinumintro = Y.one('#intro');
        deffinumintro.setHTML('<a href="' + course_url + '">' + M.util.get_string('popuplaunched', 'deffinum') + '</a>');
    }

    // When pop-up is closed return to course homepage.
    var deffinumunload = function () {
        // Onunload is called multiple times in the DEFFINUM window - we only want to handle when it is actually closed.
        setTimeout(function() {
            if (winobj.closed) {
                window.location = course_url;
            }
        }, 800)
    }

    var deffinumredirect = function (winobj) {
        Y.on('load', deffinumload, winobj);
        Y.on('unload', deffinumunload, winobj);
        // Check to make sure pop-up has been launched - if not display a warning,
        // this shouldn't happen as the pop-up here is launched on user action but good to make sure.
        setTimeout(function() {
            if (!winobj) {
                var deffinumintro = Y.one('#intro');
                deffinumintro.setHTML(M.util.get_string('popupsblocked', 'deffinum'));
            }}, 800);
    }

    // Set mode and newattempt correctly.
    var setlaunchoptions = function(mode) {
        if (mode) {
            launch_url += '&mode=' + (mode ? mode : 'normal');
        } else {
            launch_url += '&mode=normal';
        }

        var newattempt = Y.one('#deffinumviewform #a');
        launch_url += (newattempt && newattempt.get('checked') ? '&newattempt=on' : '');
    }

    if (launch == true) {
        setlaunchoptions();
        winobj = window.open(launch_url,'Popup', poptions);
        this.target = 'Popup';
        deffinumredirect(winobj);
        winobj.opener = null;
    }
    // Listen for view form submit and generate popup on user interaction.
    if (deffinumform) {
        deffinumform.delegate('click', function(e) {
            setlaunchoptions(e.currentTarget.getAttribute('value'));
            winobj = window.open(launch_url, 'Popup', poptions);
            this.target = 'Popup';
            deffinumredirect(winobj);
            winobj.opener = null;
            e.preventDefault();
        }, 'button[name=mode]');
    }
}
