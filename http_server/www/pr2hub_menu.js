"use strict";

var menu_css_decoded = window.atob("LmhjbGFzc3sNCglkaXNwbGF5OiBub25lOw0KfQ0KI3BybWVudXsNCgliYWNrZ3JvdW5kLWNvbG9yOiAjMDA4MEZGOw0KCWJvcmRlcjogMHB4IHNvbGlkICMwMDAwMDA7DQoJYm9yZGVyLXJhZGl1czogNXB4Ow0KCXdpZHRoOiAyNTBweDsNCgloZWlnaHQ6IDQwMHB4Ow0KCXBvc2l0aW9uOiBhYnNvbHV0ZTsNCglvdmVyZmxvdy15OiBhdXRvOw0KCWFuaW1hdGlvbjogbG9hZG1lbnUgMC41cyBzdGVwcyg2MCxlbmQpOw0KCWJveC1zaGFkb3c6IDBweCAwcHggMjBweCAjMDAwMDAwOw0KCXotaW5kZXg6IDk5OTk7DQp9DQojbWVudWJ1dHRvbnsNCglwb3NpdGlvbjogZml4ZWQ7DQoJdG9wOiAwcHg7DQoJbGVmdDogMHB4Ow0KCWhlaWdodDogMzBweDsNCgl3aWR0aDogODBweDsNCgliYWNrZ3JvdW5kLWNvbG9yOiAjMDAwMDAwOw0KCWJveC1zaGFkb3c6IDBweCAwcHggMjBweCAjMDAwMDAwOw0KCXotaW5kZXg6IDk5OTU7DQp9DQojbXRleHR7DQoJZGlzcGxheTogYmxvY2s7DQoJZm9udC1zaXplOiAyMnB4Ow0KCWNvbG9yOiAjRkZGRkZGOw0KCXBvc2l0aW9uOiByZWxhdGl2ZTsNCglsZWZ0OiAwcHg7DQp9DQojbXRleHQ6aG92ZXJ7DQoJY29sb3I6ICMwMDAwMDA7DQoJYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRjsNCgl0cmFuc2l0aW9uLWR1cmF0aW9uOiAxczsNCgktbW96LXRyYW5zaXRpb24tZHVyYXRpb246IDFzOw0KCS13ZWJraXQtdHJhbnNpdGlvbi1kdXJhdGlvbjogMXM7DQp9DQojbXRleHQ6bm90KDpob3Zlcil7DQoJdHJhbnNpdGlvbi1kdXJhdGlvbjogMXM7DQoJLW1vei10cmFuc2l0aW9uLWR1cmF0aW9uOiAxczsNCgktd2Via2l0LXRyYW5zaXRpb24tZHVyYXRpb246IDFzOw0KfQ0Kc3Bhbi5tZW51dGl0bGV7DQoJY29sb3I6ICNGQUNDMkU7DQoJZm9udC1zaXplOiAyNnB4Ow0KCWJhY2tncm91bmQtY29sb3I6ICMwMDAwMDA7DQoJZGlzcGxheTogYmxvY2s7DQp9DQpoci5tZW51ZGl2aWRlcnsNCgl3aWR0aDogNTAlOw0KfQ0KYS5tZW51bGlua3sNCgljb2xvcjogI0ZBQ0MyRTsNCglkaXNwbGF5OiBibG9jazsNCgl3b3JkLXdyYXA6IGJyZWFrLXdvcmQ7DQoJZm9udC1zaXplOiAyMnB4Ow0KfQ0KYS5tZW51bGluazpob3ZlcnsNCgliYWNrZ3JvdW5kLWNvbG9yOiAjMDAwMDAwOw0KCXRyYW5zaXRpb24tZHVyYXRpb246IDFzOw0KCS1tb3otdHJhbnNpdGlvbi1kdXJhdGlvbjogMXM7DQoJLXdlYmtpdC10cmFuc2l0aW9uLWR1cmF0aW9uOiAxczsNCglkaXNwbGF5OiBibG9jazsNCn0NCmEubWVudWxpbms6bm90KDpob3Zlcil7DQoJdHJhbnNpdGlvbi1kdXJhdGlvbjogMXM7DQoJLW1vei10cmFuc2l0aW9uLWR1cmF0aW9uOiAxczsNCgktd2Via2l0LXRyYW5zaXRpb24tZHVyYXRpb246IDFzOw0KCWRpc3BsYXk6IGJsb2NrOw0KfQ0KQGtleWZyYW1lcyBsb2FkbWVudXsNCglmcm9tew0KCQloZWlnaHQ6IDBweDsNCgkJb3BhY2l0eTogMDsNCgl9Ow0KfQ==");

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

function goto_ban()
{
    var ban_id = window.prompt("Enter ban id");
    if (ban_id !== null && ban_id !== "" && isNaN(ban_id) === false) {
        location.href = "/bans/show_record.php?ban_id=" + ban_id;
    } else {
        //no value given
    }
}

function set_background()
{
    var user_bg_url = window.prompt("Enter direct link to image to use as background");
    if (user_bg_url !== null && user_bg_url !== "") {
        if (user_bg_url.startsWith("http://") === true || user_bg_url.startsWith("https://") === true) {
            document.body.style.cssText = "background-image: url(" + user_bg_url + "); background-attachment: fixed; background-size: cover";
        } else {
            alert("Given link doesn't seem valid");
        }
    } else {
        //no value given
    }
}

function skip_to_page()
{
    var page_num = window.prompt("Page number on bans to skip to (pages start at 0)");
    if (page_num !== null && page_num !== "" && isNaN(page_num) === false) {
        location.replace("/bans/bans.php?start=" + page_num * 100 + "&count=100");
    } else {
        //invalid value or no value given
    }
}

function open_leaderboard()
{
    location.href = "/leaderboard.php";
}

function open_arti_page()
{
    location.href = "/hint.php";
}

function open_player_search()
{
    location.href = "/player_search.php";
}

function open_guild_search()
{
    location.href = "/guild_search.php";
}

function open_staff_list()
{
    location.href = "/staff.php";
}

function open_guild_transfer()
{
    location.href = "/guild_transfer.php";
}

function open_ban_list()
{
    location.href = "/bans/bans.php";
}

function displaymenu(ev)
{
	var hubmenu = document.getElementById("prmenu");
	hubmenu.style.left = (ev.clientX + document.body.scrollLeft + document.documentElement.scrollLeft) + "px";
	hubmenu.style.top = (ev.clientY + document.body.scrollTop + document.documentElement.scrollTop) + "px";
	hubmenu.style.display = "block";
	ev.returnValue = false;
}

function user_menu_hide()
{
    document.getElementById("prmenu").style.display = "none";
}

function initialize_menu()
{
    document.getElementById("prmenu").innerHTML = "<center><span class=\"menutitle\"><img src=\"https://pr2hub.com/favicon.ico\" width=\"20px\" height=\"20px\"></img> Pr2Hub Menu</span><hr class=\"menudivider\"></hr><a href=\"#\" class=\"menulink\" id=\"banview\">View ban by ID</a><a href=\"#\" class=\"menulink\" id=\"setbg\">Set background</a><a href=\"#\" class=\"menulink\" id=\"skip_to_ban\">Go to specified page on bans</a><a href=\"#\" class=\"menulink\" id=\"menu_leaderboard\">Leaderboard</a><a href=\"#\" class=\"menulink\" id=\"ban_list\">Bans</a><a href=\"#\" class=\"menulink\" id=\"arti_hint\">Artifact hint</a><a href=\"#\" class=\"menulink\" id=\"srch_player\">Player search</a><a href=\"#\" class=\"menulink\" id=\"guild_srch\">Guild search</a><a href=\"#\" class=\"menulink\" id=\"staff_list\">Staff list</a><a href=\"#\" class=\"menulink\" id=\"transfer_guild\">Guild transfer</a><a href=\"#\" class=\"menulink\" id=\"menu_close\">Close menu</a></center>";
}

function add_menu_button()
{
    var create_btn = document.createElement("div");
    create_btn.setAttribute("id", "menubutton");
    create_btn.innerHTML = "<a href=\"#\" id=\"mtext\">-Menu-</a>";
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

    document.getElementById("banview").addEventListener("click", function () {
        goto_ban();
    });

    document.getElementById("setbg").addEventListener("click", function () {
        set_background();
    });

    document.getElementById("skip_to_ban").addEventListener("click", function () {
        skip_to_page();
    });

    document.getElementById("menu_leaderboard").addEventListener("click", function () {
        open_leaderboard();
    });
    document.getElementById("arti_hint").addEventListener("click", function () {
        open_arti_page();
    });

    document.getElementById("srch_player").addEventListener("click", function () {
        open_player_search();
    });

    document.getElementById("guild_srch").addEventListener("click", function () {
        open_guild_search();
    });

    document.getElementById("staff_list").addEventListener("click", function () {
        open_staff_list();
    });

    document.getElementById("transfer_guild").addEventListener("click", function () {
        open_guild_transfer();
    });

    document.getElementById("ban_list").addEventListener("click", function () {
        open_ban_list();
    });

    document.addEventListener("keydown", function (keyinfo) {
        if (keyinfo.keyCode === 119) {
            document.getElementById("mtext").click();
        }
    });
};
