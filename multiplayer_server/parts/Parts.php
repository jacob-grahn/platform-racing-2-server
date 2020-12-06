<?php
// phpcs:ignoreFile

namespace pr2\multi;

class Hats
{
    const NONE = 1;
    const EXP = 2;
    const KONG = 3;
    const PROPELLER = 4;
    const COWBOY = 5;
    const CROWN = 6;
    const SANTA = 7;
    const PARTY = 8;
    const TOP_HAT = 9;
    const JUMP_START = 10;
    const MOON = 11;
    const THIEF = 12;
    const JIGG = 13;
    const ARTIFACT = 14;
    const JELLYFISH = 15;
    const CHEESE = 16;

    private const HAT_NAMES = [
        1  => 'None',
        2  => 'EXP',
        3  => 'Kong',
        4  => 'Propeller',
        5  => 'Cowboy',
        6  => 'Crown',
        7  => 'Santa',
        8  => 'Party',
        9  => 'Top',
        10 => 'Jump Start',
        11 => 'Moon',
        12 => 'Thief',
        13 => 'Jigg',
        14 => 'Artifact',
        15 => 'Jellyfish',
        16 => 'Cheese'
    ];

    const HAT_DESCS = [
        1  => 'Literally nothing.',
        2  => 'If you finish a race with this hat, it will increase your EXP gain by 100%!',
        3  => 'If you finish a race with this hat, it will increase your GP gain by 100%!',
        4  => 'Hold up while wearing this hat to float!',
        5  => 'Fly, cowboy, fly!',
        6  => 'Wear this hat to become immune to mines, laser guns, and swords!',
        7  => 'Briefly freezes the blocks you stand on!',
        8  => 'Wear this hat to become immune to lightning!',
        9  => 'Stroll through vanish blocks with class!',
        10 => 'Waiting is slow! Start racing right away.',
        11 => 'Soar to new heights by defying the laws of gravity!',
        12 => 'Steal other player\'s hats --even crowns!',
        13 => 'Bounce on the heads of your opponents!',
        14 => 'Leave your opponents in the dust for a glorious 30 seconds.',
        15 => 'Give nearby opponents a nasty sting!',
        16 => 'Turn crumble blocks into feta cheese --break through with record speed!'
    ];

    private const HAT_CODES = [
        1  => ['', 'n', 'none'],
        2  => ['e', 'exp', 'experience'],
        3  => ['k', 'kong', 'kongregate'],
        4  => ['pr', 'prop', 'propeller'],
        5  => ['cb', 'co', 'cowboy', 'gallon'],
        6  => ['cr', 'crown'],
        7  => ['s', 'santa'],
        8  => ['p', 'party'],
        9  => ['top', 'top_hat', 'tophat'],
        10 => ['js', 'jumpstart', 'jump_start', 'jump', 'start'],
        11 => ['m', 'moon', 'luna'],
        12 => ['t', 'th', 'thief'],
        13 => ['j', 'jigg', 'jiggmin'],
        14 => ['a', 'arti', 'artifact'],
        15 => ['jf', 'jellyfish', 'jelly', 'fish'],
        16 => ['ch', 'cheez', 'chz', 'cheese']
    ];


    public static function idToStr($id)
    {
        $id = (int) $id;
        $totalHats = count(HAT_NAMES);
        if ($id >= 1 && $id <= $totalHats) {
            return self::HAT_NAMES[$id];
        }
        return 'Unknown';
    }


    public static function strToId($str)
    {
        $str = strtolower($str);
        foreach (self::HAT_CODES as $id => $hat) {
            if (in_array($str, $hat)) {
                return $id;
            }
        }
        return 1;
    }

    public static function getDesc($id)
    {
        $id = (int) $id;
        $totalHats = count(HAT_DESCS);
        if ($id >= 1 && $id <= $totalHats) {
            return self::HAT_DESCS[$id];
        }
        return '';
    }
}


class Heads
{
    const CLASSIC = 1;
    const TIRED = 2;
    const SMILER = 3;
    const FLOWER = 4;
    const CLASSIC_GIRL = 5;
    const GOOF = 6;
    const DOWNER = 7;
    const BALLOON = 8;
    const WORM = 9;
    const UNICORN = 10;
    const BIRD = 11;
    const SUN = 12;
    const CANDY = 13;
    const INVISIBLE = 14;
    const FOOTBALL_HELMET = 15;
    const BASKETBALL = 16;
    const STICK = 17;
    const CAT = 18;
    const ELEPHANT = 19;
    const ANT = 20;
    const ASTRONAUT = 21;
    const ALIEN = 22;
    const DINO = 23;
    const ARMOR = 24;
    const FAIRY = 25;
    const GINGERBREAD = 26;
    const BUBBLE = 27;
    const KING = 28;
    const QUEEN = 29;
    const SIR = 30;
    const VERY_INVISIBLE = 31;
    const TACO = 32;
    const SLENDER = 33;
    const SANTA = 34;
    const FROST_DJINN = 35;
    const REINDEER = 36;
    const CROCODILE = 37;
    const VALENTINE = 38;
    const BUNNY = 39;
    const GECKO = 40;
    const BAT = 41;
    const SEA = 42;
    const BREW = 43;
    const JACKOLANTERN = 44;
    const XMAS = 45;
    const SNOWMAN = 46;
    const BLOBFISH = 47;
    const TURKEY = 48;
}


class Bodies
{
    const CLASSIC = 1;
    const STRAP = 2;
    const DRESS = 3;
    const PEC = 4;
    const GUT = 5;
    const COLLAR = 6;
    const MISS_PR2 = 7;
    const BELT = 8;
    const SNAKE = 9;
    const BIRD = 10;
    const INVISIBLE = 11;
    const BEE = 12;
    const STICK = 13;
    const CAT = 14;
    const CAR = 15;
    const BEAN = 16;
    const ANT = 17;
    const ASTRONAUT = 18;
    const ALIEN = 19;
    const GALAXY = 20;
    const BUBBLE = 21;
    const DINO = 22;
    const ARMOR = 23;
    const FAIRY = 24;
    const GINGERBREAD = 25;
    const KING = 26;
    const QUEEN = 27;
    const SIR = 28;
    const FRED = 29;
    const VERY_INVISIBLE = 30;
    const TACO = 31;
    const SLENDER = 32;
    const SANTA = 34;
    const FROST_DJINN = 35;
    const REINDEER = 36;
    const CROCODILE = 37;
    const VALENTINE = 38;
    const BUNNY = 39;
    const GECKO = 40;
    const BAT = 41;
    const SEA = 42;
    const BREW = 43;
    const XMAS = 45;
    const SNOWMAN = 46;
    const TURKEY = 48;
}


class Feet
{
    const CLASSIC = 1;
    const HEEL = 2;
    const LOAFER = 3;
    const SOCCER = 4;
    const MAGNET = 5;
    const TINY = 6;
    const SANDAL = 7;
    const BARE = 8;
    const NICE = 9;
    const BIRD = 10;
    const INVISIBLE = 11;
    const STICK = 12;
    const CAT = 13;
    const TIRE = 14;
    const ELEPHANT = 15;
    const ANT = 16;
    const ASTRONAUT = 17;
    const ALIEN = 18;
    const GALAXY = 19;
    const DINO = 20;
    const ARMOR = 21;
    const FAIRY = 22;
    const GINGERBREAD = 23;
    const KING = 24;
    const QUEEN = 25;
    const SIR = 26;
    const VERY_INVISIBLE = 27;
    const BUBBLE = 28;
    const TACO = 29;
    const SLENDER = 30;
    const SANTA = 34;
    const FROST_DJINN = 35;
    const REINDEER = 36;
    const CROCODILE = 37;
    const VALENTINE = 38;
    const BUNNY = 39;
    const GECKO = 40;
    const BAT = 41;
    const SEA = 42;
    const BREW = 43;
    const XMAS = 45;
    const SNOWMAN = 46;
    const TURKEY = 48;
}
