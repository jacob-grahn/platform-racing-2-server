"use strict";

var menu_css_decoded = window.atob("LmhjbGFzc3sNCiAgICBkaXNwbGF5OiBub25lOw0KfQ0KI3BybWVudXsNCiAgICBiYWNrZ3JvdW5kLWNvbG9yOiAjMDA4MEZGOw0KICAgIGJvcmRlcjogMHB4IHNvbGlkICMwMDAwMDA7DQogICAgYm9yZGVyLXJhZGl1czogNXB4Ow0KICAgIHdpZHRoOiAyNTBweDsNCiAgICBoZWlnaHQ6IDQwMHB4Ow0KICAgIHBvc2l0aW9uOiBhYnNvbHV0ZTsNCiAgICBvdmVyZmxvdy15OiBhdXRvOw0KICAgIGFuaW1hdGlvbjogbG9hZG1lbnUgMC4yNXMgc3RlcHMoNjAsZW5kKTsNCiAgICBib3gtc2hhZG93OiAwcHggMHB4IDIwcHggIzAwMDAwMDsNCiAgICB6LWluZGV4OiA5OTk5Ow0KfQ0KI21lbnVidXR0b257DQogICAgcG9zaXRpb246IGZpeGVkOw0KICAgIHRvcDogMjVweDsNCiAgICBsZWZ0OiAyNXB4Ow0KICAgIGhlaWdodDogMzBweDsNCiAgICB3aWR0aDogMTIwcHg7DQogICAgYmFja2dyb3VuZC1jb2xvcjogIzAwMDAwMDsNCiAgICBib3gtc2hhZG93OiAwcHggMHB4IDIwcHggIzAwMDAwMDsNCiAgICB6LWluZGV4OiA5OTk1Ow0KfQ0KI210ZXh0ew0KICAgIGRpc3BsYXk6IGJsb2NrOw0KICAgIGZvbnQtc2l6ZTogMjJweDsNCiAgICBjb2xvcjogI0ZGRkZGRjsNCiAgICBwb3NpdGlvbjogcmVsYXRpdmU7DQogICAgbGVmdDogMHB4Ow0KICAgIHRleHQtYWxpZ246IGNlbnRlcjsNCiAgICB0ZXh0LWRlY29yYXRpb246IG5vbmU7DQp9DQojbXRleHQ6aG92ZXJ7DQogICAgY29sb3I6ICMwMDAwMDA7DQogICAgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRjsNCiAgICB0cmFuc2l0aW9uLWR1cmF0aW9uOiAwLjE1czsNCiAgICAtbW96LXRyYW5zaXRpb24tZHVyYXRpb246IDAuMTVzOw0KICAgIC13ZWJraXQtdHJhbnNpdGlvbi1kdXJhdGlvbjogMC4xNXM7DQp9DQojbXRleHQ6bm90KDpob3Zlcil7DQogICAgdHJhbnNpdGlvbi1kdXJhdGlvbjogMC4xNXM7DQogICAgLW1vei10cmFuc2l0aW9uLWR1cmF0aW9uOiAwLjE1czsNCiAgICAtd2Via2l0LXRyYW5zaXRpb24tZHVyYXRpb246IDAuMTVzOw0KfQ0Kc3Bhbi5tZW51dGl0bGV7DQogICAgY29sb3I6ICNGQUNDMkU7DQogICAgZm9udC1zaXplOiAyNnB4Ow0KICAgIGJhY2tncm91bmQtY29sb3I6ICMwMDAwMDA7DQogICAgZGlzcGxheTogYmxvY2s7DQogICAgICAgIHRleHQtYWxpZ246IGNlbnRlcjsNCn0NCmhyLm1lbnVkaXZpZGVyew0KICAgIHdpZHRoOiA1MCU7DQogICAgICAgIHRleHQtYWxpZ246IGNlbnRlcjsNCn0NCmEubWVudWxpbmt7DQogICAgY29sb3I6ICNGQUNDMkU7DQogICAgZGlzcGxheTogYmxvY2s7DQogICAgd29yZC13cmFwOiBicmVhay13b3JkOw0KICAgIGZvbnQtc2l6ZTogMjJweDsNCiAgICBwYWRkaW5nLWxlZnQ6IDEwcHg7DQogICAgdGV4dC1kZWNvcmF0aW9uOiBub25lOw0KfQ0KYS5tZW51bGluazpob3ZlcnsNCiAgICBiYWNrZ3JvdW5kLWNvbG9yOiAjMDAwMDAwOw0KICAgIHRyYW5zaXRpb24tZHVyYXRpb246IDAuMTVzOw0KICAgIC1tb3otdHJhbnNpdGlvbi1kdXJhdGlvbjogMC4xNXM7DQogICAgLXdlYmtpdC10cmFuc2l0aW9uLWR1cmF0aW9uOiAwLjE1czsNCiAgICBkaXNwbGF5OiBibG9jazsNCn0NCmEubWVudWxpbms6bm90KDpob3Zlcil7DQogICAgdHJhbnNpdGlvbi1kdXJhdGlvbjogMC4xNXM7DQogICAgLW1vei10cmFuc2l0aW9uLWR1cmF0aW9uOiAwLjE1czsNCiAgICAtd2Via2l0LXRyYW5zaXRpb24tZHVyYXRpb246IDAuMTVzOw0KICAgIGRpc3BsYXk6IGJsb2NrOw0KfQ0Kc3Bhbi5tZW51dGV4dHsNCiAgICBkaXNwbGF5OiBibG9jazsNCiAgICB0ZXh0LWFsaWduOiBjZW50ZXI7DQogICAgY29sb3I6ICNGRkZGRkY7DQogICAgZm9udC1zdHlsZTogaXRhbGljOw0KfQ0KQGtleWZyYW1lcyBsb2FkbWVudXsNCiAgICBmcm9tew0KICAgICAgICBoZWlnaHQ6IDBweDsNCiAgICAgICAgb3BhY2l0eTogMDsNCiAgICB9Ow0KfQ==");

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
    var ban_id = window.prompt("Enter the ban ID.");
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
    var page_num = window.prompt("Enter the page to which you'd like to go (100 bans per page). Enter 0 to go to the start of the ban log.");
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
    var hubmenu = document.getElementById("prmenu");
    hubmenu.style.left = (115 + document.body.scrollLeft + document.documentElement.scrollLeft) + "px";
    hubmenu.style.top = (50 + document.body.scrollTop + document.documentElement.scrollTop) + "px";
    hubmenu.style.display = "block";
}

function user_menu_hide()
{
    document.getElementById("prmenu").style.display = "none";
}

function initialize_menu()
{
    document.getElementById("prmenu").innerHTML = "<span class='menutitle'><img src='/favicon.ico' width='20px' height='20px'></img> PR2 Hub</span>" +
                                                  "<hr class='menudivider'></hr>" +
                                                  "<a href='#/' class='menulink' id='setbg'>Set Background</a>" +
                                                  "<a href='#/' class='menulink' id='srch_player'>Player Search</a>" +
                                                  "<a href='#/' class='menulink' id='guild_srch'>Guild Search</a>" +
                                                  "<a href='#/' class='menulink' id='menu_leaderboard'>Leaderboard</a>" +
                                                  "<a href='#/' class='menulink' id='arti_hint'>Artifact Hint</a>" +
                                                  "<a href='#/' class='menulink' id='transfer_guild'>Transfer Guild</a>" +
                                                  "<a href='#/' class='menulink' id='staff_list'>PR2 Staff Team</a>" +
                                                  "<a href='#/' class='menulink' id='ban_list'>Ban Log</a>" +
                                                  "<a href='#/' class='menulink' id='skip_to_ban'>Skip to Ban Log Page</a>" +
                                                  "<a href='#/' class='menulink' id='banview'>View Ban</a>" +
                                                  "<a href='#/' class='menulink' id='menu_close'>Close</a>" +
                                                  "<br><span class='menutext'>You can open this menu from anywhere using the F8 key.</span>";
}

function add_menu_button()
{
    var create_btn = document.createElement("div");
    create_btn.setAttribute("id", "menubutton");
    create_btn.innerHTML = "<a href='#' id='mtext'>-- Menu --</a>";
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
    
    document.querySelector('#container').addEventListener('click', function (m_event) {
        user_menu_hide();
    })

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

    document.addEventListener("keydown", function (keyinfo) {
        if (keyinfo.keyCode === 119) {
            menuHotkey();
        }
    });
    
    document.addEventListener("scroll", function (e_args) {
        update_menu_position();
    });
};
