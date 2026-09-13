<?php

require_once GEN_HTTP_FNS;
require_once HTTP_FNS . '/output_fns.php';

output_header('Platform Racing 2');

echo '<div class="game_holder">'
        .'<iframe id="pr2-game" width="550" height="400" '
        .'src="https://pr2hub.com/client/" '
        .'title="Platform Racing 2" allowfullscreen></iframe>'
        .'<div class="game_controls">'
            .'<button type="button" data-game-scale="1" aria-pressed="true">1x</button> '
            .'<button type="button" data-game-scale="2" aria-pressed="false">2x</button> '
            .'<button type="button" id="game-fullscreen">Fullscreen</button>'
        .'</div>'
    .'</div>'
    .'<script>
        const game = document.getElementById("pr2-game");
        const gameContent = game.closest(".content");
        const scaleButtons = document.querySelectorAll("[data-game-scale]");

        scaleButtons.forEach((button) => {
            button.addEventListener("click", () => {
                const scale = Number(button.dataset.gameScale);
                game.width = 550 * scale;
                game.height = 400 * scale;
                gameContent.style.width = scale === 2 ? "1100px" : "";

                scaleButtons.forEach((scaleButton) => {
                    scaleButton.setAttribute("aria-pressed", scaleButton === button ? "true" : "false");
                });
            });
        });

        document.getElementById("game-fullscreen").addEventListener("click", () => {
            game.requestFullscreen();
        });
    </script>';

output_footer();
