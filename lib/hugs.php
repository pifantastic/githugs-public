<?php

class Hugs {

  public static $hugs = array(
    "I love the work you do. :heart:",
    "Great job as always buddy. :thumbsup:",
    "The community appreciates your hard work! Have a :beer:",
    "Great work! You're the :bomb:",
    "You're a beautiful and special person."
  );

  public static function random() {
    return self::$hugs[array_rand(self::$hugs)];
  }

}
