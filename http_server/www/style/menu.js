"use strict";

var menu_css_decoded = window.atob("LmNsb3NlZHsNCiAgICB0cmFuc2l0aW9uLXByb3BlcnR5OiBhbGw7DQogICAgdHJhbnNpdGlvbi1kdXJhdGlvbjogMC4xNXM7DQogICAgdHJhbnNpdGlvbi10aW1pbmctZnVuY3Rpb246IGN1YmljLWJlemllcigwLCAxLCAwLjUsIDEpOw0KICAgIGhlaWdodDogMHB4Ow0KICAgIG9wYWNpdHk6IDA7DQogICAgZGlzcGxheTogbm9uZTsNCn0NCi5vcGVuew0KICAgIHRyYW5zaXRpb24tcHJvcGVydHk6IGFsbDsNCiAgICB0cmFuc2l0aW9uLWR1cmF0aW9uOiAuMjVzOw0KICAgIHRyYW5zaXRpb24tdGltaW5nLWZ1bmN0aW9uOiBjdWJpYy1iZXppZXIoMCwgMSwgMC41LCAxKTsNCiAgICBoZWlnaHQ6IDQwMHB4Ow0KICAgIG9wYWNpdHk6IDE7DQogICAgZGlzcGxheTogYmxvY2s7DQp9DQojcHJtZW51ew0KICAgIGJhY2tncm91bmQtY29sb3I6ICMwMDgwRkY7DQogICAgYm9yZGVyOiAwcHggc29saWQgIzAwMDAwMDsNCiAgICBib3JkZXItdG9wLXJpZ2h0LXJhZGl1czogNXB4Ow0KICAgIGJvcmRlci1ib3R0b20tbGVmdC1yYWRpdXM6IDVweDsNCiAgICBib3JkZXItYm90dG9tLXJpZ2h0LXJhZGl1czogNXB4Ow0KICAgIHBvc2l0aW9uOiBmaXhlZDsNCiAgICBtYXgtaGVpZ2h0OiA0MDBweDsNCiAgICBtYXgtd2lkdGg6IDI1MHB4Ow0KICAgIGxlZnQ6IDI1cHg7DQogICAgdG9wOiA1NXB4Ow0KICAgIG92ZXJmbG93LXk6IGF1dG87DQogICAgYm94LXNoYWRvdzogMHB4IDBweCAyMHB4ICMwMDAwMDA7DQoJaGVpZ2h0OiA0MDBweDsNCglhbmltYXRpb246IG1lbnVEaXNwbGF5IDAuMjVzIHN0ZXBzKDYwLGVuZCk7DQogICAgei1pbmRleDogOTk5OTsNCiAgICB1c2VyLXNlbGVjdDogbm9uZTsNCn0NCiNtZW51QnV0dG9uew0KICAgIHBvc2l0aW9uOiBmaXhlZDsNCiAgICB0b3A6IDI1cHg7DQogICAgbGVmdDogMjVweDsNCiAgICBoZWlnaHQ6IDMwcHg7DQogICAgd2lkdGg6IDEyMHB4Ow0KICAgIGJhY2tncm91bmQtY29sb3I6ICMwMDAwMDA7DQogICAgYm94LXNoYWRvdzogMHB4IDBweCAyMHB4ICMwMDAwMDA7DQogICAgei1pbmRleDogOTk5NTsNCiAgICB1c2VyLXNlbGVjdDogbm9uZTsNCn0NCiNtZW51QnV0dG9uVGV4dHsNCiAgICBkaXNwbGF5OiBibG9jazsNCiAgICBmb250LXNpemU6IDIycHg7DQogICAgY29sb3I6ICNGRkZGRkY7DQogICAgcG9zaXRpb246IHJlbGF0aXZlOw0KICAgIGxlZnQ6IDBweDsNCiAgICB0ZXh0LWFsaWduOiBjZW50ZXI7DQogICAgdGV4dC1kZWNvcmF0aW9uOiBub25lOw0KICAgIHVzZXItc2VsZWN0OiBub25lOw0KfQ0KI21lbnVCdXR0b25UZXh0OmhvdmVyew0KICAgIGNvbG9yOiAjMDAwMDAwOw0KICAgIGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkY7DQogICAgdHJhbnNpdGlvbi1kdXJhdGlvbjogMC4xNXM7DQogICAgLW1vei10cmFuc2l0aW9uLWR1cmF0aW9uOiAwLjE1czsNCiAgICAtd2Via2l0LXRyYW5zaXRpb24tZHVyYXRpb246IDAuMTVzOw0KICAgIGN1cnNvcjogcG9pbnRlcjsNCn0NCiNtZW51QnV0dG9uVGV4dDpub3QoOmhvdmVyKXsNCiAgICB0cmFuc2l0aW9uLWR1cmF0aW9uOiAwLjE1czsNCiAgICAtbW96LXRyYW5zaXRpb24tZHVyYXRpb246IDAuMTVzOw0KICAgIC13ZWJraXQtdHJhbnNpdGlvbi1kdXJhdGlvbjogMC4xNXM7DQp9DQpzcGFuLm1lbnVUaXRsZXsNCiAgICBjb2xvcjogI0ZBQ0MyRTsNCiAgICBmb250LXNpemU6IDI2cHg7DQogICAgYmFja2dyb3VuZC1jb2xvcjogIzAwMDAwMDsNCiAgICBkaXNwbGF5OiBibG9jazsNCiAgICB0ZXh0LWFsaWduOiBjZW50ZXI7DQp9DQpzcGFuI21lbnVfZ29Ib21lOmhvdmVyew0KICAgIHRleHQtZGVjb3JhdGlvbjogdW5kZXJsaW5lOw0KICAgIGN1cnNvcjogcG9pbnRlcjsNCn0NCmhyLm1lbnVEaXZpZGVyew0KICAgIHdpZHRoOiA1MCU7DQogICAgdGV4dC1hbGlnbjogY2VudGVyOw0KfQ0Kc3Bhbi5tZW51TGlua3sNCiAgICBjb2xvcjogI0ZBQ0MyRTsNCiAgICBkaXNwbGF5OiBibG9jazsNCiAgICB3b3JkLXdyYXA6IGJyZWFrLXdvcmQ7DQogICAgZm9udC1zaXplOiAyMnB4Ow0KICAgIHBhZGRpbmctbGVmdDogMTVweDsNCiAgICB0ZXh0LWRlY29yYXRpb246IG5vbmU7DQogICAgY3Vyc29yOiBwb2ludGVyOw0KfQ0Kc3Bhbi5tZW51TGluazpob3ZlcnsNCiAgICBiYWNrZ3JvdW5kLWNvbG9yOiAjMDAwMDAwOw0KICAgIHRyYW5zaXRpb24tZHVyYXRpb246IDAuMTVzOw0KICAgIC1tb3otdHJhbnNpdGlvbi1kdXJhdGlvbjogMC4xNXM7DQogICAgLXdlYmtpdC10cmFuc2l0aW9uLWR1cmF0aW9uOiAwLjE1czsNCiAgICBkaXNwbGF5OiBibG9jazsNCiAgICBjdXJzb3I6IHBvaW50ZXI7DQp9DQpzcGFuLm1lbnVMaW5rOm5vdCg6aG92ZXIpew0KICAgIHRyYW5zaXRpb24tZHVyYXRpb246IDAuMTVzOw0KICAgIC1tb3otdHJhbnNpdGlvbi1kdXJhdGlvbjogMC4xNXM7DQogICAgLXdlYmtpdC10cmFuc2l0aW9uLWR1cmF0aW9uOiAwLjE1czsNCiAgICBkaXNwbGF5OiBibG9jazsNCiAgICBjdXJzb3I6IHBvaW50ZXI7DQp9DQpzcGFuLnNtYWxsTGlua3sNCiAgICBjb2xvcjogI0ZGRkZGRjsNCiAgICBmb250LXN0eWxlOiBpdGFsaWM7DQogICAgZm9udC1zaXplOiAxM3B4Ow0KfQ0Kc3Bhbi5zbWFsbExpbms6aG92ZXJ7DQogICAgdGV4dC1kZWNvcmF0aW9uOiB1bmRlcmxpbmU7DQp9DQpzcGFuLnNtYWxsVGV4dHsNCiAgICBkaXNwbGF5OiBibG9jazsNCiAgICB0ZXh0LWFsaWduOiBjZW50ZXI7DQogICAgY29sb3I6ICNGRkZGRkY7DQogICAgZm9udC1zdHlsZTogaXRhbGljOw0KfQ0KQGtleWZyYW1lcyBtZW51RGlzcGxheXsNCglmcm9tew0KCQlvcGFjaXR5OiAwOw0KCQloZWlnaHQ6IDBweDsNCgl9DQp9");
var menuOpen = false;
var baseURL = '//pr2hub.com/';

function insertCSS()
{
    var menucss = document.createElement("style");
    menucss.innerHTML = menu_css_decoded;
    document.head.appendChild(menucss);
}

function menuCodeInsert()
{
    var hubMenu = document.createElement("div");
    hubMenu.setAttribute("class", "closed");
    hubMenu.setAttribute("id", "prmenu");
    document.body.appendChild(hubMenu);
}

function menuCloseAnim(menu)
{
    var menuHeight = 400;
    var heightCounter = parseInt(menu.style.height);
    while (heightCounter !== 0 && menuHeight >= 400) {
        menuHeight -= 1;
        heightCounter -= 1;
        menu.style.height = menuHeight + "px";
    }
    menuHeight = 400;
}

function banView()
{
    var banID = window.prompt("Enter the ID of the ban you'd like to view.");
    if (banID !== null && banID !== "" && isNaN(banID) === false) {
        location.href = "/bans/show_record.php?ban_id=" + banID;
    }
}

function setBackground()
{
    var imgURL = window.prompt("Enter a direct image link to use as a background.");
    if (imgURL !== null && imgURL !== "" && (imgURL.startsWith("http://") === true || imgURL.startsWith("https://") === true)) {
        document.body.style.cssText = "background-image: url(\"" + imgURL + "\"); background-attachment: fixed; background-size: cover;";
    } else if (imgURL === null) {
        return;
    } else {
        alert("That doesn't seem to be a valid link...");
    }
}

function banLogPage()
{
    var pageNum = window.prompt("Enter the page to which you'd like to go (100 bans per page).\nEnter 0 to go to the start of the ban log.");
    if (pageNum !== null && pageNum !== "" && isNaN(pageNum) === false) {
        var startingID = parseInt(pageNum) * 100;
        location.href = baseURL + "bans/bans.php?start=" + startingID + "&count=100";
    }
}

function toggleMenu(action)
{
    var hubMenu = document.getElementById("prmenu");
    if (menuOpen === true || action == 'close') {
        menuCloseAnim(hubMenu);
        hubMenu.classList.remove("open");
        hubMenu.classList.add("closed");
        menuOpen = false;
    } else if (menuOpen === false) {
        hubMenu.classList.remove("closed");
        hubMenu.classList.add("open");
        menuOpen = true;
    }
}

function menuInit()
{
    document.getElementById("prmenu").innerHTML = "<span class='menuTitle'><img src='/favicon.ico' width='20px' height='20px'></img> <span id='menu_goHome'>PR2 Hub</span></span>" +
                                                  "<hr class='menuDivider'></hr>" +
                                                  "<span class='menuLink' id='menu_setBackground'>Set Background</span>" +
                                                  "<span class='menuLink' id='menu_playerSearch'>Player Search</span>" +
                                                  "<span class='menuLink' id='menu_guildSearch'>Guild Search</span>" +
                                                  "<span class='menuLink' id='menu_leaderboard'>Leaderboard</span>" +
                                                  "<span class='menuLink' id='menu_artiHint'>Artifact Hint</span>" +
                                                  "<span class='menuLink' id='menu_transferGuild'>Transfer Guild</span>" +
                                                  "<span class='menuLink' id='menu_staffList'>PR2 Staff Team</span>" +
                                                  "<span class='menuLink' id='menu_banLog'>Ban Log <span onclick='event.stopPropagation(); window.event.cancelBubble = true;' class='smallLink' id='menu_banLogPage'>(or specify page)</span></span>" +
                                                  "<span class='menuLink' id='menu_banPriors'>Your Bans <span onclick='event.stopPropagation(); window.event.cancelBubble = true;' class='smallLink' id='menu_banView'>(or specify ban ID)</span></span>" +
                                                  "<span class='menuLink' id='menu_close'>Close</span><br>" +
                                                  "<span class='smallText'>You can open this menu from anywhere using the F8 key.</span>";
}

function menuButtonAdd()
{
    var createButton = document.createElement("div");
    createButton.setAttribute("id", "menuButton");
    createButton.innerHTML = "<span id='menuButtonText'>-- Menu --</span>";
    document.body.appendChild(createButton);
}

window.onload = function () {
    // insert and init
    insertCSS();
    menuButtonAdd();
    menuCodeInsert();
    menuInit();

    // menu button
    document.getElementById("menuButtonText").addEventListener("click", function () {
        toggleMenu();
    });

    // clicking anywhere but the menu with the menu open
    document.querySelector('#container').addEventListener('mousedown', function (containerevent) {
        if (menuOpen === true) {
            toggleMenu('close');
        }
    });

    // close button
    document.getElementById("menu_close").addEventListener("click", function (m_event) {
        toggleMenu('close');
    });
    
    // PR2 Hub button
    document.getElementById("menu_goHome").addEventListener("click", function () {
        toggleMenu('close');
        location.href = baseURL;
    });

    // view specific ban ID button
    document.getElementById("menu_banView").addEventListener("click", function () {
        toggleMenu('close');
        banView();
    });

    // set background button
    document.getElementById("menu_setBackground").addEventListener("click", function () {
        toggleMenu('close');
        setBackground();
    });

    document.getElementById("menu_banLogPage").addEventListener("click", function () {
        toggleMenu('close');
        banLogPage();
    });

    document.getElementById("menu_leaderboard").addEventListener("click", function () {
        toggleMenu('close');
        location.href = baseURL + 'leaderboard.php';
    });
    document.getElementById("menu_artiHint").addEventListener("click", function () {
        toggleMenu('close');
        location.href = baseURL + 'hint.php';
    });

    document.getElementById("menu_playerSearch").addEventListener("click", function () {
        toggleMenu('close');
        location.href = baseURL + 'player_search.php';
    });

    document.getElementById("menu_guildSearch").addEventListener("click", function () {
        toggleMenu('close');
        location.href = baseURL + 'guild_search.php';
    });

    document.getElementById("menu_staffList").addEventListener("click", function () {
        toggleMenu('close');
        location.href = baseURL + 'staff.php';
    });

    document.getElementById("menu_transferGuild").addEventListener("click", function () {
        toggleMenu('close');
        location.href = baseURL + 'guild_transfer.php';
    });

    document.getElementById("menu_banLog").addEventListener("click", function () {
        toggleMenu('close');
        location.href = baseURL + 'bans/';
    });
    
    document.getElementById("menu_banPriors").addEventListener("click", function () {
        toggleMenu('close');
        location.href = baseURL + 'bans/view_priors.php';
    });

    document.addEventListener("keydown", function (keyinfo) {
        if (keyinfo.keyCode === 119) { // F8
            toggleMenu();
        } else if (keyinfo.keyCode === 27) { // esc
            toggleMenu('close');
        }
    });
};
