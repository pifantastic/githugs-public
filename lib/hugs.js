
var hugs = [
    "I love the work you do. :heart:"
  , "Great job as always buddy. :thumbsup:"
  , "The community appreciates your hard work! Have a :beer:"
  , "Great work! You're the :bomb:"
  , "You're a beautiful and special person."
];

var rand = function() {
  return hugs[Math.floor(Math.random() * hugs.length)];
}

exports.rand = rand;
exports.hugs = hugs;
