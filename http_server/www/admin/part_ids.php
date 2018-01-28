<?php

require_once('../../fns/all_fns.php');
require_once('../../fns/output_fns.php');

try {
	
	//connect
	$db = new DB();

	//make sure you're an admin
	$admin = check_moderator($db, true, 3);
	
	output_header('PR2 Part IDs', true, true);
	echo '<pre>Platform Racing 2 Part Codes

-- Hats --
1 - None
2 - EXP
3 - Kong
4 - Propeller
5 - Cowboy
6 - Crown
7 - Santa
8 - Party
9 - Top
10 - Jump Start
11 - Moon
12 - Thief
13 - Jigg
14 - Artifact
15+ - *BLANK*

-- Heads --
1 - Classic (Male)
2 - Tired
3 - Smiler (Wide-Eyed)
4 - Flower
5 - Classic (Female)
6 - Goof (Happy/Surprised)
7 - Downer (Emo)
8 - Balloon
9 - Worm
10 - Unicorn
11 - Bird
12 - Sun
13 - Candy
14 - Invisible
15 - Football Helmet
16 - Basketball
17 - Stick
18 - Cat
19 - Elephant
20 - Ant
21 - Astronaut
22 - Alien
23 - Dino
24 - Armor
25 - Fairy
26 - Gingerbread
27 - Bubble
28 - Wise King
29 - Wise Queen
30 - Sir
31 - Very Invisible
32 - Taco
33 - Slender
34 - Santa
35 - Frost Djinn
36 - Reindeer
37 - Crocodile
38 - Valentine
39 - Rabbit
40+ - *BLANK*

-- Bodies --
1 - Classic
2 - Strap
3 - Dress
4 - Pec (Buff/Body Builder)
5 - Gut (Rounded Belly)
6 - Collar (Popped Collar)
7 - Miss PR2 (Pageant Ribbon)
8 - Belt
9 - Snake
10 - Bird
11 - Invisible
12 - Bee
13 - Stick
14 - Cat
15 - Car
16 - Elephant
17 - Ant
18 - Astronaut
19 - Alien
20 - Galaxy
21 - Bubble
22 - Dino
23 - Armor
24 - Fairy
25 - Gingerbread
26 - Wise King
27 - Wise Queen
28 - Sir
29 - Fred
30 - Very Invisible
31 - Taco
32 - Slender
33 - *BLANK*
34 - Santa
35 - Frost Djinn
36 - Reindeer
37 - Crocodile
38 - Valentine
39 - Rabbit
40+ - *BLANK*

-- Feet --
1 - Classic
2 - Heel (High-Heel Shoes)
3 - Loafer (Sneakers)
4 - Soccer
5 - Magnet
6 - Tiny
7 - Sandal
8 - Bare (Arched Feet)
9 - Nice (Women\'s Dress Shoes)
10 - Bird
11 - Invisible
12 - Stick
13 - Cat
14 - Car
15 - Elephant
16 - Ant
17 - Astronaut
18 - Alien
19 - Galaxy
20 - Dino
21 - Armor
22 - Fairy
23 - Gingerbread
24 - Wise King
25 - Wise Queen
26 - Sir
27 - Very Invisible (+ sir outline)
28 - Bubble
29 - Taco
30 - Slender
31 - *BLANK*
32 - *BLANK*
33 - *BLANK*
34 - Santa
35 - Frost Djinn
36 - Reindeer
37 - Crocodile
38 - Valentine
39 - Rabbit
40+ - *BLANK*

-- Full Sets --
(Key: Head ID, Body ID, Feet ID)
Classic: 1, 1, 1
Bird: 11, 10, 10
Invisible: 14, 11, 11
Stick: 17, 13, 12
Cat: 18, 14, 13
Elephant: 19, 16, 15
Ant: 20, 17, 16
Astronaut: 21, 18, 17
Alien: 22, 19, 18
Dino: 23, 22, 20
Armor: 24, 23, 21
Fairy: 25, 24, 22
Gingerbread: 26, 25, 23
Bubble: 27, 21, 28
Wise King: 28, 26, 24
Wise Queen: 29, 27, 25
Sir: 30, 28, 26
Fred: Any #, 29, Any #
Very Invisible: 31, 30, 27
Taco: 32, 31, 29
Slender: 33, 32, 30
Santa: 34, 34, 34
Frost Djinn: 35, 35, 35
Reindeer: 36, 36, 36
Crocodile: 37, 37, 37
Valentine: 38, 38, 38
Bunny: 39, 39, 39

-- Blank IDs --
Hats: 15+
Heads: 40+
Bodies: 33, 40+
Feet: 31-33, 40+</pre>';

	output_footer();

}

catch (Exception $e) {
	output_header('Error');
	echo 'Error: ' . $e->getMessage();
	output_footer();
}

?>
