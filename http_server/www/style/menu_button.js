"use strict";

var menu_css_decoded = window.atob("LmhjbGFzc3sNCiAgICBkaXNwbGF5OiBub25lOw0KfQ0KI3BybWVudXsNCiAgICBiYWNrZ3JvdW5kLWNvbG9yOiAjMDA4MEZGOw0KICAgIGJvcmRlcjogMHB4IHNvbGlkICMwMDAwMDA7DQogICAgYm9yZGVyLXJhZGl1czogNXB4Ow0KICAgIHdpZHRoOiAyNTBweDsNCiAgICBoZWlnaHQ6IDQwMHB4Ow0KICAgIHBvc2l0aW9uOiBhYnNvbHV0ZTsNCiAgICBvdmVyZmxvdy15OiBhdXRvOw0KICAgIGFuaW1hdGlvbjogbG9hZG1lbnUgMC4yNXMgc3RlcHMoNjAsZW5kKTsNCiAgICBib3gtc2hhZG93OiAwcHggMHB4IDIwcHggIzAwMDAwMDsNCiAgICB6LWluZGV4OiA5OTk5Ow0KfQ0KI21lbnVidXR0b257DQogICAgcG9zaXRpb246IGZpeGVkOw0KICAgIHRvcDogMjVweDsNCiAgICBsZWZ0OiAyNXB4Ow0KICAgIGhlaWdodDogMzBweDsNCiAgICB3aWR0aDogMTIwcHg7DQogICAgYmFja2dyb3VuZC1jb2xvcjogIzAwMDAwMDsNCiAgICBib3gtc2hhZG93OiAwcHggMHB4IDIwcHggIzAwMDAwMDsNCiAgICB6LWluZGV4OiA5OTk1Ow0KfQ0KI210ZXh0ew0KICAgIGRpc3BsYXk6IGJsb2NrOw0KICAgIGZvbnQtc2l6ZTogMjJweDsNCiAgICBjb2xvcjogI0ZGRkZGRjsNCiAgICBwb3NpdGlvbjogcmVsYXRpdmU7DQogICAgbGVmdDogMHB4Ow0KICAgIHRleHQtYWxpZ246IGNlbnRlcjsNCiAgICB0ZXh0LWRlY29yYXRpb246IG5vbmU7DQp9DQojbXRleHQ6aG92ZXJ7DQogICAgY29sb3I6ICMwMDAwMDA7DQogICAgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRjsNCiAgICB0cmFuc2l0aW9uLWR1cmF0aW9uOiAwLjE1czsNCiAgICAtbW96LXRyYW5zaXRpb24tZHVyYXRpb246IDAuMTVzOw0KICAgIC13ZWJraXQtdHJhbnNpdGlvbi1kdXJhdGlvbjogMC4xNXM7DQogICAgY3Vyc29yOiBwb2ludGVyOw0KfQ0KI210ZXh0Om5vdCg6aG92ZXIpew0KICAgIHRyYW5zaXRpb24tZHVyYXRpb246IDAuMTVzOw0KICAgIC1tb3otdHJhbnNpdGlvbi1kdXJhdGlvbjogMC4xNXM7DQogICAgLXdlYmtpdC10cmFuc2l0aW9uLWR1cmF0aW9uOiAwLjE1czsNCn0NCnNwYW4ubWVudXRpdGxlew0KICAgIGNvbG9yOiAjRkFDQzJFOw0KICAgIGZvbnQtc2l6ZTogMjZweDsNCiAgICBiYWNrZ3JvdW5kLWNvbG9yOiAjMDAwMDAwOw0KICAgIGRpc3BsYXk6IGJsb2NrOw0KICAgIHRleHQtYWxpZ246IGNlbnRlcjsNCn0NCnNwYW4jZ29ob21lOmhvdmVyew0KICAgIHRleHQtZGVjb3JhdGlvbjogdW5kZXJsaW5lOw0KICAgIGN1cnNvcjogcG9pbnRlcjsNCn0NCmhyLm1lbnVkaXZpZGVyew0KICAgIHdpZHRoOiA1MCU7DQogICAgdGV4dC1hbGlnbjogY2VudGVyOw0KfQ0Kc3Bhbi5tZW51bGlua3sNCiAgICBjb2xvcjogI0ZBQ0MyRTsNCiAgICBkaXNwbGF5OiBibG9jazsNCiAgICB3b3JkLXdyYXA6IGJyZWFrLXdvcmQ7DQogICAgZm9udC1zaXplOiAyMnB4Ow0KICAgIHBhZGRpbmctbGVmdDogMTVweDsNCiAgICB0ZXh0LWRlY29yYXRpb246IG5vbmU7DQogICAgY3Vyc29yOiBwb2ludGVyOw0KfQ0Kc3Bhbi5tZW51bGluazpob3ZlcnsNCiAgICBiYWNrZ3JvdW5kLWNvbG9yOiAjMDAwMDAwOw0KICAgIHRyYW5zaXRpb24tZHVyYXRpb246IDAuMTVzOw0KICAgIC1tb3otdHJhbnNpdGlvbi1kdXJhdGlvbjogMC4xNXM7DQogICAgLXdlYmtpdC10cmFuc2l0aW9uLWR1cmF0aW9uOiAwLjE1czsNCiAgICBkaXNwbGF5OiBibG9jazsNCiAgICBjdXJzb3I6IHBvaW50ZXI7DQp9DQpzcGFuLm1lbnVsaW5rOm5vdCg6aG92ZXIpew0KICAgIHRyYW5zaXRpb24tZHVyYXRpb246IDAuMTVzOw0KICAgIC1tb3otdHJhbnNpdGlvbi1kdXJhdGlvbjogMC4xNXM7DQogICAgLXdlYmtpdC10cmFuc2l0aW9uLWR1cmF0aW9uOiAwLjE1czsNCiAgICBkaXNwbGF5OiBibG9jazsNCiAgICBjdXJzb3I6IHBvaW50ZXI7DQp9DQpzcGFuLnNtYWxsdGV4dHsNCiAgICBjb2xvcjogI0ZGRkZGRjsNCiAgICBmb250LXN0eWxlOiBpdGFsaWM7DQogICAgZm9udC1zaXplOiAxM3B4Ow0KfQ0Kc3Bhbi5zbWFsbHRleHQ6aG92ZXJ7DQogICAgdGV4dC1kZWNvcmF0aW9uOiB1bmRlcmxpbmU7DQp9DQpzcGFuLm1lbnV0ZXh0ew0KICAgIGRpc3BsYXk6IGJsb2NrOw0KICAgIHRleHQtYWxpZ246IGNlbnRlcjsNCiAgICBjb2xvcjogI0ZGRkZGRjsNCiAgICBmb250LXN0eWxlOiBpdGFsaWM7DQp9DQpAa2V5ZnJhbWVzIGxvYWRtZW51ew0KICAgIGZyb217DQogICAgICAgIGhlaWdodDogMHB4Ow0KICAgICAgICBvcGFjaXR5OiAwOw0KICAgIH07DQp9");

function insert_css()
{
    var menucss = document.createElement("style");
    menucss.innerHTML = menu_css_decoded;
    document.body.appendChild(menucss);
}

function insert_menu_code()
{
    var hub_menu = document.createElement("div");
    hub_menu.setAttribute("class", "hclass");
    hub_menu.setAttribute("id", "prmenu");
    document.body.appendChild(hub_menu);
}

function update_menu_position()
{
    if (document.getElementById("prmenu").style.display === "block") {
        document.getElementById("prmenu").style.display = "none";
    }
}

function goto_ban()
{
    var ban_id = window.prompt("Enter the ban ID of the ban you'd like to view. Enter 0 or click cancel to view your bans.");
    if (ban_id !== null && ban_id !== "" && isNaN(ban_id) === false) {
        location.href = "/bans/show_record.php?ban_id=" + ban_id;
    }
}

function set_background()
{
    var user_bg_url = window.prompt("Enter a direct image link to use as a background.");
    if (user_bg_url !== null && user_bg_url !== "") {
        if (user_bg_url.startsWith("http://") === true || user_bg_url.startsWith("https://") === true) {
            document.body.style.cssText = "background-image: url(" + user_bg_url + "); background-attachment: fixed; background-size: cover";
        } else {
            alert("That doesn't seem to be a valid link...");
        }
    }
}

function skip_to_page()
{
    var page_num = window.prompt("Enter the page to which you'd like to go (100 bans per page). Enter 0 or press cancel to go to the start of the ban log.");
    if (page_num !== null && page_num !== "" && isNaN(page_num) === false) {
        location.replace("/bans/bans.php?start=" + page_num * 100 + "&count=100");
    }
}

function open_link(link)
{
    location.href = "//pr2hub.com/" + link;
}

function displaymenu(ev)
{
    var hubmenu = document.getElementById("prmenu");
    hubmenu.style.left = (ev.clientX + document.body.scrollLeft + document.documentElement.scrollLeft) + "px";
    hubmenu.style.top = (ev.clientY + document.body.scrollTop + document.documentElement.scrollTop) + "px";
    hubmenu.style.display = "block";
    ev.returnValue = false;
}

function menuHotkey()
{
    var hotkeymenu = document.getElementById("prmenu");
    hotkeymenu.style.left = (115 + document.body.scrollLeft + document.documentElement.scrollLeft) + "px";
    hotkeymenu.style.top = (50 + document.body.scrollTop + document.documentElement.scrollTop) + "px";
    hotkeymenu.style.display = "block";
}

function user_menu_hide()
{
    document.getElementById("prmenu").style.display = "none";
}

function initialize_menu()
{
    document.getElementById("prmenu").innerHTML = "<span class='menutitle'><img src='/favicon.ico' width='20px' height='20px'></img> <span id='gohome'>PR2 Hub</span></span>" +
                                                  "<hr class='menudivider'></hr>" +
                                                  "<span class='menulink' id='setbg'>Set Background</span>" +
                                                  "<span class='menulink' id='srch_player'>Player Search</span>" +
                                                  "<span class='menulink' id='guild_srch'>Guild Search</span>" +
                                                  "<span class='menulink' id='menu_leaderboard'>Leaderboard</span>" +
                                                  "<span class='menulink' id='arti_hint'>Artifact Hint</span>" +
                                                  "<span class='menulink' id='transfer_guild'>Transfer Guild</span>" +
                                                  "<span class='menulink' id='staff_list'>PR2 Staff Team</span>" +
                                                  "<span class='menulink' id='ban_list'>Ban Log <span class='smalltext' id='skip_to_ban'>(or specify page)</span></span>" +
                                                  "<span class='menulink' id='ban_priors'>Your Bans <span class='smalltext' id='banview'>(or specify ban ID)</span></span>" +
                                                  "<span class='menulink' id='menu_close'>Close</span><br>" +
                                                  "<span class='menutext'>You can open this menu from anywhere using the F8 key.</span>";
}

function add_menu_button()
{
    var create_btn = document.createElement("div");
    create_btn.setAttribute("id", "menubutton");
    create_btn.innerHTML = "<span id='mtext'>-- Menu --</span>";
    document.body.appendChild(create_btn);
}

window.onload = function () {
    insert_css();

    insert_menu_code();

    initialize_menu();

    add_menu_button();

    document.getElementById("mtext").addEventListener("click", function (eventargs) {
        displaymenu(eventargs);
    });

    document.getElementById("menu_close").addEventListener("click", function (m_event) {
        user_menu_hide();
    });
    
    document.querySelector('#container').addEventListener('click', function (containerevent) {
        user_menu_hide();
    })
    
    document.getElementById("gohome").addEventListener("click", function () {
        user_menu_hide();
        open_link('');
    });

    document.getElementById("banview").addEventListener("click", function () {
        user_menu_hide();
        goto_ban();
    });

    document.getElementById("setbg").addEventListener("click", function () {
        user_menu_hide();
        set_background();
    });

    document.getElementById("skip_to_ban").addEventListener("click", function () {
        user_menu_hide();
        skip_to_page();
    });

    document.getElementById("menu_leaderboard").addEventListener("click", function () {
        user_menu_hide();
        open_link('leaderboard.php');
    });
    document.getElementById("arti_hint").addEventListener("click", function () {
        user_menu_hide();
        open_link('hint.php');
    });

    document.getElementById("srch_player").addEventListener("click", function () {
        user_menu_hide();
        open_link('player_search.php');
    });

    document.getElementById("guild_srch").addEventListener("click", function () {
        user_menu_hide();
        open_link('guild_search.php');
    });

    document.getElementById("staff_list").addEventListener("click", function () {
        user_menu_hide();
        open_link('staff.php');
    });

    document.getElementById("transfer_guild").addEventListener("click", function () {
        user_menu_hide();
        open_link('guild_transfer.php');
    });

    document.getElementById("ban_list").addEventListener("click", function () {
        user_menu_hide();
        open_link('bans/bans.php');
    });
    
    document.getElementById("ban_priors").addEventListener("click", function () {
        user_menu_hide();
        open_link('bans/view_priors.php');
    });

    document.addEventListener("keydown", function (keyinfo) {
        if (keyinfo.keyCode === 119) {
            menuHotkey();
        }
    });
    
    document.addEventListener("scroll", function (e_args) {
        update_menu_position();
    });
};
