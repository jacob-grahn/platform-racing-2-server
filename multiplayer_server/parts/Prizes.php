<?php

class Prizes {

	const TYPE_HAT = 'hat';
	const TYPE_HEAD = 'head';
	const TYPE_BODY = 'body';
	const TYPE_FEET = 'feet';
	const TYPE_EPIC_HAT = 'eHat';
	const TYPE_EPIC_HEAD = 'eHead';
	const TYPE_EPIC_BODY = 'eBody';
	const TYPE_EPIC_FEET = 'eFeet';
	const TYPE_EXP = 'exp';

	private static $arr;


	//---
	public static $NO_HAT;
	public static $KONG_HAT;
	public static $EXP_HAT;
	public static $PROPELLER_HAT;
	public static $COWBOY_HAT;
	public static $CROWN_HAT;
	public static $SANTA_HAT;
	public static $PARTY_HAT;
	public static $TOP_HAT;
	public static $JUMP_START_HAT;
	public static $MOON_HAT;
	public static $THIEF_HAT;
	public static $JIGG_HAT;
	public static $ARTIFACT_HAT;

	public static $EPIC_KONG_HAT;
	public static $EPIC_EXP_HAT;
	public static $EPIC_PROPELLER_HAT;
	public static $EPIC_COWBOY_HAT;
	public static $EPIC_CROWN_HAT;
	public static $EPIC_SANTA_HAT;
	public static $EPIC_PARTY_HAT;
	public static $EPIC_TOP_HAT;
	public static $EPIC_JUMP_START_HAT;
	public static $EPIC_MOON_HAT;
	public static $EPIC_THIEF_HAT;
	public static $EPIC_JIGG_HAT;
	public static $EPIC_ARTIFACT_HAT;


	//---
	public static $CLASSIC_HEAD;
	public static $TIRED_HEAD;
	public static $SMILER_HEAD;
	public static $FLOWER_HEAD;
	public static $CLASSIC_GIRL_HEAD;
	public static $GOOF_HEAD;
	public static $DOWNER_HEAD;
	public static $BALLOON_HEAD;
	public static $WORM_HEAD;
	public static $UNICORN_HEAD;
	public static $BIRD_HEAD;
	public static $SUN_HEAD;
	public static $CANDY_HEAD;
	public static $INVISIBLE_HEAD;
	public static $FOOTBALL_HELMET_HEAD;
	public static $BASKETBALL_HEAD;
	public static $STICK_HEAD;
	public static $CAT_HEAD;
	public static $ELEPHANT_HEAD;
	public static $ANT_HEAD;
	public static $ASTRONAUT_HEAD;
	public static $ALIEN_HEAD;
	public static $DINO_HEAD;
	public static $ARMOR_HEAD;
	public static $FAIRY_HEAD;
	public static $GINGERBREAD_HEAD;
	public static $BUBBLE_HEAD;
	public static $KING_HEAD;
	public static $QUEEN_HEAD;
	public static $SIR_HEAD;
	public static $VERY_INVISIBLE_HEAD;
	public static $TACO_HEAD;
	public static $SLENDER_HEAD;

	public static $EPIC_CLASSIC_HEAD;
	public static $EPIC_TIRED_HEAD;
	public static $EPIC_SMILER_HEAD;
	public static $EPIC_FLOWER_HEAD;
	public static $EPIC_CLASSIC_GIRL_HEAD;
	public static $EPIC_GOOF_HEAD;
	public static $EPIC_DOWNER_HEAD;
	public static $EPIC_BALLOON_HEAD;
	public static $EPIC_WORM_HEAD;
	public static $EPIC_UNICORN_HEAD;
	public static $EPIC_BIRD_HEAD;
	public static $EPIC_SUN_HEAD;
	public static $EPIC_CANDY_HEAD;
	public static $EPIC_INVISIBLE_HEAD;
	public static $EPIC_FOOTBALL_HELMET_HEAD;
	public static $EPIC_BASKETBALL_HEAD;
	public static $EPIC_STICK_HEAD;
	public static $EPIC_CAT_HEAD;
	public static $EPIC_ELEPHANT_HEAD;
	public static $EPIC_ANT_HEAD;
	public static $EPIC_ASTRONAUT_HEAD;
	public static $EPIC_ALIEN_HEAD;
	public static $EPIC_DINO_HEAD;
	public static $EPIC_ARMOR_HEAD;
	public static $EPIC_FAIRY_HEAD;
	public static $EPIC_GINGERBREAD_HEAD;
	public static $EPIC_BUBBLE_HEAD;
	public static $EPIC_KING_HEAD;
	public static $EPIC_QUEEN_HEAD;
	public static $EPIC_SIR_HEAD;
	public static $EPIC_VERY_INVISIBLE_HEAD;
	public static $EPIC_TACO_HEAD;
	public static $EPIC_SLENDER_HEAD;



	//---
	public static $CLASSIC_BODY;
	public static $STRAP_BODY;
	public static $DRESS_BODY;
	public static $PEC_BODY;
	public static $GUT_BODY;
	public static $COLLAR_BODY;
	public static $MISS_PR2_BODY;
	public static $BELT_BODY;
	public static $SNAKE_BODY;
	public static $BIRD_BODY;
	public static $INVISIBLE_BODY;
	public static $BEE_BODY;
	public static $STICK_BODY;
	public static $CAT_BODY;
	public static $CAR_BODY;
	public static $BEAN_BODY;
	public static $ANT_BODY;
	public static $ASTRONAUT_BODY;
	public static $ALIEN_BODY;
	public static $GALAXY_BODY;
	public static $BUBBLE_BODY;
	public static $DINO_BODY;
	public static $ARMOR_BODY;
	public static $FAIRY_BODY;
	public static $GINGERBREAD_BODY;
	public static $KING_BODY;
	public static $QUEEN_BODY;
	public static $SIR_BODY;
	public static $FRED_BODY;
	public static $VERY_INVISIBLE_BODY;
	public static $TACO_BODY;
	public static $SLENDER_BODY;

	public static $EPIC_CLASSIC_BODY;
	public static $EPIC_STRAP_BODY;
	public static $EPIC_DRESS_BODY;
	public static $EPIC_PEC_BODY;
	public static $EPIC_GUT_BODY;
	public static $EPIC_COLLAR_BODY;
	public static $EPIC_MISS_PR2_BODY;
	public static $EPIC_BELT_BODY;
	public static $EPIC_SNAKE_BODY;
	public static $EPIC_BIRD_BODY;
	public static $EPIC_INVISIBLE_BODY;
	public static $EPIC_BEE_BODY;
	public static $EPIC_STICK_BODY;
	public static $EPIC_CAT_BODY;
	public static $EPIC_CAR_BODY;
	public static $EPIC_BEAN_BODY;
	public static $EPIC_ANT_BODY;
	public static $EPIC_ASTRONAUT_BODY;
	public static $EPIC_ALIEN_BODY;
	public static $EPIC_GALAXY_BODY;
	public static $EPIC_BUBBLE_BODY;
	public static $EPIC_DINO_BODY;
	public static $EPIC_ARMOR_BODY;
	public static $EPIC_FAIRY_BODY;
	public static $EPIC_GINGERBREAD_BODY;
	public static $EPIC_KING_BODY;
	public static $EPIC_QUEEN_BODY;
	public static $EPIC_SIR_BODY;
	public static $EPIC_FRED_BODY;
	public static $EPIC_VERY_INVISIBLE_BODY;
	public static $EPIC_TACO_BODY;
	public static $EPIC_SLENDER_BODY;



	//---
	public static $CLASSIC_FEET;
	public static $HEEL_FEET;
	public static $LOAFER_FEET;
	public static $SOCCER_FEET;
	public static $MAGNET_FEET;
	public static $TINY_FEET;
	public static $SANDAL_FEET;
	public static $BARE_FEET;
	public static $NICE_FEET;
	public static $BIRD_FEET;
	public static $INVISIBLE_FEET;
	public static $STICK_FEET;
	public static $CAT_FEET;
	public static $TIRE_FEET;
	public static $ELEPHANT_FEET;
	public static $ANT_FEET;
	public static $ASTRONAUT_FEET;
	public static $ALIEN_FEET;
	public static $GALAXY_FEET;
	public static $DINO_FEET;
	public static $ARMOR_FEET;
	public static $FAIRY_FEET;
	public static $GINGERBREAD_FEET;
	public static $KING_FEET;
	public static $QUEEN_FEET;
	public static $SIR_FEET;
	public static $VERY_INVISIBLE_FEET;
	public static $BUBBLE_FEET;
	public static $TACO_FEET;
	public static $SLENDER_FEET;

	public static $EPIC_CLASSIC_FEET;
	public static $EPIC_HEEL_FEET;
	public static $EPIC_LOAFER_FEET;
	public static $EPIC_SOCCER_FEET;
	public static $EPIC_MAGNET_FEET;
	public static $EPIC_TINY_FEET;
	public static $EPIC_SANDAL_FEET;
	public static $EPIC_BARE_FEET;
	public static $EPIC_NICE_FEET;
	public static $EPIC_BIRD_FEET;
	public static $EPIC_INVISIBLE_FEET;
	public static $EPIC_STICK_FEET;
	public static $EPIC_CAT_FEET;
	public static $EPIC_TIRE_FEET;
	public static $EPIC_ELEPHANT_FEET;
	public static $EPIC_ANT_FEET;
	public static $EPIC_ASTRONAUT_FEET;
	public static $EPIC_ALIEN_FEET;
	public static $EPIC_GALAXY_FEET;
	public static $EPIC_DINO_FEET;
	public static $EPIC_ARMOR_FEET;
	public static $EPIC_FAIRY_FEET;
	public static $EPIC_GINGERBREAD_FEET;
	public static $EPIC_KING_FEET;
	public static $EPIC_QUEEN_FEET;
	public static $EPIC_SIR_FEET;
	public static $EPIC_VERY_INVISIBLE_FEET;
	public static $EPIC_BUBBLE_FEET;
	public static $EPIC_TACO_FEET;
	public static $EPIC_SLENDER_FEET;


	//---
	public static $EXP_5;


	public static function init() {

		self::$arr = array();


		//---
		self::$NO_HAT = new Prize( self::TYPE_HAT, Hats::NONE, 'No Hat' );
		self::$KONG_HAT = new Prize( self::TYPE_HAT, Hats::KONG, 'Kongregate Hat' );
		self::$EXP_HAT = new Prize( self::TYPE_HAT, Hats::EXP, 'Exp Hat', 'If you finish a race with this hat, it will increase your exp gain by 100%!' );
		self::$PROPELLER_HAT = new Prize( self::TYPE_HAT, Hats::PROPELLER, 'Propeller Hat', 'Hold up while wearing this hat to float!', true );
		self::$COWBOY_HAT = new Prize( self::TYPE_HAT, Hats::COWBOY, 'Cowboy Hat' );
		self::$CROWN_HAT = new Prize( self::TYPE_HAT, Hats::CROWN, 'Crown' );
		self::$SANTA_HAT = new Prize( self::TYPE_HAT, Hats::SANTA, 'Santa Cap', 'Briefly freezes the blocks you stand on!' );
		self::$PARTY_HAT = new Prize( self::TYPE_HAT, Hats::PARTY, 'Party Hat', 'Wear this hat to become immune to lightning!' );
		self::$TOP_HAT = new Prize( self::TYPE_HAT, Hats::TOP_HAT, 'Top Hat', 'Stroll through vanish blocks with class!', true );
		self::$JUMP_START_HAT = new Prize( self::TYPE_HAT, Hats::JUMP_START, 'Jump Start Hat', 'Waiting is slow! Start racing right away.' );
		self::$MOON_HAT = new Prize( self::TYPE_HAT, Hats::MOON, 'Moon Hat', 'Earn some super bright Lux!', true );
		self::$THIEF_HAT = new Prize( self::TYPE_HAT, Hats::THIEF, 'Thief Hat', 'Steal other player\'s hats --even crowns!', true );
		self::$JIGG_HAT = new Prize( self::TYPE_HAT, Hats::JIGG, 'Jigg Hat', 'Jump on the heads of your opponents!', true ); //buto (EXACT) by ZePHiR
		self::$ARTIFACT_HAT = new Prize( self::TYPE_HAT, Hats::ARTIFACT, 'Artifact Hat' );

		self::$EPIC_KONG_HAT = new Prize( self::TYPE_EPIC_HAT, Hats::KONG, 'Epic Upgrade' );
		self::$EPIC_EXP_HAT = new Prize( self::TYPE_EPIC_HAT, Hats::EXP, 'Epic Upgrade', '', true ); // campaign 5 level 9
		self::$EPIC_PROPELLER_HAT = new Prize( self::TYPE_EPIC_HAT, Hats::PROPELLER, 'Epic Upgrade' ); // jiggmin.com/threads/104505-The-Ultimate-Jiggmin-Gaming-Tournament
		self::$EPIC_COWBOY_HAT = new Prize( self::TYPE_EPIC_HAT, Hats::COWBOY, 'Epic Upgrade' );
		self::$EPIC_CROWN_HAT = new Prize( self::TYPE_EPIC_HAT, Hats::CROWN, 'Epic Upgrade' ); // cheetah racing leage
		self::$EPIC_SANTA_HAT = new Prize( self::TYPE_EPIC_HAT, Hats::SANTA, 'Epic Upgrade' ); // PR2 Quests jiggmin.com/threads/109956-PR2-Quests
		self::$EPIC_PARTY_HAT = new Prize( self::TYPE_EPIC_HAT, Hats::PARTY, 'Epic Upgrade' );
		self::$EPIC_TOP_HAT = new Prize( self::TYPE_EPIC_HAT, Hats::TOP_HAT, 'Epic Upgrade' ); // Sir Sirlington
		self::$EPIC_JUMP_START_HAT = new Prize( self::TYPE_EPIC_HAT, Hats::JUMP_START, 'Epic Upgrade' ); // campaign 6
		self::$EPIC_MOON_HAT = new Prize( self::TYPE_EPIC_HAT, Hats::MOON, 'Epic Upgrade' );
		self::$EPIC_THIEF_HAT = new Prize( self::TYPE_EPIC_HAT, Hats::THIEF, 'Epic Upgrade' );
		self::$EPIC_JIGG_HAT = new Prize( self::TYPE_EPIC_HAT, Hats::JIGG, 'Epic Upgrade' ); // caption of the week
		self::$EPIC_ARTIFACT_HAT = new Prize( self::TYPE_EPIC_HAT, Hats::ARTIFACT, 'Epic Upgrade' );


		//---
		self::$CLASSIC_HEAD = new Prize( self::TYPE_HEAD, Heads::CLASSIC, 'Classic Head' );
		self::$TIRED_HEAD = new Prize( self::TYPE_HEAD, Heads::TIRED, 'Tired Head' );
		self::$SMILER_HEAD = new Prize( self::TYPE_HEAD, Heads::SMILER, 'Smiling Head' );
		self::$FLOWER_HEAD = new Prize( self::TYPE_HEAD, Heads::FLOWER, 'Flower Head' );
		self::$CLASSIC_GIRL_HEAD = new Prize( self::TYPE_HEAD, Heads::CLASSIC_GIRL, 'Lady Head' );
		self::$GOOF_HEAD = new Prize( self::TYPE_HEAD, Heads::GOOF, 'Goof Head' );
		self::$DOWNER_HEAD = new Prize( self::TYPE_HEAD, Heads::DOWNER, 'Downer Head' );
		self::$BALLOON_HEAD = new Prize( self::TYPE_HEAD, Heads::BALLOON, 'Balloon Head' );
		self::$WORM_HEAD = new Prize( self::TYPE_HEAD, Heads::WORM, 'Worm Head' );
		self::$UNICORN_HEAD = new Prize( self::TYPE_HEAD, Heads::UNICORN, 'Unicorn Head' );
		self::$BIRD_HEAD = new Prize( self::TYPE_HEAD, Heads::BIRD, 'Bird Head' );
		self::$SUN_HEAD = new Prize( self::TYPE_HEAD, Heads::SUN, 'Sun Head');
		self::$CANDY_HEAD = new Prize( self::TYPE_HEAD, Heads::CANDY, 'Candy Head' );
		self::$INVISIBLE_HEAD = new Prize( self::TYPE_HEAD, Heads::INVISIBLE, 'Invisible Head' );
		self::$FOOTBALL_HELMET_HEAD = new Prize( self::TYPE_HEAD, Heads::FOOTBALL_HELMET, 'Helmet Head' );
		self::$BASKETBALL_HEAD = new Prize( self::TYPE_HEAD, Heads::BASKETBALL, 'Basketball Head' );
		self::$STICK_HEAD = new Prize( self::TYPE_HEAD, Heads::STICK, 'Stick Head' );
		self::$CAT_HEAD = new Prize( self::TYPE_HEAD, Heads::CAT, 'Cat Head' );
		self::$ELEPHANT_HEAD = new Prize( self::TYPE_HEAD, Heads::ELEPHANT, 'Elephant Head' );
		self::$ANT_HEAD= new Prize( self::TYPE_HEAD, Heads::ANT, 'Ant Head' );
		self::$ASTRONAUT_HEAD = new Prize( self::TYPE_HEAD, Heads::ASTRONAUT, 'Astronaut Head' );
		self::$ALIEN_HEAD = new Prize( self::TYPE_HEAD, Heads::ALIEN, 'Alien Head' );
		self::$DINO_HEAD = new Prize( self::TYPE_HEAD, Heads::DINO, 'Dino Head' );
		self::$ARMOR_HEAD = new Prize( self::TYPE_HEAD, Heads::ARMOR, 'Armor Head' );
		self::$FAIRY_HEAD = new Prize( self::TYPE_HEAD, Heads::FAIRY, 'Fairy Head' );
		self::$GINGERBREAD_HEAD = new Prize( self::TYPE_HEAD, Heads::GINGERBREAD, 'Gingerbread Head' );
		self::$BUBBLE_HEAD = new Prize( self::TYPE_HEAD, Heads::BUBBLE, 'Bubble Head' );
		self::$KING_HEAD = new Prize( self::TYPE_HEAD, Heads::KING, 'King Head' );
		self::$QUEEN_HEAD = new Prize( self::TYPE_HEAD, Heads::QUEEN, 'Queen Head' );
		self::$SIR_HEAD = new Prize( self::TYPE_HEAD, Heads::SIR, 'Sir Head' );
		self::$VERY_INVISIBLE_HEAD = new Prize( self::TYPE_HEAD, Heads::VERY_INVISIBLE, 'Very Invisible Head' );
		self::$TACO_HEAD = new Prize( self::TYPE_HEAD, Heads::TACO, 'Taco Head' ); // random levels
		self::$SLENDER_HEAD = new Prize( self::TYPE_HEAD, Heads::SLENDER, 'Slender Head' ); // -Deliverance- by changelings

		self::$EPIC_CLASSIC_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::CLASSIC, 'Epic Upgrade' ); // random levels
		self::$EPIC_TIRED_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::TIRED, 'Epic Upgrade' ); // random levels
		self::$EPIC_SMILER_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::SMILER, 'Epic Upgrade' );
		self::$EPIC_FLOWER_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::FLOWER, 'Epic Upgrade' ); // random levels
		self::$EPIC_CLASSIC_GIRL_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::CLASSIC_GIRL, 'Epic Upgrade' );
		self::$EPIC_GOOF_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::GOOF, 'Epic Upgrade' );
		self::$EPIC_DOWNER_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::DOWNER, 'Epic Upgrade' );
		self::$EPIC_BALLOON_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::BALLOON, 'Epic Upgrade' );
		self::$EPIC_WORM_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::WORM, 'Epic Upgrade' ); // jiggmin.com/threads/111373-Campaign-of-the-Fortnight-Theme-Suggestions!
		self::$EPIC_UNICORN_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::UNICORN, 'Epic Upgrade' ); //campaign 6
		self::$EPIC_BIRD_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::BIRD, 'Epic Upgrade' ); //campaign 5 level 1
		self::$EPIC_SUN_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::SUN, 'Epic Upgrade'); // jiggmin.com/threads/108828-Survivor-Jiggmin-s-Village
		self::$EPIC_CANDY_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::CANDY, 'Epic Upgrade' ); // campaign 6
		self::$EPIC_INVISIBLE_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::INVISIBLE, 'Epic Upgrade' ); // NA
		self::$EPIC_FOOTBALL_HELMET_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::FOOTBALL_HELMET, 'Epic Upgrade' ); // campaign 6
		self::$EPIC_BASKETBALL_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::BASKETBALL, 'Epic Upgrade' ); // Jiggmin Grand Prix jiggmin.com/threads/104222-Jiggmin-Grand-Prix
		self::$EPIC_STICK_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::STICK, 'Epic Upgrade' ); //Styler of The Week //Curve Fever Tournament
		self::$EPIC_CAT_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::CAT, 'Epic Upgrade' ); //cheetah racing leage
		self::$EPIC_ELEPHANT_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::ELEPHANT, 'Epic Upgrade' );
		self::$EPIC_ANT_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::ANT, 'Epic Upgrade' );
		self::$EPIC_ASTRONAUT_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::ASTRONAUT, 'Epic Upgrade' );
		self::$EPIC_ALIEN_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::ALIEN, 'Epic Upgrade' ); //free rice of the week
		self::$EPIC_DINO_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::DINO, 'Epic Upgrade' ); //campaign 5 level 4
		self::$EPIC_ARMOR_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::ARMOR, 'Epic Upgrade' );
		self::$EPIC_FAIRY_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::FAIRY, 'Epic Upgrade' ); //Draw the JVer!
		self::$EPIC_GINGERBREAD_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::GINGERBREAD, 'Epic Upgrade' );
		self::$EPIC_BUBBLE_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::BUBBLE, 'Epic Upgrade' );
		self::$EPIC_KING_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::KING, 'Epic Upgrade' ); //built in
		self::$EPIC_QUEEN_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::QUEEN, 'Epic Upgrade' ); //build in
		self::$EPIC_SIR_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::SIR, 'Epic Upgrade' ); //Sir Sirlington
		self::$EPIC_VERY_INVISIBLE_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::VERY_INVISIBLE, 'Epic Upgrade' );
		self::$EPIC_TACO_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::TACO, 'Epic Upgrade' ); //Racer of the week
		self::$EPIC_SLENDER_HEAD = new Prize( self::TYPE_EPIC_HEAD, Heads::SLENDER, 'Epic Upgrade' ); //campaign 6


		//---
		self::$CLASSIC_BODY = new Prize( self::TYPE_BODY, Bodies::CLASSIC, 'Classic Body' );
		self::$STRAP_BODY = new Prize( self::TYPE_BODY, Bodies::STRAP, 'Strap Body' );
		self::$DRESS_BODY = new Prize( self::TYPE_BODY, Bodies::DRESS, 'Dress Body' );
		self::$PEC_BODY = new Prize( self::TYPE_BODY, Bodies::PEC, 'Pec Body' );
		self::$GUT_BODY = new Prize( self::TYPE_BODY, Bodies::GUT, 'Gut Body' );
		self::$COLLAR_BODY = new Prize( self::TYPE_BODY, Bodies::COLLAR, 'Collar Body' );
		self::$MISS_PR2_BODY = new Prize( self::TYPE_BODY, Bodies::MISS_PR2, 'Miss PR2 Body' );
		self::$BELT_BODY = new Prize( self::TYPE_BODY, Bodies::BELT, 'Belt Body' );
		self::$SNAKE_BODY = new Prize( self::TYPE_BODY, Bodies::SNAKE, 'Snake Body' );
		self::$BIRD_BODY = new Prize( self::TYPE_BODY, Bodies::BIRD, 'Bird Body' );
		self::$INVISIBLE_BODY = new Prize( self::TYPE_BODY, Bodies::INVISIBLE, 'Invisible Body' );
		self::$BEE_BODY = new Prize( self::TYPE_BODY, Bodies::BEE, 'Bee Body' );
		self::$STICK_BODY = new Prize( self::TYPE_BODY, Bodies::STICK, 'Stick Body' );
		self::$CAT_BODY = new Prize( self::TYPE_BODY, Bodies::CAT, 'Cat Body' );
		self::$CAR_BODY = new Prize( self::TYPE_BODY, Bodies::CAR, 'Car Body' );
		self::$BEAN_BODY = new Prize( self::TYPE_BODY, Bodies::BEAN, 'Elephant Body' );
		self::$ANT_BODY = new Prize( self::TYPE_BODY, Bodies::ANT, 'Ant Body' );
		self::$ASTRONAUT_BODY = new Prize( self::TYPE_BODY, Bodies::ASTRONAUT, 'Astronaut Body' );
		self::$ALIEN_BODY = new Prize( self::TYPE_BODY, Bodies::ALIEN, 'Alien Body' );
		self::$GALAXY_BODY = new Prize( self::TYPE_BODY, Bodies::GALAXY, 'Galaxy Body' );
		self::$BUBBLE_BODY = new Prize( self::TYPE_BODY, Bodies::BUBBLE, 'Bubble Body' );
		self::$DINO_BODY = new Prize( self::TYPE_BODY, Bodies::DINO, 'Dino Body' );
		self::$ARMOR_BODY = new Prize( self::TYPE_BODY, Bodies::ARMOR, 'Armor Body' );
		self::$FAIRY_BODY = new Prize( self::TYPE_BODY, Bodies::FAIRY, 'Fairy Body' );
		self::$GINGERBREAD_BODY = new Prize( self::TYPE_BODY, Bodies::GINGERBREAD, 'Gingerbread Body' );
		self::$KING_BODY = new Prize( self::TYPE_BODY, Bodies::KING, 'King Body' );
		self::$QUEEN_BODY = new Prize( self::TYPE_BODY, Bodies::QUEEN, 'Queen Body' );
		self::$SIR_BODY = new Prize( self::TYPE_BODY, Bodies::SIR, 'Sir Body' );
		self::$FRED_BODY = new Prize( self::TYPE_BODY, Bodies::FRED, 'Fred Body' );
		self::$VERY_INVISIBLE_BODY = new Prize( self::TYPE_BODY, Bodies::VERY_INVISIBLE, 'Very Invisible Body' );
		self::$TACO_BODY = new Prize( self::TYPE_BODY, Bodies::TACO, 'Taco Body' ); //random levels
		self::$SLENDER_BODY = new Prize( self::TYPE_BODY, Bodies::SLENDER, 'Slender Body' ); //-Deliverance- by changelings

		self::$EPIC_CLASSIC_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::CLASSIC, 'Epic Upgrade' ); //random levels
		self::$EPIC_STRAP_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::STRAP, 'Epic Upgrade' ); //random levels
		self::$EPIC_DRESS_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::DRESS, 'Epic Upgrade' ); //random levels
		self::$EPIC_PEC_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::PEC, 'Epic Upgrade' );
		self::$EPIC_GUT_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::GUT, 'Epic Upgrade' );
		self::$EPIC_COLLAR_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::COLLAR, 'Epic Upgrade' );
		self::$EPIC_MISS_PR2_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::MISS_PR2, 'Epic Upgrade' );
		self::$EPIC_BELT_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::BELT, 'Epic Upgrade' );
		self::$EPIC_SNAKE_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::SNAKE, 'Epic Upgrade' );
		self::$EPIC_BIRD_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::BIRD, 'Epic Upgrade' ); // campaign 5 level 2
		self::$EPIC_INVISIBLE_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::INVISIBLE, 'Epic Upgrade' );
		self::$EPIC_BEE_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::BEE, 'Epic Upgrade' );
		self::$EPIC_STICK_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::STICK, 'Epic Upgrade' ); // Styler of The Week //Curve Fever Tournament
		self::$EPIC_CAT_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::CAT, 'Epic Upgrade' ); // cheetah racing leage
		self::$EPIC_CAR_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::CAR, 'Epic Upgrade' ); // Jiggmin Grand Prix top 8 jiggmin.com/threads/104222-Jiggmin-Grand-Prix
		self::$EPIC_BEAN_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::BEAN, 'Epic Upgrade' ); // campaign 6
		self::$EPIC_ANT_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::ANT, 'Epic Upgrade' );
		self::$EPIC_ASTRONAUT_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::ASTRONAUT, 'Epic Upgrade' );
		self::$EPIC_ALIEN_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::ALIEN, 'Epic Upgrade' ); //free rice of the week
		self::$EPIC_GALAXY_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::GALAXY, 'Epic Upgrade' ); //campaign 5 level 7
		self::$EPIC_BUBBLE_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::BUBBLE, 'Epic Upgrade' );
		self::$EPIC_DINO_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::DINO, 'Epic Upgrade' ); //campaign 5 level 5
		self::$EPIC_ARMOR_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::ARMOR, 'Epic Upgrade' );
		self::$EPIC_FAIRY_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::FAIRY, 'Epic Upgrade' ); //Draw the JVer!
		self::$EPIC_GINGERBREAD_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::GINGERBREAD, 'Epic Upgrade' );
		self::$EPIC_KING_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::KING, 'Epic Upgrade' ); //built in
		self::$EPIC_QUEEN_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::QUEEN, 'Epic Upgrade' ); //built in
		self::$EPIC_SIR_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::SIR, 'Epic Upgrade' ); //Sir Sirlington
		self::$EPIC_FRED_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::FRED, 'Epic Upgrade' ); //built in
		self::$EPIC_VERY_INVISIBLE_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::VERY_INVISIBLE, 'Epic Upgrade' );
		self::$EPIC_TACO_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::TACO, 'Epic Upgrade' ); //racer of the week
		self::$EPIC_SLENDER_BODY = new Prize( self::TYPE_EPIC_BODY, Bodies::SLENDER, 'Epic Upgrade' ); //campaign 6


		//---
		self::$CLASSIC_FEET = new Prize( self::TYPE_FEET, Feet::CLASSIC, 'Classic Feet' );
		self::$HEEL_FEET = new Prize( self::TYPE_FEET, Feet::HEEL, 'Heel Feet' );
		self::$LOAFER_FEET = new Prize( self::TYPE_FEET, Feet::LOAFER, 'Loafer Feet' );
		self::$SOCCER_FEET = new Prize( self::TYPE_FEET, Feet::SOCCER, 'Soccer Feet' );
		self::$MAGNET_FEET = new Prize( self::TYPE_FEET, Feet::MAGNET, 'Magnet Feet' );
		self::$TINY_FEET = new Prize( self::TYPE_FEET, Feet::TINY, 'Tiny Feet' );
		self::$SANDAL_FEET = new Prize( self::TYPE_FEET, Feet::SANDAL, 'Sandal Feet' );
		self::$BARE_FEET = new Prize( self::TYPE_FEET, Feet::BARE, 'Bare Feet' );
		self::$NICE_FEET = new Prize( self::TYPE_FEET, Feet::NICE, 'Nice Feet' );
		self::$BIRD_FEET = new Prize( self::TYPE_FEET, Feet::BIRD, 'Bird Feet' );
		self::$INVISIBLE_FEET = new Prize( self::TYPE_FEET, Feet::INVISIBLE, 'Invisible Feet' );
		self::$STICK_FEET = new Prize( self::TYPE_FEET, Feet::STICK, 'Stick Feet' );
		self::$CAT_FEET = new Prize( self::TYPE_FEET, Feet::CAT, 'Cat Feet' );
		self::$TIRE_FEET = new Prize( self::TYPE_FEET, Feet::TIRE, 'Tire Feet' );
		self::$ELEPHANT_FEET = new Prize( self::TYPE_FEET, Feet::ELEPHANT, 'Elephant Feet' );
		self::$ANT_FEET = new Prize( self::TYPE_FEET, Feet::ANT, 'Ant Feet' );
		self::$ASTRONAUT_FEET = new Prize( self::TYPE_FEET, Feet::ASTRONAUT, 'Astronaut Feet' );
		self::$ALIEN_FEET = new Prize( self::TYPE_FEET, Feet::ALIEN, 'Alien Feet' );
		self::$GALAXY_FEET = new Prize( self::TYPE_FEET, Feet::GALAXY, 'Galaxy Feet' );
		self::$DINO_FEET = new Prize( self::TYPE_FEET, Feet::DINO, 'Dino Feet' );
		self::$ARMOR_FEET = new Prize( self::TYPE_FEET, Feet::ARMOR, 'Armor Feet' );
		self::$FAIRY_FEET = new Prize( self::TYPE_FEET, Feet::FAIRY, 'Fairy Feet' );
		self::$GINGERBREAD_FEET = new Prize( self::TYPE_FEET, Feet::GINGERBREAD, 'Gingerbread Feet' );
		self::$KING_FEET = new Prize( self::TYPE_FEET, Feet::KING, 'King Feet' );
		self::$QUEEN_FEET = new Prize( self::TYPE_FEET, Feet::QUEEN, 'Queen Feet' );
		self::$SIR_FEET = new Prize( self::TYPE_FEET, Feet::SIR, 'Sir Feet' );
		self::$VERY_INVISIBLE_FEET = new Prize( self::TYPE_FEET, Feet::VERY_INVISIBLE, 'Very Invisible Feet' );
		self::$BUBBLE_FEET = new Prize( self::TYPE_FEET, Feet::BUBBLE, 'Bubble Feet' );
		self::$TACO_FEET = new Prize( self::TYPE_FEET, Feet::TACO, 'Taco Feet' ); //random levels
		self::$SLENDER_FEET = new Prize( self::TYPE_FEET, Feet::SLENDER, 'Slender Feet' ); //-Deliverance- by changelings

		self::$EPIC_CLASSIC_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::CLASSIC, 'Epic Upgrade' ); //random levels
		self::$EPIC_HEEL_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::HEEL, 'Epic Upgrade' ); //random levels
		self::$EPIC_LOAFER_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::LOAFER, 'Epic Upgrade' ); // jiggmin.com/threads/111373-Campaign-of-the-Fortnight-Theme-Suggestions!
		self::$EPIC_SOCCER_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::SOCCER, 'Epic Upgrade' ); // jiggmin.com/threads/111373-Campaign-of-the-Fortnight-Theme-Suggestions!
		self::$EPIC_MAGNET_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::MAGNET, 'Epic Upgrade' );
		self::$EPIC_TINY_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::TINY, 'Epic Upgrade' );
		self::$EPIC_SANDAL_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::SANDAL, 'Epic Upgrade' ); //random levels
		self::$EPIC_BARE_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::BARE, 'Epic Upgrade' );
		self::$EPIC_NICE_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::NICE, 'Epic Upgrade' );
		self::$EPIC_BIRD_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::BIRD, 'Epic Upgrade' ); //campaign 5 level 3
		self::$EPIC_INVISIBLE_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::INVISIBLE, 'Epic Upgrade' );
		self::$EPIC_STICK_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::STICK, 'Epic Upgrade' ); //Styler of The Week //Curve Fever Tournament
		self::$EPIC_CAT_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::CAT, 'Epic Upgrade' ); //cheetah racing leage
		self::$EPIC_TIRE_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::TIRE, 'Epic Upgrade' ); //jiggmin grand prix
		self::$EPIC_ELEPHANT_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::ELEPHANT, 'Epic Upgrade' ); //campaign 6
		self::$EPIC_ANT_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::ANT, 'Epic Upgrade' );
		self::$EPIC_ASTRONAUT_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::ASTRONAUT, 'Epic Upgrade' );
		self::$EPIC_ALIEN_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::ALIEN, 'Epic Upgrade' ); //free rice of the week
		self::$EPIC_GALAXY_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::GALAXY, 'Epic Upgrade' ); //campaign 5 level 8
		self::$EPIC_DINO_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::DINO, 'Epic Upgrade' ); //campaign 5 level 6
		self::$EPIC_ARMOR_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::ARMOR, 'Epic Upgrade' );
		self::$EPIC_FAIRY_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::FAIRY, 'Epic Upgrade' ); //Draw the JVer!
		self::$EPIC_GINGERBREAD_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::GINGERBREAD, 'Epic Upgrade' );
		self::$EPIC_KING_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::KING, 'Epic Upgrade' ); //included
		self::$EPIC_QUEEN_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::QUEEN, 'Epic Upgrade' ); //included
		self::$EPIC_SIR_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::SIR, 'Epic Upgrade' ); //Sir Sirlington
		self::$EPIC_VERY_INVISIBLE_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::VERY_INVISIBLE, 'Epic Upgrade' ); //n/a
		self::$EPIC_BUBBLE_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::BUBBLE, 'Epic Upgrade' );
		self::$EPIC_TACO_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::TACO, 'Epic Upgrade' ); //racer of the week
		self::$EPIC_SLENDER_FEET = new Prize( self::TYPE_EPIC_FEET, Feet::SLENDER, 'Epic Upgrade' ); //campaign 6


		//---
		self::$EXP_5 = new Prize( self::TYPE_EXP, 5, 'Exp' );
	}



	public static function add( $prize ) {
		self::$arr[] = $prize;
	}



	public static function find( $type, $id ) {
		$match = null;
		foreach( self::$arr as $prize ) {
			if( $prize->get_type() == $type && $prize->get_id() == $id ) {
				$match = $prize;
				break;
			}
		}
		return $match;
	}

}

?>
